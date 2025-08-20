<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

// Include configuration file
require_once 'config.php';

// Get session ID and credits from URL
$session_id = $_GET['session_id'] ?? '';
$credits = intval($_GET['credits'] ?? 0);
$plan = $_GET['plan'] ?? 'starter';

// Verify the payment with Stripe
$payment_verified = false;
$stripe_error = '';

if ($session_id && $credits > 0) {
    try {
        require 'vendor/autoload.php';
        \Stripe\Stripe::setApiKey('sk_test_Hn56gOIbeaGjuep2QLMoM2GA00eVGqgfrz');
        
        $session = \Stripe\Checkout\Session::retrieve($session_id);
        
        if ($session->payment_status === 'paid') {
            $payment_verified = true;
            
            // Update user credits in database
            try {
                $pdo = getDatabaseConnection();
                $stmt = $pdo->prepare("UPDATE members SET credits = credits + ? WHERE member_id = ?");
                $stmt->execute([$credits, $_SESSION['member_id']]);
                
                // Log the successful payment
                error_log("Payment successful: User {$_SESSION['member_id']} purchased {$credits} credits via {$plan} plan. Session: {$session_id}");
                
            } catch (Exception $e) {
                error_log("Error updating credits: " . $e->getMessage());
                $stripe_error = "Payment successful but credits update failed. Please contact support.";
            }
        } else {
            $stripe_error = "Payment not completed. Status: " . $session->payment_status;
        }
        
    } catch (Exception $e) {
        error_log("Stripe verification error: " . $e->getMessage());
        $stripe_error = "Unable to verify payment. Please contact support.";
    }
}

// Get updated user information
try {
    $credits = getUserCredits($_SESSION['member_id']);
    $userInfo = getUserInfo($_SESSION['member_id']);
    $username = $userInfo['username'];
} catch (Exception $e) {
    $credits = 0;
    $username = 'User';
    error_log("Error loading user data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Chief.AI MedicalNotes</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <style>
        :root {
            --primary-50: #EFF6FF;
            --primary-100: #DBEAFE;
            --primary-200: #BFDBFE;
            --primary-300: #93C5FD;
            --primary-400: #60A5FA;
            --primary-500: #3B82F6;
            --primary-600: #2563EB;
            --primary-700: #1D4ED8;
            --primary-800: #1E40AF;
            --primary-900: #1E3A8A;
            --accent-50: #ECFDF5;
            --accent-100: #D1FAE5;
            --accent-200: #A7F3D0;
            --accent-300: #6EE7B7;
            --accent-400: #34D399;
            --accent-500: #10B981;
            --accent-600: #059669;
            --accent-700: #047857;
            --accent-800: #065F46;
            --accent-900: #064E3B;
            --neutral-50: #F9FAFB;
            --neutral-100: #F3F4F6;
            --neutral-200: #E5E7EB;
            --neutral-300: #D1D5DB;
            --neutral-400: #9CA3AF;
            --neutral-500: #6B7280;
            --neutral-600: #4B5563;
            --neutral-700: #374151;
            --neutral-800: #1F2937;
            --neutral-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--neutral-50);
            color: var(--neutral-900);
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .success-card {
            background: white;
            border-radius: var(--radius-2xl);
            padding: 3rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--accent-200);
            text-align: center;
            margin: 2rem 0;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-500), var(--accent-600));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            margin: 0 auto 2rem;
        }

        .success-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--accent-600);
            margin-bottom: 1rem;
        }

        .success-subtitle {
            font-size: 1.25rem;
            color: var(--neutral-600);
            margin-bottom: 2rem;
        }

        .credits-info {
            background: var(--accent-50);
            border: 2px solid var(--accent-200);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin: 2rem 0;
        }

        .credits-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--accent-600);
            margin-bottom: 0.5rem;
        }

        .credits-label {
            font-size: 1.125rem;
            color: var(--accent-700);
            font-weight: 600;
        }

        .plan-info {
            background: var(--primary-50);
            border: 2px solid var(--primary-200);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .plan-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-700);
            margin-bottom: 0.5rem;
        }

        .plan-description {
            color: var(--primary-600);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--radius-lg);
            font-family: inherit;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--neutral-100);
            color: var(--neutral-700);
            border: 1px solid var(--neutral-300);
        }

        .btn-secondary:hover {
            background: var(--neutral-200);
            transform: translateY(-2px);
        }

        .error-message {
            background: #FEF2F2;
            border: 2px solid #FECACA;
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin: 2rem 0;
            color: #DC2626;
        }

        .error-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .success-card {
                padding: 2rem 1.5rem;
            }
            
            .success-title {
                font-size: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($payment_verified): ?>
            <!-- Success Message -->
            <div class="success-card">
                <div class="success-icon">
                    <span class="material-icons-round">check_circle</span>
                </div>
                
                <h1 class="success-title">Payment Successful!</h1>
                <p class="success-subtitle">
                    Thank you for your purchase. Your credits have been added to your account.
                </p>

                <!-- Credits Information -->
                <div class="credits-info">
                    <div class="credits-number"><?php echo $credits; ?></div>
                    <div class="credits-label">Credits Available</div>
                </div>

                <!-- Plan Information -->
                <div class="plan-info">
                    <div class="plan-title"><?php echo ucfirst($plan); ?> Plan</div>
                    <div class="plan-description">
                        You can now use these credits for MedicalVoice and MedicalVision processing.
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="modules.php" class="btn btn-primary">
                        <span class="material-icons-round">play_arrow</span>
                        Start Using Modules
                    </a>
                    <a href="credits.php" class="btn btn-secondary">
                        <span class="material-icons-round">account_balance_wallet</span>
                        View Credits
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <span class="material-icons-round">dashboard</span>
                        Go to Dashboard
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- Error Message -->
            <div class="success-card">
                <div class="success-icon" style="background: linear-gradient(135deg, #EF4444, #DC2626);">
                    <span class="material-icons-round">error</span>
                </div>
                
                <h1 class="success-title" style="color: #DC2626;">Payment Verification Failed</h1>
                <p class="success-subtitle">
                    We couldn't verify your payment. Please contact support if you believe this is an error.
                </p>

                <div class="error-message">
                    <div class="error-title">Error Details:</div>
                    <p><?php echo htmlspecialchars($stripe_error ?: 'Unknown error occurred'); ?></p>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="buy_credits.php" class="btn btn-primary">
                        <span class="material-icons-round">refresh</span>
                        Try Again
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <span class="material-icons-round">dashboard</span>
                        Go to Dashboard
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
