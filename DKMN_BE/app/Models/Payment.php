<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    // Tên bảng
    protected $table = 'payments';

    // Các trường fillable
    protected $fillable = [
        'ticket_id',
        'method',
        'provider',
        'provider_ref',
        'qr_image_url',
        'amount_vnd',
        'status',
        'checksum',
        'idempotency_key',
        'webhook_idempotency_key',
        'paid_at',
        'expires_at',
    ];

    // Ép kiểu dữ liệu
    protected $casts = [
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Các hằng số trạng thái thanh toán
    const STATUS_PENDING = 'PENDING';
    const STATUS_SUCCEEDED = 'SUCCEEDED';
    const STATUS_FAILED = 'FAILED';
    const STATUS_MISMATCH = 'MISMATCH';
    const STATUS_EXPIRED = 'EXPIRED';

    /**
     * Quan hệ n-1 với Ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
}
