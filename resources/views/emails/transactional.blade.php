<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subjectLine }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #202223; margin: 0; padding: 24px; background: #f6f6f7; }
        .card { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 32px; border: 1px solid #e1e3e5; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .logo { font-size: 20px; font-weight: 700; color: #1a1a1a; margin-bottom: 24px; text-transform: uppercase; letter-spacing: 0.05em; }
        .content { white-space: pre-wrap; font-size: 15px; }
        .footer { margin-top: 32px; font-size: 12px; color: #6d7175; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">Northwind Ceramics</div>
        <div class="content">{!! nl2br(e($bodyContent)) !!}</div>
        <div class="footer">
            Handcrafted with care in Melbourne, Australia.<br>
            Questions? Reply directly to this email.
        </div>
    </div>
</body>
</html>
