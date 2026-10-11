param(
    [string]$ForwardTo = "http://localhost:8000/webhooks/stripe",
    [string]$Events = "checkout.session.completed,payment_intent.succeeded,payment_intent.payment_failed,charge.refunded,customer.subscription.created,customer.subscription.updated,customer.subscription.deleted"
)

# Resolve stripe executable
$stripeCmd = Get-Command stripe -ErrorAction SilentlyContinue
if (-not $stripeCmd) {
    $fallbackPath = "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\Stripe.StripeCli_Microsoft.Winget.Source_8wekyb3d8bbwe\stripe.exe"
    if (Test-Path $fallbackPath) {
        $stripeExe = $fallbackPath
    } else {
        Write-Error "Stripe CLI not found in PATH or standard location. Run 'winget install Stripe.StripeCLI' to install."
        exit 1
    }
} else {
    $stripeExe = $stripeCmd.Source
}

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " Northwind Ceramics - Stripe Webhook Forwarding" -ForegroundColor Cyan
Write-Host " Target Endpoint: $ForwardTo" -ForegroundColor Green
Write-Host " Forwarding Events: $Events" -ForegroundColor DarkGray
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "Note: When Stripe CLI connects, copy the displayed 'whsec_...' secret into STRIPE_WEBHOOK_SECRET in your .env file." -ForegroundColor Yellow
Write-Host ""

& $stripeExe listen --forward-to $ForwardTo --events $Events
