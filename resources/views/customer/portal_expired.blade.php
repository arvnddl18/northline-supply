<!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Link Expired — Northwind Ceramics</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/css/northwind.css">
</head>
<body>
    <div class="customer-page page-enter">
        <header class="customer-header">
            <a href="/" class="customer-brand">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
                    <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
                </svg>
                <span>NORTHWIND <small>CERAMICS</small></span>
            </a>
            <span class="handmade-pill">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/>
                </svg>
                Handmade in Australia
            </span>
        </header>

        <main id="main-content" class="expired-card">
            <div class="expired-symbol">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
                </svg>
            </div>

            <p class="eyebrow">SECURE ACCESS · LINK EXPIRED</p>
            <h1>A fresh note<br>from the studio.</h1>
            <p>This secure access link has expired or has already been used. Please request a fresh magic link to access your stoneware pre-orders and Ceramic Club deliveries.</p>

            <form action="/portal/request-link" method="POST" class="magic-link-form">
                @csrf
                <label class="field-label" for="portal-email">
                    <span>Email address</span>
                    <input type="email" id="portal-email" name="email" placeholder="you@example.com" autocomplete="email" required>
                </label>
                <button class="button button-primary button-full" type="submit">
                    Email me a new access link
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                </button>
            </form>

            <div class="security-foot">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/>
                </svg>
                For your privacy, we’ll display the confirmation regardless of whether an account is found.
            </div>

            <a class="contact-studio" href="mailto:emma@northwindceramics.com.au">
                Need a hand? Contact Emma Carter
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
            </a>
        </main>

        <footer class="customer-footer">
            <span>Made with care in Melbourne &amp; Byron Bay.</span>
            <span>Northwind Ceramics © 2026</span>
        </footer>
    </div>
</body>
</html>
