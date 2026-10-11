<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentSchedule;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class InstallmentService
{
    /**
     * Calculate zero-penny leakage installment distribution for 2 to 4 payments (S4-T1, US-10).
     *
     * @param int $totalCents Total order amount in integer cents
     * @param int $numberOfPayments Number of split payments (2..4)
     * @return array<int, int> Integer amounts in cents summing exactly to $totalCents
     */
    public function calculateSchedule(int $totalCents, int $numberOfPayments): array
    {
        if ($totalCents <= 0) {
            throw new InvalidArgumentException("Total amount must be greater than zero.");
        }

        if ($numberOfPayments < 2 || $numberOfPayments > 4) {
            throw new InvalidArgumentException("Installment payments must be between 2 and 4.");
        }

        $base = intdiv($totalCents, $numberOfPayments);
        $remainder = $totalCents % $numberOfPayments;

        $schedule = array_fill(0, $numberOfPayments, $base);

        // Distribute remainder pennies to the earliest installments
        for ($i = 0; $i < $remainder; $i++) {
            $schedule[$i] += 1;
        }

        assert(array_sum($schedule) === $totalCents, "Zero-leakage invariant violated!");

        return $schedule;
    }

    /**
     * Generate and persist PaymentSchedule rows for an order under an installment plan.
     *
     * @return Collection<int, PaymentSchedule>
     */
    public function generateSchedules(Order $order, Plan $plan, ?Carbon $startDate = null): Collection
    {
        $startDate = $startDate ?? now();
        $count = $plan->installments_count ?: 3;
        $amounts = $this->calculateSchedule($order->total_amount, $count);

        $intervalType = $plan->interval ?: 'week';
        $intervalCount = $plan->interval_count ?: 2; // Default 2 weeks (bi-weekly)

        $schedules = collect();

        foreach ($amounts as $index => $amount) {
            $sequence = $index + 1;
            
            // First installment is due immediately at checkout (sequence 1)
            if ($sequence === 1) {
                $dueDate = $startDate->copy();
            } else {
                $step = ($sequence - 1) * $intervalCount;
                $dueDate = match ($intervalType) {
                    'day' => $startDate->copy()->addDays($step),
                    'week' => $startDate->copy()->addWeeks($step),
                    'month' => $startDate->copy()->addMonths($step),
                    default => $startDate->copy()->addWeeks($step),
                };
            }

            $schedule = $order->paymentSchedules()->create([
                'sequence' => $sequence,
                'due_at' => $dueDate,
                'amount' => $amount,
                'status' => $sequence === 1 ? PaymentSchedule::STATUS_SUCCEEDED : PaymentSchedule::STATUS_PENDING,
                'attempt_count' => 0,
            ]);

            $schedules->push($schedule);
        }

        return $schedules;
    }
}
