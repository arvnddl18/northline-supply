<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StorefrontCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;
    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.secret' => null, 'services.stripe.key' => null]);

        $this->shop = Shop::create([
            'shopify_domain' => 'northwind-ceramics.myshopify.com',
            'access_token' => 'shpat_test_token',
            'stripe_secret_key' => 'sk_test_abc',
            'installed_at' => now(),
            'currency' => 'AUD',
        ]);

        $this->plan = $this->shop->plans()->create([
            'name' => '30% deposit',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 30,
            'active' => true,
        ]);
    }

    protected function url(array $p): string
    {
        config(['services.shopify.api_secret' => 's3cret']);
        ksort($p);
        $s = '';
        foreach ($p as $k => $v) {
            $s .= "{$k}={$v}";
        }
        $p['signature'] = hash_hmac('sha256', $s, 's3cret');

        return '/proxy/checkout?'.http_build_query($p);
    }

    protected function params(array $over = []): array
    {
        return array_merge([
            'shop' => $this->shop->shopify_domain,
            'product_id' => '123',
            'variant_id' => '456',
            'plan_id' => $this->plan->id,
            'quantity' => 1,
            'price' => 20000,
            'currency' => 'AUD',
            'title' => 'Vase',
        ], $over);
    }

    public function test_creates_stripe_session_and_redirects(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response(['url' => 'https://checkout.stripe.com/c/pay/cs_test_1'])]);

        $this->get($this->url($this->params()))
            ->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_1');

        Http::assertSent(function ($request) {
            $b = $request->data();

            return $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
                && $b['payment_intent_data']['setup_future_usage'] === 'off_session'
                && $b['line_items'][0]['price_data']['unit_amount'] === 6000
                && $b['metadata']['balance_cents'] === 14000
                && $b['metadata']['deposit_cents'] === 6000
                && $b['metadata']['shop_id'] == $this->shop->id;
        });
    }

    public function test_json_format_returns_redirect_url(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response(['url' => 'https://checkout.stripe.com/x'])]);

        $this->getJson($this->url($this->params()))
            ->assertOk()->assertJson(['redirect_url' => 'https://checkout.stripe.com/x']);
    }

    public function test_quantity_multiplies_total(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response(['url' => 'https://checkout.stripe.com/x'])]);

        $this->get($this->url($this->params(['quantity' => 2])));

        Http::assertSent(fn ($r) => $r->data()['line_items'][0]['price_data']['unit_amount'] === 12000);
    }

    public function test_missing_params_rejected(): void
    {
        Http::fake();
        $this->getJson($this->url(['shop' => $this->shop->shopify_domain]))->assertStatus(422);
        Http::assertNothingSent();
    }

    public function test_unknown_or_inactive_plan_404(): void
    {
        Http::fake();
        $this->plan->update(['active' => false]);

        $this->getJson($this->url($this->params()))->assertStatus(404);
        Http::assertNothingSent();
    }

    public function test_missing_stripe_credentials_409(): void
    {
        Http::fake();
        $this->shop->update(['stripe_secret_key' => null]);

        $this->getJson($this->url($this->params()))->assertStatus(409);
        Http::assertNothingSent();
    }

    public function test_stripe_failure_returns_502(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response(['error' => 'x'], 400)]);

        $this->getJson($this->url($this->params()))->assertStatus(502);
    }
}

