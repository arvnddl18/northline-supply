<?php

namespace App\Console\Commands;

use App\Models\PaymentSchedule;
use App\Services\Email\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class SendBalanceRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-balance-reminders {--days=7 : Number of days ahead to scan for due schedules}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find due balances, generate secure signed balance links, and dispatch reminder emails (S2-T6)';

    /**
     * Execute the console command.
     */
    public function handle(EmailService $emailService): int
    {
        $days = (int) $this->option('days');
        $this->info("Scanning for payment schedules due in <= {$days} days...");

        $schedules = PaymentSchedule::pendingDueSoon($days)
            ->with(['order.shop', 'order.customer'])
            ->get();

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($schedules as $schedule) {
            $order = $schedule->order;
            if (!$order || $order->isSettled()) {
                continue;
            }

            // Expiration timestamp: 7 days after the scheduled due date
            $expiration = $schedule->due_at ? $schedule->due_at->copy()->addDays(7) : now()->addDays(14);
            $paymentLink = URL::signedRoute('pay.balance', ['schedule' => $schedule->id], $expiration);

            $sent = $emailService->sendBalanceReminder($schedule, $paymentLink);
            if ($sent) {
                $sentCount++;
                $this->line("  [Sent] Order #{$order->id} -> Customer {$order->customer->email}");
            } else {
                $skippedCount++;
            }
        }

        $this->info("Completed reminder run: {$sentCount} sent, {$skippedCount} skipped (already notified or settled).");

        return Command::SUCCESS;
    }
}
