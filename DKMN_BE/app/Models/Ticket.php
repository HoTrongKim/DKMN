<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    // Tên bảng
    protected $table = 'tickets';

    // Các trường fillable
    protected $fillable = [
        'don_hang_id',
        'trip_id',
        'seat_numbers',
        'status',
        'base_fare_vnd',
        'discount_vnd',
        'surcharge_vnd',
        'total_amount_vnd',
        'paid_amount_vnd',
        'payment_id',
    ];

    // Các hằng số trạng thái vé
    const STATUS_PENDING = 'PENDING';
    const STATUS_PAID = 'PAID';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_REFUNDED = 'REFUNDED';

    /**
     * Quan hệ n-1 với DonHang
     */
    public function donHang(): BelongsTo
    {
        return $this->belongsTo(DonHang::class, 'don_hang_id');
    }

    /**
     * Quan hệ n-1 với ChuyenDi (Trip)
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(ChuyenDi::class, 'trip_id');
    }

    /**
     * Quan hệ 1-n với Payment
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'ticket_id');
    }
}
