<?php

namespace App\Services\PaymentProviders;

use App\Contracts\PaymentProviderInterface;
use App\Models\Payment;

class GenericQrProvider implements PaymentProviderInterface
{
    public function __construct(
        protected string $key,
        protected string $label,
        protected string $account,
        protected string $baseUrl = 'https://img.vietqr.io/image'
    ) {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function generateQr(int $amount, Payment $payment): array
    {
        $metadata = sprintf('%s - %s', $this->label, $payment->ticket_id);
        $payload = urlencode($metadata);
        $qrImageUrl = sprintf(
            '%s/%s-compact.png?amount=%d&addInfo=%s',
            rtrim($this->baseUrl, '/'),
            $this->account,
            $amount,
            $payload
        );

        return [
            'qrImageUrl' => $qrImageUrl,
            'providerRef' => strtoupper(sprintf('%s-%s', $this->key, $payment->id)),
            'metadata' => [
                'amount' => $amount,
                'ticket_id' => $payment->ticket_id,
            ],
        ];
    }
}
