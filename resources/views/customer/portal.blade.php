<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Account Portal — Northwind Ceramics</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #faf9f6; color: #202223; margin: 0; padding: 40px 20px; }
        .container { max-width: 720px; margin: 0 auto; }
        .header { margin-bottom: 24px; }
        h1 { font-size: 24px; color: #1a1a1a; margin-bottom: 6px; }
        p.subtitle { color: #6d7175; font-size: 14px; margin-top: 0; }
        .card { background: #ffffff; border-radius: 10px; border: 1px solid #e1e3e5; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 24px; margin-bottom: 24px; }
        .card h2 { font-size: 17px; margin-top: 0; border-bottom: 1px solid #f1f2f3; padding-bottom: 12px; }
        .item-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f6f6f7; }
        .item-row:last-child { border-bottom: none; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .badge-paid { background: #e3f1df; color: #108043; }
        .badge-pending { background: #fff5ea; color: #b95000; }
        .badge-active { background: #e3f1df; color: #108043; }
        .badge-paused { background: #f6f6f7; color: #6d7175; }
        .btn { display: inline-block; padding: 6px 14px; background: #008060; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 500; }
        .btn:hover { background: #006e52; }
        .btn-outline { background: transparent; border: 1px solid #c9cccf; color: #202223; }
        .btn-outline:hover { background: #f6f6f7; }
        .empty { color: #8c9196; font-size: 14px; padding: 12px 0; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Welcome, {{ $customer->email }}</h1>
        <p class="subtitle">Manage your handmade pottery pre-orders, balances, and Ceramic Club memberships.</p>
    </div>

    <!-- Active Ceramic Club Subscriptions -->
    <div class="card">
        <h2>Ceramic Club Subscriptions</h2>
        @forelse($subscriptions as $sub)
            <div class="item-row">
                <div>
                    <strong>{{ $sub->plan?->name ?? 'Clay Subscription' }}</strong>
                    <div style="font-size: 13px; color: #6d7175;">
                        Next renewal: {{ $sub->next_renewal_at ? $sub->next_renewal_at->format('M d, Y') : 'N/A' }}
                    </div>
                </div>
                <div>
                    <span class="badge {{ $sub->isActive() ? 'badge-active' : 'badge-paused' }}">{{ $sub->status }}</span>
                </div>
            </div>
        @empty
            <div class="empty">No active memberships found.</div>
        @endforelse
    </div>

    <!-- Pre-orders & Scheduled Balances -->
    <div class="card">
        <h2>Orders & Payment Schedules</h2>
        @forelse($orders as $order)
            <div class="item-row">
                <div>
                    <strong>Order #{{ $order->id }}</strong>
                    <div style="font-size: 13px; color: #6d7175;">
                        Total: ${{ number_format($order->total_amount / 100, 2) }} {{ $order->currency }}
                        • Paid: ${{ number_format($order->paid_amount / 100, 2) }}
                    </div>
                </div>
                <div style="text-align: right;">
                    <span class="badge {{ $order->payment_status === 'paid' ? 'badge-paid' : 'badge-pending' }}">
                        {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                    </span>
                    @if($order->remainingBalance() > 0)
                        @php
                            $pendingSchedule = $order->paymentSchedules->where('status', 'pending')->first();
                        @endphp
                        @if($pendingSchedule)
                            <div style="margin-top: 6px;">
                                <a href="{{ URL::signedRoute('pay.balance', ['schedule' => $pendingSchedule->id], now()->addDays(7)) }}" class="btn">
                                    Pay Balance (${{ number_format($pendingSchedule->amount / 100, 2) }})
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="empty">No recent orders found.</div>
        @endforelse
    </div>
</div>
</body>
</html>
