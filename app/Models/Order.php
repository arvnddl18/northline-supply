<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING_DEPOSIT = 'pending_deposit';
    public const STATUS_DEPOSIT_PAID = 'deposit_paid';
    public const STATUS_BALANCE_DUE = 'balance_due';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';
    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'shop_id',
        'shopify_order_id',
        'customer_id',
        'plan_id',
        'currency',
        'total_amount',
        'paid_amount',
        'payment_status',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'paid_amount' => 'integer',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class)->orderBy('sequence');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function refunds(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Refund::class, Payment::class);
    }

    public function remainingBalance(): int
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    public function isSettled(): bool
    {
        return $this->payment_status === self::STATUS_PAID || $this->remainingBalance() === 0;
    }
}
