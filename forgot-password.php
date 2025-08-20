<?php
session_start();
if (isset($_SESSION['member_id'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Chief.AI - MedicalNotes</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #1976d2;
            --primary-blue-hover: #1565c0;
            --primary-blue-light: #e3f2fd;
            --text-primary: #202124;
            --text-secondary: #5f6368;
            --text-muted: #9aa0a6;
            --border-color: #dadce0;
            --border-focus: #1976d2;
            --bg-white: #ffffff;
            --bg-gray: #f8f9fa;
            --success-color: #0f9d58;
            --shadow-sm: 0 1px 2px 0 rgba(60, 64, 67, 0.3), 0 1px 3px 1px rgba(60, 64, 67, 0.15);
            --shadow-md: 0 1px 3px 0 rgba(60, 64, 67, 0.3), 0 4px 8px 3px rgba(60, 64, 67, 0.15);
            --shadow-lg: 0 4px 4px 0 rgba(60, 64, 67, 0.3), 0 8px 16px 6px rgba(60, 64, 67, 0.15);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-white);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: var(--bg-white);
        }

        .auth-card {
            width: 100%;
            max-width: 450px;
            background: var(--bg-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-header {
            text-align: center;
            padding: 48px 48px 32px;
        }

        .logo-container {
            margin-bottom: 24px;
        }

        .logo {
            width: 72px;
            height: 72px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-hover));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: var(--shadow-md);
        }

        .logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .brand-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            letter-spacing: -0.025em;
        }

        .brand-subtitle {
            font-size: 16px;
            color: var(--text-secondary);
            font-weight: 400;
        }

        .auth-form {
            padding: 0 48px 48px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px;
            font-size: 16px;
            font-family: inherit;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background: var(--bg-white);
            color: var(--text-primary);
            transition: var(--transition);
            outline: none;
        }

        .form-input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input.error {
            border-color: #d93025;
            box-shadow: 0 0 0 2px rgba(217, 48, 37, 0.1);
        }

        .error-message {
            color: #d93025;
            font-size: 14px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .submit-btn {
            width: 100%;
            padding: 16px 24px;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: 16px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            background: var(--primary-blue-hover);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            background: var(--text-muted);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .submit-btn .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        .submit-btn.loading .spinner {
            display: block;
        }

        .submit-btn.loading .btn-text {
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .auth-links {
            text-align: center;
            margin-top: 24px;
        }

        .auth-link {
            color: var(--primary-blue);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
        }

        .auth-link:hover {
            color: var(--primary-blue-hover);
            text-decoration: underline;
        }

        .help-text {
            background: var(--bg-gray);
            border-radius: var(--radius-md);
            padding: 20px;
            margin: 24px 0;
            border: 1px solid var(--border-color);
        }

        .help-text h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .help-text p {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .help-text ul {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
            padding-left: 20px;
        }

        .help-text li {
            margin-bottom: 4px;
        }

        .help-text .material-icons-round {
            color: var(--primary-blue);
            font-size: 16px;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .auth-container {
                padding: 16px;
            }
            
            .auth-card {
                border-radius: var(--radius-lg);
            }
            
            .auth-header {
                padding: 32px 24px 24px;
            }
            
            .auth-form {
                padding: 0 24px 32px;
            }
            
            .logo {
                width: 64px;
                height: 64px;
            }
            
            .logo img {
                width: 40px;
                height: 40px;
            }
            
            .brand-title {
                font-size: 20px;
            }
        }

        /* Accessibility */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* Focus indicators */
        .form-input:focus,
        .submit-btn:focus {
            outline: 2px solid var(--primary-blue);
            outline-offset: 2px;
        }

        /* High contrast mode */
        @media (prefers-contrast: high) {
            :root {
                --border-color: #000000;
                --text-secondary: #000000;
            }
        }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-container">
                    <div class="logo">
                        <img src="/logo.jpeg" alt="Chief.AI Logo" width="48" height="48">
                    </div>
                    <h1 class="brand-title">Reset your password</h1>
                    <p class="brand-subtitle">Enter your email to receive reset instructions</p>
                </div>
            </div>

            <form class="auth-form" method="POST" action="reset-password.php" id="resetForm">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="Enter your email address"
                        required
                        autocomplete="email"
                        aria-describedby="email-error"
                    >
                    <div class="error-message" id="email-error" style="display: none;">
                        <span class="material-icons-round" style="font-size: 16px;">error</span>
                        <span>Please enter a valid email address</span>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="btn-text">Send Reset Link</span>
                        <div class="spinner"></div>
                    </button>
                </div>
            </form>

            <div class="help-text">
                <h4>
                    <span class="material-icons-round">help</span>
                    How password reset works
                </h4>
                <p>We'll send you an email with a secure link to reset your password:</p>
                <ul>
                    <li>Check your email inbox (and spam folder)</li>
                    <li>Click the reset link in the email</li>
                    <li>Create a new strong password</li>
                    <li>Sign in with your new password</li>
                </ul>
            </div>

            <div class="auth-links" style="padding-bottom: 32px;">
                <a href="login.php" class="auth-link">
                    <span class="material-icons-round" style="font-size: 16px; vertical-align: middle;">arrow_back</span>
                    Back to Sign In
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetForm');
            const emailInput = document.getElementById('email');
            const submitBtn = document.getElementById('submitBtn');

            // Form validation
            function validateEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            function showError(message) {
                const errorElement = document.getElementById('email-error');
                errorElement.textContent = message;
                errorElement.style.display = 'flex';
                emailInput.classList.add('error');
            }

            function hideError() {
                const errorElement = document.getElementById('email-error');
                errorElement.style.display = 'none';
                emailInput.classList.remove('error');
            }

            // Real-time validation
            emailInput.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    showError('Email is required');
                } else if (!validateEmail(this.value)) {
                    showError('Please enter a valid email address');
                } else {
                    hideError();
                }
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                const email = emailInput.value.trim();
                
                if (!email) {
                    e.preventDefault();
                    showError('Email is required');
                    return;
                }
                
                if (!validateEmail(email)) {
                    e.preventDefault();
                    showError('Please enter a valid email address');
                    return;
                }
                
                // Show loading state
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && document.activeElement.classList.contains('form-input')) {
                    form.submit();
                }
            });

            // Focus management
            emailInput.focus();
        });
    </script>
</body>
</html>
