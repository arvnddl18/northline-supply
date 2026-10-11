<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'shop_id',
        'customer_id',
        'plan_id',
        'stripe_subscription_id',
        'status',
        'next_renewal_at',
        'paused_until',
    ];

    protected $casts = [
        'next_renewal_at' => 'datetime',
        'paused_until' => 'datetime',
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

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function pause(Carbon $resumeDate): void
    {
        $this->update([
            'status' => self::STATUS_PAUSED,
            'paused_until' => $resumeDate,
        ]);
    }

    public function resume(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'paused_until' => null,
        ]);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }
}
