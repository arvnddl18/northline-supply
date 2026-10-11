<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    protected $fillable = [
        'shop_id',
        'name',
        'type',
        'deposit_type',
        'deposit_value',
        'installments_count',
        'interval',
        'interval_count',
        'active',
    ];

    protected $casts = [
        'deposit_value' => 'integer',
        'installments_count' => 'integer',
        'interval_count' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Shop this plan belongs to.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Product or collection targets associated with this plan.
     */
    public function targets(): HasMany
    {
        return $this->hasMany(PlanTarget::class);
    }

    /**
     * Scope query to only active plans.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Determine if this is a deposit plan.
     */
    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }

    /**
     * Determine if this is an installment plan.
     */
    public function isInstallment(): bool
    {
        return $this->type === 'installment';
    }

    /**
     * Determine if this is a recurring subscription plan.
     */
    public function isSubscription(): bool
    {
        return $this->type === 'subscription';
    }
}
