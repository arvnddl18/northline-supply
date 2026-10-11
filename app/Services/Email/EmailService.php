<?php

namespace App\Services\Email;

use App\Mail\OrderTransactionalMail;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\PaymentSchedule;
use App\Models\Shop;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Render template with substituted tokens.
     * shortcut: String token replacement instead of full Liquid runtime for zero bloat; upgrade in Sprint 5 if logic blocks are needed.
     *
     * @param Shop $shop
     * @param string $key
     * @param array<string, string> $tokens
     * @return array{subject: string, body: string}
     */
    public function renderTemplate(Shop $shop, string $key, array $tokens = []): array
    {
        $template = $shop->emailTemplates()->where('key', $key)->first();

        $subject = $template ? $template->subject : EmailTemplate::defaultSubject($key);
        $body = $template ? $template->body : EmailTemplate::defaultBody($key);

        foreach ($tokens as $token => $val) {
            $needle = "{" . trim($token, "{}") . "}";
            $subject = str_replace($needle, (string) $val, $subject);
            $body = str_replace($needle, (string) $val, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    /**
     * Send deposit confirmation email upon initial pre-order payment (S2-T7).
     */
    public function sendDepositConfirmation(Order $order): bool
    {
        $customer = $order->customer;
        $shop = $order->shop;

        if (!$customer || empty($customer->email) || !$shop) {
            return false;
        }

        $currency = $order->currency;
        $depositFormatted = "{$currency} " . number_format($order->paid_amount / 100, 2);
        $balanceFormatted = "{$currency} " . number_format($order->remainingBalance() / 100, 2);
        $schedule = $order->paymentSchedules()->where('status', '!=', 'cancelled')->first();
        $dueDateFormatted = $schedule && $schedule->due_at ? $schedule->due_at->format('M j, Y') : 'in 4 weeks';

        $rendered = $this->renderTemplate($shop, EmailTemplate::KEY_DEPOSIT_RECEIVED, [
            'customer_name' => $customer->email,
            'order_number' => $order->shopify_order_id ? str_replace('gid://shopify/Order/', '', $order->shopify_order_id) : (string) $order->id,
            'deposit_amount' => $depositFormatted,
            'balance_amount' => $balanceFormatted,
            'due_date' => $dueDateFormatted,
        ]);

        try {
            Mail::to($customer->email)->send(new OrderTransactionalMail($rendered['subject'], $rendered['body']));

            EmailLog::create([
                'order_id' => $order->id,
                'template_key' => EmailTemplate::KEY_DEPOSIT_RECEIVED,
                'to_email' => $customer->email,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed sending deposit confirmation email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send balance reminder email containing secure payment link (S2-T6, S2-T7).
     * Enforces deduplication: will not send if log already exists for this order + template.
     */
    public function sendBalanceReminder(PaymentSchedule $schedule, string $paymentLink): bool
    {
        $order = $schedule->order;
        if (!$order) {
            return false;
        }

        // Deduplication check: strictly one reminder per step (NFR-6, US-06)
        $alreadySent = EmailLog::where('order_id', $order->id)
            ->where('template_key', EmailTemplate::KEY_BALANCE_DUE)
            ->exists();

        if ($alreadySent) {
            Log::info("Skipping balance reminder for order {$order->id}: already sent.");
            return false;
        }

        $customer = $order->customer;
        $shop = $order->shop;
        if (!$customer || empty($customer->email) || !$shop) {
            return false;
        }

        $currency = $order->currency;
        $balanceFormatted = "{$currency} " . number_format($schedule->amount / 100, 2);
        $dueDateFormatted = $schedule->due_at ? $schedule->due_at->format('M j, Y') : 'soon';

        $rendered = $this->renderTemplate($shop, EmailTemplate::KEY_BALANCE_DUE, [
            'customer_name' => $customer->email,
            'order_number' => $order->shopify_order_id ? str_replace('gid://shopify/Order/', '', $order->shopify_order_id) : (string) $order->id,
            'balance_amount' => $balanceFormatted,
            'due_date' => $dueDateFormatted,
            'payment_link' => $paymentLink,
        ]);

        try {
            Mail::to($customer->email)->send(new OrderTransactionalMail($rendered['subject'], $rendered['body']));

            EmailLog::create([
                'order_id' => $order->id,
                'template_key' => EmailTemplate::KEY_BALANCE_DUE,
                'to_email' => $customer->email,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed sending balance reminder email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send balance paid receipt email when final balance settles (S2-T5, S2-T7).
     */
    public function sendBalancePaidReceipt(Order $order): bool
    {
        $customer = $order->customer;
        $shop = $order->shop;

        if (!$customer || empty($customer->email) || !$shop) {
            return false;
        }

        $currency = $order->currency;
        $balanceFormatted = "{$currency} " . number_format($order->paid_amount / 100, 2);

        $rendered = $this->renderTemplate($shop, EmailTemplate::KEY_BALANCE_PAID, [
            'customer_name' => $customer->email,
            'order_number' => $order->shopify_order_id ? str_replace('gid://shopify/Order/', '', $order->shopify_order_id) : (string) $order->id,
            'balance_amount' => $balanceFormatted,
        ]);

        try {
            Mail::to($customer->email)->send(new OrderTransactionalMail($rendered['subject'], $rendered['body']));

            EmailLog::create([
                'order_id' => $order->id,
                'template_key' => EmailTemplate::KEY_BALANCE_PAID,
                'to_email' => $customer->email,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed sending balance paid receipt email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Alias for sendBalancePaidReceipt used in automated runners.
     */
    public function sendBalancePaidConfirmation(Order $order): bool
    {
        return $this->sendBalancePaidReceipt($order);
    }

    /**
     * Alias for sendBalanceReminder used in automated runners.
     */
    public function sendBalanceDueReminder(PaymentSchedule $schedule, ?string $link = null): bool
    {
        $link = $link ?? \Illuminate\Support\Facades\URL::signedRoute('pay.balance', ['schedule' => $schedule->id], now()->addDays(7));
        return $this->sendBalanceReminder($schedule, $link);
    }
}
