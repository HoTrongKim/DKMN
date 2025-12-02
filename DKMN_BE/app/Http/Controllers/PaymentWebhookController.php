<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentReconcileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Controller xử lý webhook từ SePay (ngân hàng tự động đối soát)
 * Nhận thông báo chuyển khoản từ SePay, extract mã order, tìm payment tương ứng và mark succeeded
 */
class PaymentWebhookController extends Controller
{
    public function __construct(private readonly PaymentReconcileService $reconcileService)
    {
    }

    /**
     * Xử lý webhook từ SePay
     * - Xác thực token/API key
     * - Kiểm tra IP whitelist (nếu có)
     * - Extract order code từ description hoặc payload
     * - Tìm payment pending tương ứng
     * - Mark payment succeeded nếu match
     */
    public function handleSepay(Request $request): JsonResponse
    {
        // Cho phép GET để SePay kiểm tra URL (trả về 200)
        if ($request->isMethod('get')) {
            return $this->respond(true, 'OK');
        }

        $expectedToken = trim((string) config('services.sepay.webhook_token', ''));
        $expectedApiKey = trim((string) config('services.sepay.webhook_api_key', ''));

        $authHeader = $request->header('Authorization') ?: $request->header('X-Authorization');
        $providedToken = $authHeader
            ?: $request->input('token')
            ?: $request->input('webhookToken')
            ?: $request->input('api_key');

        // Authorization header có thể dạng "Bearer xxx" hoặc "Api-Key xxx"
        if ($providedToken && str_contains($providedToken, ' ')) {
            $parts = explode(' ', $providedToken, 2);
            $providedToken = trim($parts[1] ?? $providedToken);
        }

        $providedApiKey = $request->header('X-API-KEY')
            ?? $request->header('X-Api-Key')
            ?? $request->input('api_key');

        $allowedIps = array_filter(array_map('trim', explode(',', (string) env('SEPAY_ALLOWED_IPS', ''))));
        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps, true)) {
            return $this->respond(false, 'IP not allowed', 403);
        }

        $tokenOk = $expectedToken === '' || (is_string($providedToken) && hash_equals($expectedToken, trim($providedToken)));
        $apiOk = $expectedApiKey === '' || (is_string($providedApiKey) && hash_equals($expectedApiKey, trim($providedApiKey)));

        if (($expectedToken !== '' || $expectedApiKey !== '') && !$tokenOk && !$apiOk) {
            return $this->respond(false, 'Unauthorized', 401);
        }

        $payload = $request->all();
        $rawAmount = null;
        foreach (['amount', 'amount_vnd', 'payment_amount', 'transferAmount', 'transfer_amount', 'transferValue', 'transfer_value'] as $amountKey) {
            $value = Arr::get($payload, $amountKey);
            if ($value !== null && $value !== '') {
                $rawAmount = $value;
                break;
            }
        }
        $amount = (int) round((float) ($rawAmount ?? 0));
        $description = Arr::get($payload, 'description')
            ?? Arr::get($payload, 'content')
            ?? Arr::get($payload, 'order_description')
            ?? Arr::get($payload, 'transaction_description');
        $explicitCode = Arr::get($payload, 'order_code')
            ?? Arr::get($payload, 'code')
            ?? Arr::get($payload, 'orderId')
            ?? Arr::get($payload, 'order_id');

        $orderCode = $this->reconcileService->extractOrderCode($description, $explicitCode);
        $requireCode = filter_var(env('SEPAY_REQUIRE_CODE', false), FILTER_VALIDATE_BOOL);
        if (!$orderCode && $requireCode) {
            return $this->respond(false, 'Order code not found in payload', 422);
        }

        if (!$orderCode) {
            return $this->respond(true, 'Order code not found, skipped', 200, [
                'skipped' => true,
            ]);
        }

        $payment = $this->reconcileService->findMatchingPayment($orderCode, $amount);
        if (!$payment && $amount > 0) {
            // In case bank payload is missing/rounded amount, fall back to matching by order code only.
            $payment = $this->reconcileService->findMatchingPayment($orderCode, 0);
        }
        if (!$payment) {
            return $this->respond(true, 'No pending payment matched', 200, [
                'skipped' => true,
                'orderCode' => $orderCode,
            ]);
        }

        $refNo = Arr::get($payload, 'transaction_no')
            ?? Arr::get($payload, 'txn_id')
            ?? Arr::get($payload, 'ref_no')
            ?? Arr::get($payload, 'bank_tran_no')
            ?? Arr::get($payload, 'bank_ref')
            ?? $payment->provider_ref;

        $this->reconcileService->markPaymentSuccess($payment, $refNo);

        return $this->respond(true, 'Payment reconciled', 200, [
            'paymentId' => $payment->id,
            'orderCode' => $orderCode,
            'status' => Payment::STATUS_SUCCEEDED,
        ]);
    }

    /**
     * Helper response chuẩn cho SePay webhook
     * Trả về JSON với success/status/message
     */
    private function respond(bool $ok, string $message, int $status = 200, array $data = []): JsonResponse
    {
        return response()->json(array_merge([
            'success' => $ok,
            'status' => $ok,
            'message' => $message,
        ], $data), $status);
    }
}
