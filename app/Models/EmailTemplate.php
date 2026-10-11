<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use HasFactory;

    public const KEY_DEPOSIT_RECEIVED = 'deposit_received';
    public const KEY_BALANCE_DUE = 'balance_due';
    public const KEY_BALANCE_PAID = 'balance_paid';

    protected $fillable = [
        'shop_id',
        'key',
        'subject',
        'body',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get default subject line for system keys.
     */
    public static function defaultSubject(string $key): string
    {
        return match ($key) {
            self::KEY_DEPOSIT_RECEIVED => 'Your Northwind Ceramics pre-order deposit is confirmed (#{order_number})',
            self::KEY_BALANCE_DUE => 'Reminder: Balance payment due for your order #{order_number}',
            self::KEY_BALANCE_PAID => 'Payment complete! Your order #{order_number} is ready for packing',
            default => 'Order Update (#{order_number})',
        };
    }

    /**
     * Get default body template for system keys.
     */
    public static function defaultBody(string $key): string
    {
        return match ($key) {
            self::KEY_DEPOSIT_RECEIVED => "Hi {customer_name},\n\nThank you for placing your pre-order with Northwind Ceramics! We have received your deposit of {deposit_amount}.\n\nYour remaining balance of {balance_amount} is due around {due_date}. We will send you a reminder when production is finishing.\n\nWarm regards,\nEmma & the Studio Team",
            self::KEY_BALANCE_DUE => "Hi {customer_name},\n\nYour handmade pottery is almost complete! Your balance of {balance_amount} for order #{order_number} is due on {due_date}.\n\nYou can pay your balance securely using the link below:\n{payment_link}\n\nWarm regards,\nNorthwind Ceramics",
            self::KEY_BALANCE_PAID => "Hi {customer_name},\n\nWe have received your balance payment of {balance_amount}. Your order #{order_number} is fully paid and will now be carefully packed and dispatched.\n\nThank you for supporting Australian handmade ceramics!\n\nNorthwind Ceramics",
            default => "Order #{order_number} update.\n\n{payment_link}",
        };
    }
}
