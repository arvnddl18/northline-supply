<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentSchedule extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'sequence',
        'due_at',
        'amount',
        'status',
        'attempt_count',
        'next_retry_at',
        'stripe_payment_intent_id',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'amount' => 'integer',
        'attempt_count' => 'integer',
        'due_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'schedule_id');
    }

    public function scopePendingDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('due_at', '<=', now()->addDays($days));
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('due_at', '<=', now());
    }
}
