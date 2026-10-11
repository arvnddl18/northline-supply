<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'user_id',
        'action',
        'target_type',
        'target_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an audit log entry.
     *
     * @param int $shopId
     * @param string $action
     * @param Model|string $target
     * @param array<string, mixed> $meta
     * @param int|null $userId
     */
    public static function record(
        int $shopId,
        string $action,
        Model|string $target,
        array $meta = [],
        ?int $userId = null
    ): self {
        $targetType = is_object($target) ? get_class($target) : 'System';
        $targetId = is_object($target) ? (string) $target->getKey() : (string) $target;

        return static::create([
            'shop_id' => $shopId,
            'user_id' => $userId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'meta' => $meta,
        ]);
    }
}
