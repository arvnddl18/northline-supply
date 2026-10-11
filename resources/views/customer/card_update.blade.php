<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Payment Method — Northwind Ceramics</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #faf9f6; color: #202223; margin: 0; padding: 40px 20px; }
        .card { max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e1e3e5; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 32px; }
        h1 { font-size: 20px; margin-top: 0; color: #1a1a1a; }
        p { color: #6d7175; font-size: 14px; line-height: 1.5; }
        #card-element { border: 1px solid #c9cccf; border-radius: 6px; padding: 12px; margin-top: 16px; background: #fff; }
        #card-errors { color: #d82c0d; font-size: 13px; margin-top: 8px; }
        button { width: 100%; margin-top: 24px; padding: 12px; background: #008060; color: #fff; font-size: 15px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; transition: background 0.15s; }
        button:hover { background: #006e52; }
        .success-box { display: none; background: #f0f7f4; border: 1px solid #b3d9c7; border-radius: 6px; padding: 16px; margin-top: 16px; color: #004d3d; font-size: 14px; text-align: center; }
    </style>
</head>
<body>
<div class="card">
    <h1>Update Your Payment Card</h1>
    <p>Please enter your updated credit or debit card details below to continue your scheduled installment of <strong>${{ number_format($schedule->amount / 100, 2) }} {{ $order->currency }}</strong>.</p>
    
    <div id="payment-form">
        <div id="card-element"></div>
        <div id="card-errors" role="alert"></div>
        <button id="submit-btn">Save New Card</button>
    </div>

    <div id="success-box" class="success-box">
        <strong>Card Updated Successfully</strong><br>
        Your scheduled payments will process automatically with this card. Thank you!
    </div>
</div>

<script>
    const stripe = Stripe("{{ $publishableKey }}");
    const elements = stripe.elements();
    const cardElement = elements.create("card", {
        style: {
            base: { fontSize: "16px", color: "#202223", "::placeholder": { color: "#8c9196" } }
        }
    });
    cardElement.mount("#card-element");

    const formBtn = document.getElementById("submit-btn");
    const errorEl = document.getElementById("card-errors");
    const successBox = document.getElementById("success-box");
    const paymentForm = document.getElementById("payment-form");

    formBtn.addEventListener("click", async (e) => {
        e.preventDefault();
        formBtn.disabled = true;
        formBtn.textContent = "Verifying...";
        errorEl.textContent = "";

        const { setupIntent, error } = await stripe.confirmCardSetup(
            "{{ $clientSecret }}",
            { payment_method: { card: cardElement } }
        );

        if (error) {
            errorEl.textContent = error.message;
            formBtn.disabled = false;
            formBtn.textContent = "Save New Card";
        } else {
            // Send token to backend
            const updateUrl = window.location.href;
            const res = await fetch(updateUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ payment_method_id: setupIntent.payment_method })
            });

            if (res.ok) {
                paymentForm.style.display = "none";
                successBox.style.display = "block";
            } else {
                errorEl.textContent = "Failed to update card. Please refresh and try again.";
                formBtn.disabled = false;
                formBtn.textContent = "Save New Card";
            }
        }
    });
</script>
</body>
</html>
