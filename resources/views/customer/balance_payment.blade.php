<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Northwind Ceramics — Complete Your Pre-order Balance</title>
    <style>
        :root {
            --primary: #1a1a1a;
            --accent: #9c6644;
            --bg: #f8f6f0;
            --card-bg: #ffffff;
            --border: #e6e2dd;
            --text-muted: #6b635b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg);
            color: var(--primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .container {
            width: 100%;
            max-width: 480px;
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 32px 28px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .brand {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 8px;
        }
        .title {
            font-size: 22px;
            font-weight: 600;
            color: var(--primary);
        }
        .message-card {
            background: #fdfaf6;
            border: 1px solid #ebd9c9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
            text-align: center;
        }
        .badge {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 8px;
        }
        .badge-success { background: #e3f1df; color: #1e7025; }
        .badge-warning { background: #fef1d6; color: #875700; }
        .badge-danger { background: #fde8e8; color: #991b1b; }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }
        .summary-table td {
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }
        .summary-table td:last-child {
            text-align: right;
            font-weight: 500;
        }
        .summary-table tr.total td {
            font-size: 16px;
            font-weight: 700;
            border-bottom: none;
            padding-top: 14px;
            color: var(--accent);
        }
        .btn-pay {
            display: block;
            width: 100%;
            background: var(--accent);
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            padding: 14px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s ease;
        }
        .btn-pay:hover {
            background: #845233;
        }
        .footer-note {
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 20px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand">Northwind Ceramics</div>
            <h1 class="title">Handmade Pre-order Settlement</h1>
        </div>

        @if(!empty($error))
            <div class="message-card">
                <span class="badge badge-danger">Link Notice</span>
                <p style="margin-top: 6px; font-size: 14px; color: #4a4a4a;">{{ $error }}</p>
            </div>
        @elseif($isPaid)
            <div class="message-card">
                <span class="badge badge-success">Completed</span>
                <h3 style="margin-top: 6px; font-size: 17px;">Balance Already Paid</h3>
                <p style="margin-top: 6px; font-size: 14px; color: #5a5a5a;">
                    Thank you! The final balance for this order has been settled in full. Your pottery is being prepared for dispatch.
                </p>
            </div>
        @else
            <div class="message-card">
                <span class="badge badge-warning">Balance Due</span>
                <p style="margin-top: 4px; font-size: 14px; color: #5a5a5a;">
                    Due by {{ $dueDate }}
                </p>
            </div>

            <table class="summary-table">
                <tr>
                    <td>Order Reference</td>
                    <td>{{ $orderNumber }}</td>
                </tr>
                <tr>
                    <td>Total Order Value</td>
                    <td>{{ $currency }} {{ $totalFormatted }}</td>
                </tr>
                <tr>
                    <td>Deposit Captured</td>
                    <td style="color: #1e7025;">- {{ $currency }} {{ $depositFormatted }}</td>
                </tr>
                <tr class="total">
                    <td>Remaining Balance</td>
                    <td>{{ $currency }} {{ $balanceFormatted }}</td>
                </tr>
            </table>

            <form method="POST" action="{{ $checkoutUrl }}">
                @csrf
                <button type="submit" class="btn-pay">
                    Pay Balance Now ({{ $currency }} {{ $balanceFormatted }})
                </button>
            </form>
        @endif

        <div class="footer-note">
            Secure card processing powered by Stripe.<br>
            Handmade pottery crafted in Melbourne, Australia.
        </div>
    </div>
</body>
</html>
