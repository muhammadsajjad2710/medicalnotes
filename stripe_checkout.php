<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // ✅ Show errors in browser

require 'vendor/autoload.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

// ✅ Stripe API Key (Test Mode)
\Stripe\Stripe::setApiKey('sk_test_Hn56gOIbeaGjuep2QLMoM2GA00eVGqgfrz');

// ✅ Get credits from URL parameters
$credits = intval($_GET['credits'] ?? 0);
$plan = $_GET['plan'] ?? 'starter';
$amount = intval($_GET['price'] ?? 999); // Amount in cents

if ($credits <= 0) {
    die("Invalid credits amount");
}

// ✅ Generate proper URLs for Stripe (must be absolute URLs)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

// ✅ Fix: Check if port is already included in HTTP_HOST
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, ':') !== false) {
    // Port is already included in HTTP_HOST (e.g., localhost:8000)
    $baseUrl = $protocol . '://' . $host;
} else {
    // Port is not included, check if we need to add it
    $port = $_SERVER['SERVER_PORT'];
    if ($port != '80' && $port != '443') {
        $baseUrl = $protocol . '://' . $host . ':' . $port;
    } else {
        $baseUrl = $protocol . '://' . $host;
    }
}

// ✅ Ensure URLs are properly formatted for Stripe
$successUrl = $baseUrl . '/payment_success.php?session_id={CHECKOUT_SESSION_ID}&credits=' . $credits . '&plan=' . $plan;
$cancelUrl = $baseUrl . '/buy_credits.php';

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => "$credits MedicalNotes Credits - $plan Plan",
                    'description' => "AI-powered medical processing credits for MedicalVoice and MedicalVision"
                ],
                'unit_amount' => $amount,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
        'metadata' => [
            'credits' => $credits,
            'plan' => $plan,
            'user_id' => $_SESSION['member_id'] ?? 'unknown'
        ]
    ]);

    // ✅ Redirect to Stripe Checkout
    header("Location: " . $checkout_session->url);
    exit;

} catch (\Stripe\Exception\ApiErrorException $e) {
    // Styled error page
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
      <meta charset='UTF-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
      <title>Payment Error - Chief.AI MedicalNotes</title>
      <style>
        body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f8fafc;margin:0;color:#1a202c}
        .wrap{max-width:780px;margin:6rem auto;padding:0 1rem}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
        .head{background:linear-gradient(135deg,#ff6b6b,#ee5a52);color:#fff;padding:1.25rem 1.5rem;border-radius:16px 16px 0 0}
        .body{padding:1.5rem}
        h1{margin:0;font-size:1.25rem}
        .meta{background:#fff5f5;border:1px solid #fed7d7;border-radius:12px;padding:1rem;margin-top:1rem}
        .meta p{margin:.25rem 0}
        .btn{display:inline-block;margin-top:1rem;background:#667eea;border:none;color:#fff;padding:.75rem 1.25rem;border-radius:10px;text-decoration:none;font-weight:600}
        .btn:hover{filter:brightness(1.05)}
      </style>
    </head>
    <body>
      <div class='wrap'>
        <div class='card' role='alert' aria-live='assertive'>
          <div class='head'><h1>Payment Error</h1></div>
          <div class='body'>
            <p><strong>Stripe reported an error.</strong></p>
            <div class='meta'>
              <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
              <p><strong>Code:</strong> " . htmlspecialchars($e->getStripeCode()) . "</p>
              <p><strong>Success URL:</strong> " . htmlspecialchars($successUrl) . "</p>
              <p><strong>Cancel URL:</strong> " . htmlspecialchars($cancelUrl) . "</p>
            </div>
            <a class='btn' href='buy_credits.php' aria-label='Back to buy credits'>← Back to Buy Credits</a>
          </div>
        </div>
      </div>
    </body>
    </html>";
} catch (Exception $e) {
    // Styled generic error page
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
      <meta charset='UTF-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
      <title>Unexpected Error - Chief.AI MedicalNotes</title>
      <style>
        body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f8fafc;margin:0;color:#1a202c}
        .wrap{max-width:780px;margin:6rem auto;padding:0 1rem}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
        .head{background:linear-gradient(135deg,#f093fb,#f5576c);color:#fff;padding:1.25rem 1.5rem;border-radius:16px 16px 0 0}
        .body{padding:1.5rem}
        h1{margin:0;font-size:1.25rem}
        .meta{background:#f7fafc;border:1px solid #e2e8f0;border-radius:12px;padding:1rem;margin-top:1rem}
        .btn{display:inline-block;margin-top:1rem;background:#667eea;border:none;color:#fff;padding:.75rem 1.25rem;border-radius:10px;text-decoration:none;font-weight:600}
        .btn:hover{filter:brightness(1.05)}
      </style>
    </head>
    <body>
      <div class='wrap'>
        <div class='card' role='alert' aria-live='assertive'>
          <div class='head'><h1>Unexpected Error</h1></div>
          <div class='body'>
            <p><strong>An unexpected error occurred while starting checkout.</strong></p>
            <div class='meta'>
              <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            </div>
            <a class='btn' href='buy_credits.php' aria-label='Back to buy credits'>← Back to Buy Credits</a>
          </div>
        </div>
      </div>
    </body>
    </html>";
}
?>
