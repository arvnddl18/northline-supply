<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderTransactionalMail;
use App\Models\EmailTemplate;
use App\Models\Shop;
use App\Services\Email\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailTemplateController extends Controller
{
    public function __construct(
        protected EmailService $emailService
    ) {}

    /**
     * List all email templates for the current shop (S2-T7).
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $keys = [
            EmailTemplate::KEY_DEPOSIT_RECEIVED,
            EmailTemplate::KEY_BALANCE_DUE,
            EmailTemplate::KEY_BALANCE_PAID,
        ];

        $templates = [];
        foreach ($keys as $key) {
            $record = $shop->emailTemplates()->where('key', $key)->first();
            $templates[] = [
                'key' => $key,
                'name' => match ($key) {
                    EmailTemplate::KEY_DEPOSIT_RECEIVED => 'Deposit Received Confirmation',
                    EmailTemplate::KEY_BALANCE_DUE => 'Balance Due Reminder',
                    EmailTemplate::KEY_BALANCE_PAID => 'Final Balance Paid Receipt',
                    default => $key,
                },
                'subject' => $record ? $record->subject : EmailTemplate::defaultSubject($key),
                'body' => $record ? $record->body : EmailTemplate::defaultBody($key),
                'is_customized' => !is_null($record),
                'available_tokens' => [
                    '{customer_name}',
                    '{order_number}',
                    '{deposit_amount}',
                    '{balance_amount}',
                    '{due_date}',
                    '{payment_link}',
                ],
            ];
        }

        return response()->json([
            'templates' => $templates,
        ]);
    }

    /**
     * Update/customize an email template for the shop.
     */
    public function update(Request $request, string $key): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $validKeys = [
            EmailTemplate::KEY_DEPOSIT_RECEIVED,
            EmailTemplate::KEY_BALANCE_DUE,
            EmailTemplate::KEY_BALANCE_PAID,
        ];

        if (!in_array($key, $validKeys, true)) {
            return response()->json(['error' => 'Invalid template key.'], 404);
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
        ]);

        $template = $shop->emailTemplates()->updateOrCreate(
            ['key' => $key],
            [
                'subject' => $validated['subject'],
                'body' => $validated['body'],
            ]
        );

        return response()->json([
            'status' => 'saved',
            'template' => [
                'key' => $template->key,
                'subject' => $template->subject,
                'body' => $template->body,
            ],
        ]);
    }

    /**
     * Preview rendered template with sample token values.
     */
    public function preview(Request $request, string $key): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $sampleTokens = [
            'customer_name' => 'Sarah Jenkins',
            'order_number' => '1042',
            'deposit_amount' => "{$shop->currency} 60.00",
            'balance_amount' => "{$shop->currency} 140.00",
            'due_date' => now()->addDays(28)->format('M j, Y'),
            'payment_link' => 'https://' . $shop->shopify_domain . '/pay/balance/sample-token',
        ];

        $rendered = $this->emailService->renderTemplate($shop, $key, $sampleTokens);

        return response()->json([
            'key' => $key,
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
        ]);
    }

    /**
     * Send a test email of the specified template to the merchant.
     */
    public function testSend(Request $request, string $key): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $sampleTokens = [
            'customer_name' => 'Studio Test Recipient',
            'order_number' => 'TEST-001',
            'deposit_amount' => "{$shop->currency} 60.00",
            'balance_amount' => "{$shop->currency} 140.00",
            'due_date' => now()->addDays(30)->format('M j, Y'),
            'payment_link' => 'https://' . $shop->shopify_domain . '/pay/balance/sample-preview-link',
        ];

        $rendered = $this->emailService->renderTemplate($shop, $key, $sampleTokens);

        try {
            Mail::to($validated['email'])->send(
                new OrderTransactionalMail($rendered['subject'], $rendered['body'])
            );

            return response()->json([
                'status' => 'sent',
                'recipient' => $validated['email'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Unable to send email: ' . $e->getMessage(),
            ], 500);
        }
    }
}
