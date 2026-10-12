<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanTarget;
use App\Models\Shop;
use App\Services\Payment\DepositCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlanController extends Controller
{
    public function __construct(
        protected DepositCalculationService $depositService
    ) {}

    /**
     * List all plans for the authenticated shop.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $plans = $shop->plans()
            ->with('targets')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function (Plan $plan) use ($shop) {
                $currency = $shop->currency ?? 'AUD';
                $formattedValue = $plan->deposit_type === 'percentage'
                    ? "{$plan->deposit_value}%"
                    : $this->depositService->format((int) $plan->deposit_value, $currency);

                $terms = $plan->isInstallment()
                    ? "{$plan->installments_count} payments (" . ($plan->interval_count > 1 ? "{$plan->interval_count} {$plan->interval}s" : "{$plan->interval}ly") . ')'
                    : $formattedValue;

                return array_merge($plan->toArray(), [
                    'formatted_deposit_value' => $formattedValue,
                    'formatted_terms' => $terms,
                    'targets_count' => $plan->targets->count(),
                ]);
            });

        return response()->json([
            'plans' => $plans,
            'currency' => $shop->currency ?? 'AUD',
        ]);
    }

    /**
     * Store a newly created plan for the authenticated shop.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:deposit,installment,subscription'],
            'deposit_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'deposit_value' => ['nullable', 'integer', 'min:1'],
            'installments_count' => ['nullable', 'integer', 'min:2', 'max:4'],
            'interval' => ['nullable', 'string', 'in:week,month'],
            'interval_count' => ['nullable', 'integer', 'min:1'],
            'active' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['string'],
            'collection_ids' => ['nullable', 'array'],
            'collection_ids.*' => ['string'],
        ]);

        $type = $validated['type'] ?? 'deposit';
        if ($type === 'installment') {
            $validated['installments_count'] = $validated['installments_count'] ?? 3;
            $validated['deposit_type'] = $validated['deposit_type'] ?? 'percentage';
            $validated['deposit_value'] = $validated['deposit_value'] ?? (int) round(100 / $validated['installments_count']);
        } else {
            if (empty($validated['deposit_type']) || empty($validated['deposit_value'])) {
                throw ValidationException::withMessages([
                    'deposit_type' => ['Deposit calculation method and value are required.'],
                ]);
            }
            $this->validateDepositLogic(
                $validated['deposit_type'],
                $validated['deposit_value'],
                $shop->currency ?? 'AUD'
            );
        }

        $plan = DB::transaction(function () use ($shop, $validated, $request) {
            $data = [
                'name' => $validated['name'],
                'type' => $validated['type'] ?? 'deposit',
                'deposit_type' => $validated['deposit_type'],
                'deposit_value' => $validated['deposit_value'],
                'active' => $validated['active'] ?? true,
            ];

            if (!empty($validated['installments_count'])) {
                $data['installments_count'] = $validated['installments_count'];
            }
            if (!empty($validated['interval'])) {
                $data['interval'] = $validated['interval'];
            }
            if (!empty($validated['interval_count'])) {
                $data['interval_count'] = $validated['interval_count'];
            }

            $plan = $shop->plans()->create($data);

            $this->syncTargets($plan, $validated['product_ids'] ?? [], $validated['collection_ids'] ?? []);

            \App\Models\AuditLog::record(
                shopId: $shop->id,
                action: 'plan.created',
                target: $plan,
                meta: [
                    'name' => $plan->name,
                    'deposit_type' => $plan->deposit_type,
                    'deposit_value' => $plan->deposit_value,
                ],
                userId: $request->attributes->get('user_id')
            );

            return $plan->load('targets');
        });

        return response()->json([
            'message' => 'Plan created successfully.',
            'plan' => $plan,
        ], 201);
    }

    /**
     * Show a single plan belonging to the authenticated shop.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $plan = $shop->plans()->with('targets')->findOrFail($id);

        return response()->json(['plan' => $plan]);
    }

    /**
     * Update an existing plan belonging to the authenticated shop.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $plan = $shop->plans()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:deposit,installment,subscription'],
            'deposit_type' => ['sometimes', 'required', 'string', 'in:percentage,fixed'],
            'deposit_value' => ['sometimes', 'required', 'integer', 'min:1'],
            'installments_count' => ['nullable', 'integer', 'min:2'],
            'interval' => ['nullable', 'string', 'in:week,month'],
            'interval_count' => ['nullable', 'integer', 'min:1'],
            'active' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['string'],
            'collection_ids' => ['nullable', 'array'],
            'collection_ids.*' => ['string'],
        ]);

        $depType = $validated['deposit_type'] ?? $plan->deposit_type;
        $depVal = $validated['deposit_value'] ?? $plan->deposit_value;

        $this->validateDepositLogic($depType, $depVal, $shop->currency ?? 'AUD');

        $updatedPlan = DB::transaction(function () use ($plan, $validated, $shop, $request) {
            $plan->update(array_filter([
                'name' => $validated['name'] ?? $plan->name,
                'type' => $validated['type'] ?? $plan->type,
                'deposit_type' => $validated['deposit_type'] ?? $plan->deposit_type,
                'deposit_value' => $validated['deposit_value'] ?? $plan->deposit_value,
                'installments_count' => array_key_exists('installments_count', $validated) ? $validated['installments_count'] : $plan->installments_count,
                'interval' => array_key_exists('interval', $validated) ? $validated['interval'] : $plan->interval,
                'interval_count' => array_key_exists('interval_count', $validated) ? $validated['interval_count'] : $plan->interval_count,
                'active' => array_key_exists('active', $validated) ? $validated['active'] : $plan->active,
            ], fn($v) => !is_null($v)));

            if (isset($validated['product_ids']) || isset($validated['collection_ids'])) {
                $this->syncTargets(
                    $plan,
                    $validated['product_ids'] ?? [],
                    $validated['collection_ids'] ?? []
                );
            }

            \App\Models\AuditLog::record(
                shopId: $shop->id,
                action: 'plan.updated',
                target: $plan,
                meta: [
                    'name' => $plan->name,
                    'deposit_type' => $plan->deposit_type,
                    'deposit_value' => $plan->deposit_value,
                ],
                userId: $request->attributes->get('user_id')
            );

            return $plan->load('targets');
        });

        return response()->json([
            'message' => 'Plan updated successfully.',
            'plan' => $updatedPlan,
        ]);
    }

    /**
     * Delete a plan belonging to the authenticated shop.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $plan = $shop->plans()->findOrFail($id);

        \App\Models\AuditLog::record(
            shopId: $shop->id,
            action: 'plan.deleted',
            target: "plan:{$plan->id}",
            meta: [
                'name' => $plan->name,
            ],
            userId: $request->attributes->get('user_id')
        );

        $plan->delete();

        return response()->json([
            'message' => 'Plan deleted successfully.',
        ]);
    }

    /**
     * Live preview calculation for given deposit or installment parameters.
     */
    public function preview(Request $request): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $type = $request->input('type') ?? $request->input('plan_type') ?? 'deposit';
        $totalCents = (int) ($request->input('sample_total_cents') ?? 20000); // Default $200.00
        $currency = $request->input('currency') ?? ($shop->currency ?? 'AUD');

        if ($type === 'installment') {
            $count = (int) ($request->input('installments_count') ?? 3);
            if ($count < 2 || $count > 4) {
                return response()->json(['error' => 'Installment count must be between 2 and 4.'], 422);
            }
            $amounts = app(\App\Services\Payments\InstallmentService::class)->calculateSchedule($totalCents, $count);
            $schedule = [];
            foreach ($amounts as $i => $amt) {
                $schedule[] = [
                    'sequence' => $i + 1,
                    'amount_cents' => $amt,
                    'formatted_amount' => $this->depositService->format($amt, $currency),
                ];
            }
            return response()->json([
                'total_cents' => $totalCents,
                'formatted_total' => $this->depositService->format($totalCents, $currency),
                'currency' => $currency,
                'is_installment' => true,
                'installments_count' => $count,
                'first_installment_cents' => $amounts[0],
                'formatted_first_installment' => $this->depositService->format($amounts[0], $currency),
                'schedule' => $schedule,
            ]);
        }

        $validated = $request->validate([
            'deposit_type' => ['required', 'string', 'in:percentage,fixed'],
            'deposit_value' => ['required', 'integer', 'min:1'],
            'sample_total_cents' => ['nullable', 'integer', 'min:100'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        try {
            $result = $this->depositService->calculate(
                $totalCents,
                $validated['deposit_type'],
                $validated['deposit_value'],
                $currency
            );

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Validate deposit logic against bounds.
     */
    protected function validateDepositLogic(string $type, int $value, string $currency): void
    {
        if ($type === 'percentage') {
            if ($value <= 0 || $value >= 100) {
                throw ValidationException::withMessages([
                    'deposit_value' => ['Percentage deposit must be strictly between 1% and 99%.'],
                ]);
            }
        } elseif ($type === 'fixed') {
            if ($value <= 0) {
                throw ValidationException::withMessages([
                    'deposit_value' => ['Fixed deposit amount must be greater than zero.'],
                ]);
            }
        }
    }

    /**
     * Synchronize product and collection targets for a plan.
     */
    protected function syncTargets(Plan $plan, array $productIds, array $collectionIds): void
    {
        $plan->targets()->delete();

        $rows = [];
        foreach ($productIds as $prodId) {
            if (!empty($prodId)) {
                $rows[] = [
                    'shopify_product_id' => (string) $prodId,
                    'shopify_collection_id' => null,
                ];
            }
        }

        foreach ($collectionIds as $collId) {
            if (!empty($collId)) {
                $rows[] = [
                    'shopify_product_id' => null,
                    'shopify_collection_id' => (string) $collId,
                ];
            }
        }

        if (!empty($rows)) {
            $plan->targets()->createMany($rows);
        }
    }
}
