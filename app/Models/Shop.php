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
        'stripe_publishable_key',
        'stripe_secret_key',
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
        'stripe_publishable_key' => 'encrypted',
        'stripe_secret_key' => 'encrypted',
        'settings' => 'array',
        'installed_at' => 'datetime',
        'uninstalled_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'access_token',
        'stripe_secret_key',
    ];

    /**
     * Store Stripe keys on shop model.
     */
    public function setStripeCredentials(string $publishableKey, string $secretKey, ?string $accountId = null): void
    {
        $this->stripe_publishable_key = $publishableKey;
        $this->stripe_secret_key = $secretKey;
        if ($accountId) {
            $this->stripe_account_id = $accountId;
        }
        $this->save();
    }

    /**
     * Get active Stripe publishable key (per-shop key or config fallback).
     */
    public function getStripePublishableKey(): ?string
    {
        return $this->stripe_publishable_key ?: config('services.stripe.key');
    }

    /**
     * Get active Stripe secret key (per-shop key or config fallback).
     */
    public function getStripeSecretKey(): ?string
    {
        return $this->stripe_secret_key ?: config('services.stripe.secret');
    }

    /**
     * Check if Stripe account is connected with valid keys.
     */
    public function isStripeConnected(): bool
    {
        return !empty($this->stripe_account_id) && !empty($this->getStripeSecretKey());
    }

    /**
     * Check if Stripe is currently operating in test mode.
     */
    public function isStripeTestMode(): bool
    {
        $secret = (string) $this->getStripeSecretKey();
        $pub = (string) $this->getStripePublishableKey();
        return str_starts_with($secret, 'sk_test_') || str_starts_with($pub, 'pk_test_');
    }

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

    /**
     * Orders placed in this shop.
     */
    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Customizable email templates for this shop.
     */
    public function emailTemplates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmailTemplate::class);
    }

    /**
     * Audit log history for this shop.
     */
    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Ceramic Club recurring subscriptions for this shop.
     */
    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
