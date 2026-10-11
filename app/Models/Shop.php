<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shops';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shopify_domain',
        'access_token',
        'stripe_account_id',
        'currency',
        'settings',
        'installed_at',
        'uninstalled_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'access_token' => 'encrypted',
        'settings' => 'array',
        'installed_at' => 'datetime',
        'uninstalled_at' => 'datetime',
    ];

    /**
     * Determine if the shop is currently installed and active.
     */
    public function isInstalled(): bool
    {
        return !is_null($this->installed_at) && is_null($this->uninstalled_at);
    }

    /**
     * Mark shop uninstalled: clear access token and set uninstallation timestamp.
     */
    public function markUninstalled(): void
    {
        $this->update([
            'access_token' => null,
            'uninstalled_at' => now(),
        ]);
    }

    /**
     * Mark shop installed / re-installed with fresh encrypted token.
     */
    public function markInstalled(string $accessToken): void
    {
        $this->update([
            'access_token' => $accessToken,
            'installed_at' => now(),
            'uninstalled_at' => null,
        ]);
    }

    /**
     * Users belonging to this shop.
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Payment plans defined for this shop.
     */
    public function plans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * Customers associated with this shop.
     */
    public function customers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
