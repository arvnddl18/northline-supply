<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanTarget extends Model
{
    use HasFactory;

    protected $table = 'plan_targets';

    protected $fillable = [
        'plan_id',
        'shopify_product_id',
        'shopify_collection_id',
    ];

    /**
     * Plan this target belongs to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
