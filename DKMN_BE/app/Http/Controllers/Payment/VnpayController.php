<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Ticket;
use App\Services\PaymentService;
use App\Services\PaymentSuccessNotifier;
use App\Services\TicketHoldService;
use App\Services\TicketNotificationService;
use App\Services\VnpayService;
use App\Support\PriceNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Controller xử lý thanh toán qua cổng VNPAY
 * Tạo payment intent, xử lý IPN webhook từ VNPAY, xử lý return URL sau thanh toán
 */
class VnpayController extends Controller
{
    public function __construct(
        private readonly VnpayService $vnpayService,
        private readonly PaymentService $paymentService,
        private readonly TicketNotificationService $ticketNotificationService,
        private readonly PaymentSuccessNotifier $paymentSuccessNotifier
    ) {
    }

    /**
     * Khởi tạo payment intent với VNPAY
     * Tạo payment record và build URL chuyển hướng đến VNPAY
     * Hỗ trợ idempotency key để tránh tạo payment trùng lặp
     */
    /**
     * Khởi tạo payment intent với VNPAY
     * Tạo payment record và build URL chuyển hướng đến VNPAY
     * Hỗ trợ idempotency key để tránh tạo payment trùng lặp
     * Logic:
     * - Validate thông tin vé và các tham số thanh toán
     * - Kiểm tra quyền sở hữu vé và thời gian giữ vé
     * - Tính toán số tiền cần thanh toán (hỗ trợ test mode)
     * - Kiểm tra idempotency key để trả về payment đã tồn tại (nếu có)
     * - Tạo payment record mới trong DB transaction
     * - Build URL thanh toán VNPAY và trả về cho client
     */
    public function init(Request $request): JsonResponse
    {
        // 1. Validate request
        $validated = $request->validate([
            'ticketId' => 'required|integer|exists:tickets,id',
            'bankCode' => 'nullable|string|max:50', // Mã ngân hàng (nếu user chọn trước)
            'locale' => 'nullable|string|in:vn,en', // Ngôn ngữ hiển thị
            'orderInfo' => 'nullable|string|max:255', // Nội dung thanh toán
            'returnUrl' => 'nullable|url|max:255', // URL redirect sau khi thanh toán
            'testMode' => 'nullable|boolean', // Chế độ test (dùng số tiền ảo)
        ]);

        // 2. Load ticket và kiểm tra quyền
        $ticket = Ticket::with('donHang')->findOrFail($validated['ticketId']);
        if ($response = $this->guardTicketOwner($request, $ticket)) {
            return $response;
        }
        
        // 3. Kiểm tra thời gian giữ vé (ticket hold expiration)
        if (TicketHoldService::isExpired($ticket)) {
            TicketHoldService::expireTicket($ticket);
            return response()->json([
                'status' => false,
                'message' => 'Phien giu ghe da het han. Vui long dat lai chuyen.',
            ], 410);
        }

        // 4. Tính toán số tiền thanh toán
        $fare = $this->paymentService->computeFare($ticket);
        $amount = (int) ($fare['totalAmount'] ?? 0);

        // Xử lý số tiền cho chế độ test
        if ($request->boolean('testMode')) {
            $amount = (int) config('payments.test_amount_vnd', $amount ?: 0);
        }
        // Fallback số tiền mặc định nếu tính toán ra <= 0 (tránh lỗi VNPAY)
        if ($amount <= 0) {
            $amount = (int) config('payments.default_fare_vnd', 1200);
        }

        // 5. Xử lý Idempotency Key (tránh trùng lặp giao dịch)
        $idempotencyKey = $request->header('Idempotency-Key');
        if ($idempotencyKey) {
            $existing = Payment::query()
                ->where('ticket_id', $ticket->id)
                ->where('method', 'VNPAY')
                ->where('provider', 'vnpay')
                ->where('idempotency_key', $idempotencyKey)
                ->latest()
                ->first();

            if ($existing) {
                return $this->respondWithPayment($existing);
            }
        }

        // 6. Tạo Payment Record mới
        $expiresAt = Carbon::now()->addMinutes(
            (int) config('payments.vnpay.expire_minutes', config('payments.intent_expiration_minutes', 15))
        );

        $payment = DB::transaction(function () use ($ticket, $amount, $idempotencyKey, $expiresAt) {
            $payment = Payment::create([
                'ticket_id' => $ticket->id,
                'method' => 'VNPAY',
                'provider' => 'vnpay',
                'amount_vnd' => PriceNormalizer::clamp($amount),
                'status' => Payment::STATUS_PENDING,
                'idempotency_key' => $idempotencyKey,
                'expires_at' => $expiresAt,
            ]);

            // Tạo tham chiếu tạm thời và checksum bảo mật
            $payment->provider_ref = 'VNPAY-' . $payment->id;
            $payment->checksum = $this->paymentService->computeChecksum($payment);
            $payment->save();

            return $payment->fresh();
        });

        // 7. Build VNPAY URL
        try {
            $payData = $this->vnpayService->buildPayUrl($payment, [
                'bank_code' => $validated['bankCode'] ?? null,
                'locale' => $validated['locale'] ?? null,
                'order_info' => $validated['orderInfo'] ?? null,
                'return_url' => $validated['returnUrl'] ?? null,
                'ip_addr' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // Xóa payment nếu build URL thất bại (do cấu hình lỗi)
            $payment->delete();

            return response()->json([
                'status' => false,
                'message' => 'Cau hinh VNPAY chua du. Vui long kiem tra tmn_code/hash_secret/payment_url.',
            ], 503);
        }

        // Cập nhật provider_ref thực tế từ VNPAY (txn_ref)
        $payment->provider_ref = $payData['txn_ref'];
        $payment->save();

        return response()->json([
            'status' => true,
            'data' => $this->serializePayment($payment->fresh()),
            'payUrl' => $payData['pay_url'],
            'payload' => $payData['query'],
        ], 201);
    }

    /**
     * Xử lý IPN (Instant Payment Notification) từ VNPAY
     * Verify signature, kiểm tra amount, cập nhật status payment
     * VNPAY gọi webhook này khi thanh toán thành công/thất bại
     * Logic:
     * - Verify signature của VNPAY gửi sang
     * - Tìm payment dựa trên vnp_TxnRef
     * - Kiểm tra số tiền thanh toán (cho phép sai số nhỏ)
     * - Cập nhật trạng thái payment (thành công/thất bại)
     * - Nếu thành công: cập nhật ticket, gửi email xác nhận
     */
    public function ipn(Request $request): JsonResponse
    {
        $payload = $request->all();

        // 1. Verify Signature
        if (!$this->vnpayService->verifySignature($payload)) {
            return $this->ipnResponse('97', 'Invalid signature');
        }

        // 2. Tìm Payment
        $txnRef = $payload['vnp_TxnRef'] ?? null;
        if (!$txnRef || !is_string($txnRef)) {
            return $this->ipnResponse('01', 'Missing transaction reference');
        }

        $payment = Payment::find($txnRef);
        if (!$payment) {
            return $this->ipnResponse('01', 'Payment not found');
        }

        // 3. Kiểm tra số tiền (Amount Check)
        $amount = (int) round(((int) ($payload['vnp_Amount'] ?? 0)) / 100); // VNPAY amount nhân 100
        $allowedDelta = (int) config('payments.allowed_amount_delta', 0);

        if (abs($amount - (int) $payment->amount_vnd) > $allowedDelta) {
            $payment->update([
                'status' => Payment::STATUS_MISMATCH,
                'webhook_idempotency_key' => $payload['vnp_SecureHash'] ?? null,
            ]);

            return $this->ipnResponse('04', 'Amount mismatch');
        }

        // 4. Kiểm tra trạng thái hiện tại (Idempotency)
        if ($payment->status === Payment::STATUS_SUCCEEDED) {
            return $this->ipnResponse('00', 'Payment already confirmed', $payment);
        }

        // 5. Xử lý kết quả giao dịch
        $responseCode = (string) ($payload['vnp_ResponseCode'] ?? '');
        $txnStatus = (string) ($payload['vnp_TransactionStatus'] ?? '');

        // Giao dịch thất bại
        if ($responseCode !== '00' || $txnStatus !== '00') {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'provider_ref' => $payment->provider_ref ?? ($payload['vnp_TransactionNo'] ?? $txnRef),
                'webhook_idempotency_key' => $payload['vnp_SecureHash'] ?? null,
            ]);

            return $this->ipnResponse('00', 'Payment recorded as failed', $payment);
        }

        // Giao dịch thành công -> Hoàn tất thanh toán
        $ticket = $this->completePayment(
            $payment,
            $amount,
            $payload['vnp_SecureHash'] ?? null,
            $payload['vnp_TransactionNo'] ?? ($payload['vnp_BankTranNo'] ?? $payment->provider_ref)
        );

        // Gửi thông báo/email
        if ($ticket) {
            $this->ticketNotificationService->sendTicketBookedMail($ticket, $payment->fresh());
            $this->paymentSuccessNotifier->send($ticket, $payment->fresh());
        }

        return $this->ipnResponse('00', 'Confirm success', $payment->fresh());
    }

    /**
     * Xử lý return URL sau khi user hoàn tất thanh toán trên VNPAY
     * User được redirect về đây kèm query params chứa kết quả
     * Verify signature và complete payment nếu thành công
     */
    public function handleReturn(Request $request): JsonResponse
    {
        $payload = $request->all();
        $txnRef = $payload['vnp_TxnRef'] ?? null;
        $payment = $txnRef ? Payment::find($txnRef) : null;

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        $amount = (int) round(((int) ($payload['vnp_Amount'] ?? 0)) / 100);
        $responseCode = (string) ($payload['vnp_ResponseCode'] ?? '');
        $txnStatus = (string) ($payload['vnp_TransactionStatus'] ?? '');

        // Verify signature và check status thành công
        if ($this->vnpayService->verifySignature($payload)
            && $responseCode === '00'
            && $txnStatus === '00'
            && $payment->status !== Payment::STATUS_SUCCEEDED
        ) {
            // Hoàn tất thanh toán nếu chưa xử lý qua IPN
            $ticket = $this->completePayment(
                $payment,
                $amount,
                $payload['vnp_SecureHash'] ?? null,
                $payload['vnp_TransactionNo'] ?? ($payload['vnp_BankTranNo'] ?? $payment->provider_ref)
            );

            if ($ticket) {
                $this->ticketNotificationService->sendTicketBookedMail($ticket, $payment->fresh());
                $this->paymentSuccessNotifier->send($ticket, $payment->fresh());
            }
        }

        return response()->json([
            'status' => true,
            'data' => $this->serializePayment($payment->fresh()),
            'message' => $responseCode === '00' ? 'Payment success' : 'Payment failed or cancelled',
        ]);
    }

    /**
     * Complete payment: cập nhật status thành succeeded, set paid_at
     * Cập nhật amount trên ticket và gửi email xác nhận
     * Sử dụng DB transaction để đảm bảo tính toàn vẹn
     */
    private function completePayment(
        Payment $payment,
        int $amount,
        ?string $idempotencyKey,
        ?string $providerRef
    ): ?Ticket {
        $ticketForMail = null;

        DB::transaction(function () use ($payment, $amount, $idempotencyKey, $providerRef, &$ticketForMail) {
            // Cập nhật trạng thái thanh toán
            $payment->update([
                'status' => Payment::STATUS_SUCCEEDED,
                'paid_at' => now(),
                'webhook_idempotency_key' => $idempotencyKey,
                'provider_ref' => $providerRef ?: $payment->provider_ref,
                'amount_vnd' => PriceNormalizer::clamp($amount),
            ]);

            // Cập nhật thông tin vé (lock row để tránh race condition)
            $ticket = $payment->ticket()->lockForUpdate()->first();
            if ($ticket) {
                $this->paymentService->setAmountOnTicket($ticket, $payment->amount_vnd, $payment->id);
                $ticketForMail = $ticket->fresh();
            }
        });

        return $ticketForMail;
    }

    /**
     * Format response cho IPN theo chuẩn của VNPAY
     * RspCode: 00=success, 97=invalid signature, 01=not found, 04=amount mismatch
     */
    private function ipnResponse(string $code, string $message, ?Payment $payment = null): JsonResponse
    {
        $data = [
            'RspCode' => $code,
            'Message' => $message,
        ];

        if ($payment) {
            $data['payment'] = $this->serializePayment($payment->fresh());
        }

        return response()->json($data);
    }

    /**
     * Kiểm tra quyền sở hữu vé: chỉ cho phép user tạo order hoặc admin
     * Return null nếu OK, return JsonResponse nếu unauthorized/forbidden
     */
    private function guardTicketOwner(Request $request, Ticket $ticket): ?JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Yeu cau dang nhap.',
            ], 401);
        }

        $role = strtolower((string) ($user->vai_tro ?? ''));
        if ($role === 'quan_tri') {
            return null;
        }

        $ownerId = $ticket->donHang?->nguoi_dung_id;
        if ((int) $ownerId !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Khong co quyen thao tac tren ve nay.',
            ], 403);
        }

        return null;
    }

    /**
     * Helper response với payment data đã serialize
     */
    private function respondWithPayment(Payment $payment, int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->serializePayment($payment->fresh()),
        ], $status);
    }

    /**
     * Transform payment model sang format API response
     * Include: paymentId, status, method, amount, QR URL, timestamps
     */
    private function serializePayment(Payment $payment): array
    {
        return [
            'paymentId' => $payment->id,
            'ticketId' => $payment->ticket_id,
            'status' => $payment->status,
            'method' => $payment->method,
            'provider' => $payment->provider,
            'providerRef' => $payment->provider_ref,
            'amount' => (int) $payment->amount_vnd,
            'currency' => 'VND',
            'qrImageUrl' => $payment->qr_image_url,
            'expiresAt' => optional($payment->expires_at)->toIso8601String(),
            'paidAt' => optional($payment->paid_at)->toIso8601String(),
        ];
    }
}
