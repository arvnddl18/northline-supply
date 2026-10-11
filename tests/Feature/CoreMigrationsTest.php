<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\PlanTarget;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreMigrationsTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shop = Shop::create([
            'shopify_domain' => 'test-ceramics.myshopify.com',
            'access_token' => 'shpat_test_token_123',
            'installed_at' => now(),
            'currency' => 'AUD',
        ]);
    }

    public function test_user_belongs_to_shop_and_has_roles(): void
    {
        $owner = User::create([
            'shop_id' => $this->shop->id,
            'email' => 'emma@northwindceramics.com.au',
            'role' => 'owner',
        ]);

        $staff = User::create([
            'shop_id' => $this->shop->id,
            'email' => 'apprentice@northwindceramics.com.au',
            'role' => 'staff',
        ]);

        $this->assertTrue($owner->isOwner());
        $this->assertFalse($owner->isStaff());
        $this->assertEquals($this->shop->id, $owner->shop->id);

        $this->assertTrue($staff->isStaff());
        $this->assertFalse($staff->isOwner());

        $this->assertCount(2, $this->shop->users);
    }

    public function test_plan_belongs_to_shop_and_manages_deposit_configurations(): void
    {
        $depositPlan = Plan::create([
            'shop_id' => $this->shop->id,
            'name' => '30% Made-to-Order Deposit',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 30,
            'installments_count' => 1,
            'active' => true,
        ]);

        $fixedPlan = Plan::create([
            'shop_id' => $this->shop->id,
            'name' => '$50 Flat Workshop Deposit',
            'type' => 'deposit',
            'deposit_type' => 'fixed',
            'deposit_value' => 5000, // 5000 cents = $50.00
            'installments_count' => 1,
            'active' => false,
        ]);

        $this->assertTrue($depositPlan->isDeposit());
        $this->assertFalse($depositPlan->isInstallment());
        $this->assertFalse($depositPlan->isSubscription());
        $this->assertTrue($depositPlan->active);

        // Test active scope
        $activePlans = Plan::active()->get();
        $this->assertTrue($activePlans->contains($depositPlan));
        $this->assertFalse($activePlans->contains($fixedPlan));

        $this->assertCount(2, $this->shop->plans);
    }

    public function test_plan_targets_link_products_and_collections_and_cascade_on_plan_delete(): void
    {
        $plan = Plan::create([
            'shop_id' => $this->shop->id,
            'name' => 'Tableware Deposit Plan',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 25,
            'active' => true,
        ]);

        $productTarget = PlanTarget::create([
            'plan_id' => $plan->id,
            'shopify_product_id' => 'gid://shopify/Product/987654321',
        ]);

        $collectionTarget = PlanTarget::create([
            'plan_id' => $plan->id,
            'shopify_collection_id' => 'gid://shopify/Collection/123456789',
        ]);

        $this->assertCount(2, $plan->targets);
        $this->assertEquals($plan->id, $productTarget->plan->id);

        // Deleting plan cascades to targets
        $plan->delete();
        $this->assertDatabaseMissing('plan_targets', ['id' => $productTarget->id]);
        $this->assertDatabaseMissing('plan_targets', ['id' => $collectionTarget->id]);
    }

    public function test_customer_records_link_shopify_and_stripe_identities(): void
    {
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'shopify_customer_id' => 'gid://shopify/Customer/112233',
            'email' => 'collector@example.com',
            'stripe_customer_id' => 'cus_test_123456789',
            'default_payment_method' => 'pm_card_visa_4242',
        ]);

        $this->assertEquals($this->shop->id, $customer->shop->id);
        $this->assertEquals('cus_test_123456789', $customer->stripe_customer_id);
        $this->assertCount(1, $this->shop->customers);
    }

    public function test_deleting_shop_cascades_to_all_core_tenant_entities(): void
    {
        $user = User::create([
            'shop_id' => $this->shop->id,
            'email' => 'admin@test.com',
        ]);

        $plan = Plan::create([
            'shop_id' => $this->shop->id,
            'name' => 'Cascade Plan',
            'type' => 'deposit',
        ]);

        $target = PlanTarget::create([
            'plan_id' => $plan->id,
            'shopify_product_id' => 'gid://shopify/Product/1',
        ]);

        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'email' => 'buyer@test.com',
            'stripe_customer_id' => 'cus_999',
        ]);

        // Act: Delete the shop
        $this->shop->delete();

        // Assert: Everything cascaded
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
        $this->assertDatabaseMissing('plan_targets', ['id' => $target->id]);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
