# Completed Work: S1-T1 Core Migrations

## Overview
- **Task ID**: `S1-T1`
- **Title**: Core migrations
- **Priority**: `#p0-must`
- **Work Type**: `#dev`
- **Sprint**: Sprint 1
- **Estimate**: 1.0d
- **Status**: Completed

## Objectives Achieved
1. **Multi-Tenant User Schema (`users`)**:
   - Added foreign key `shop_id` referencing `shops(id)` with cascading deletion.
   - Added `role` attribute (`owner`, `staff`) with default `'owner'`.
   - Made `name` and `password` nullable to allow seamless auto-provisioning for Shopify embedded admin sessions.
   - Updated [User.php](file:///c:/codework/northline-supply/app/Models/User.php) with `shop()` relationship, `isOwner()`, and `isStaff()` role helpers.
2. **Payment Plans Table & Model (`plans`)**:
   - Created `plans` table storing payment configurations for deposits, installments, and subscriptions.
   - Supported deposit percentage values (e.g. 30%) and fixed amounts in integer cents (e.g. 5000 = $50.00).
   - Created [Plan.php](file:///c:/codework/northline-supply/app/Models/Plan.php) with `active()` query scope, `shop()` relationship, `targets()` relationship, and `isDeposit()`, `isInstallment()`, `isSubscription()` helpers.
3. **Plan Targets Schema & Model (`plan_targets`)**:
   - Created `plan_targets` table linking payment plans to specific Shopify product GIDs (`shopify_product_id`) or collection GIDs (`shopify_collection_id`).
   - Configured cascading deletion when a plan is deleted.
   - Created [PlanTarget.php](file:///c:/codework/northline-supply/app/Models/PlanTarget.php) with `plan()` relationship.
4. **Customer Identities Schema & Model (`customers`)**:
   - Created `customers` table linking Shopify customer GIDs (`shopify_customer_id`) to Stripe customer profiles (`stripe_customer_id`) and saved payment methods (`default_payment_method`).
   - Scoped multi-tenancy by `shop_id` with cascading deletion.
   - Created [Customer.php](file:///c:/codework/northline-supply/app/Models/Customer.php) with `shop()` relationship.
5. **Shop Entity Cascade Integration (`shops`)**:
   - Updated [Shop.php](file:///c:/codework/northline-supply/app/Models/Shop.php) with `users()`, `plans()`, and `customers()` relationships.
6. **PostgreSQL 16 & Rollback Verification**:
   - Executed migrations against live PostgreSQL 16 container (`docker compose run --rm app php artisan migrate`).
   - Tested 4-step rollback and re-application cleanly.
7. **Automated Test Coverage**:
   - Created `tests/Feature/CoreMigrationsTest.php` verifying:
     - User-to-shop association and role privileges (`owner` vs `staff`)
     - Plan creation, active scope, and deposit configurations (percentage & fixed cents)
     - Plan targets linking products/collections and cascading deletion
     - Customer identity mapping between Shopify and Stripe
     - Full tenant cascade deletion when a Shop is removed
   - All 19 tests in the test suite pass (83 assertions).

## Related Files
- `database/migrations/2026_10_12_000001_add_shop_id_and_role_to_users_table.php`
- `database/migrations/2026_10_12_000002_create_plans_table.php`
- `database/migrations/2026_10_12_000003_create_plan_targets_table.php`
- `database/migrations/2026_10_12_000004_create_customers_table.php`
- `app/Models/Shop.php`
- `app/Models/User.php`
- `app/Models/Plan.php`
- `app/Models/PlanTarget.php`
- `app/Models/Customer.php`
- `tests/Feature/CoreMigrationsTest.php`
- `Vault/Northline Supply Vault/11 - Specs/DATABASE_SCHEMA.md`
- `Vault/Northline Supply Vault/11 - Specs/IMPLEMENTATION_PLAN.md` (Sprint 1)
