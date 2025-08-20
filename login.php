<?php
session_start();
if (isset($_SESSION['member_id'])) {
    header("Location: index.php");
    exit;
}

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = "Invalid request. Please try again.";
    } else {
        // Process login
        require_once 'config.php';
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error_message = "Please enter both username and password.";
        } else {
            try {
                $pdo = getDatabaseConnection();
                if ($pdo) {
                    $stmt = $pdo->prepare("SELECT member_id, username, password_hash FROM members WHERE username = ?");
                    $stmt->execute([$username]);
                    $user = $stmt->fetch();
                    
                    if ($user && password_verify($password, $user['password_hash'])) {
                        $_SESSION['member_id'] = $user['member_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerate token
                        
                        // Log successful login
                        error_log("Successful login: User ID {$user['member_id']}, Username: $username, IP: " . $_SERVER['REMOTE_ADDR']);
                        
                        header("Location: index.php");
                        exit;
                    } else {
                        $error_message = "Invalid username or password.";
                        error_log("Failed login attempt: Username: $username, IP: " . $_SERVER['REMOTE_ADDR']);
                    }
                } else {
                    $error_message = "System temporarily unavailable. Please try again.";
                }
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $error_message = "System error. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to Chief.AI - MedicalNotes</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <!-- Unified Design System -->
    <link rel="stylesheet" href="design-system.css">
    
    <style>
        /* Login-specific styles using design system tokens */
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-6);
            background: var(--neutral-50);
        }

        .auth-card {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: slideUp 0.4s var(--transition-bounce);
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
            padding: var(--space-12) var(--space-12) var(--space-8);
            background: linear-gradient(135deg, var(--primary-50), var(--secondary-50));
        }

        .logo-container {
            margin-bottom: var(--space-6);
        }

        .logo {
            width: 72px;
            height: 72px;
            border-radius: var(--radius-2xl);
            background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--space-4);
            box-shadow: var(--shadow-lg);
        }

        .logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .brand-title {
            font-size: var(--text-3xl);
            font-weight: 800;
            color: var(--neutral-900);
            margin-bottom: var(--space-2);
            background: linear-gradient(135deg, var(--primary-600), var(--secondary-600));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-subtitle {
            font-size: var(--text-lg);
            color: var(--neutral-600);
            font-weight: 500;
        }

        .auth-form {
            padding: var(--space-8) var(--space-8) var(--space-8);
        }

        .form-group {
            margin-bottom: var(--space-6);
        }

        .form-label {
            display: block;
            font-size: var(--text-sm);
            font-weight: 600;
            color: var(--neutral-700);
            margin-bottom: var(--space-2);
        }

        .form-input {
            width: 100%;
            padding: var(--space-4);
            border: 2px solid var(--neutral-200);
            border-radius: var(--radius-lg);
            font-size: var(--text-base);
            font-family: inherit;
            transition: all var(--transition-normal);
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px var(--primary-100);
        }

        .form-input.error {
            border-color: var(--error-500);
            box-shadow: 0 0 0 3px var(--error-100);
        }

        .error-message {
            color: var(--error-600);
            font-size: var(--text-sm);
            margin-top: var(--space-2);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            padding: var(--space-4) var(--space-6);
            border: none;
            border-radius: var(--radius-lg);
            font-size: var(--text-base);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-bounce);
            box-shadow: var(--shadow-md);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            padding: var(--space-6) var(--space-8);
            border-top: 1px solid var(--neutral-200);
            background: var(--neutral-50);
        }

        .auth-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: var(--space-4);
        }

        .auth-link {
            color: var(--primary-600);
            text-decoration: none;
            font-size: var(--text-sm);
            font-weight: 500;
            transition: color var(--transition-normal);
        }

        .auth-link:hover {
            color: var(--primary-700);
        }

        .divider {
            color: var(--neutral-400);
            font-size: var(--text-sm);
        }

        @media (max-width: 640px) {
            .auth-card {
                margin: var(--space-4);
                border-radius: var(--radius-xl);
            }
            
            .auth-header {
                padding: var(--space-8) var(--space-6) var(--space-6);
            }
            
            .auth-form {
                padding: var(--space-6);
            }
            
            .auth-links {
                flex-direction: column;
                gap: var(--space-3);
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
                        <img src="logo.jpeg" alt="Chief.AI Logo">
                    </div>
                </div>
                <h1 class="brand-title">Chief.AI</h1>
                <p class="brand-subtitle">MedicalNotes</p>
            </div>

            <form class="auth-form" method="POST" action="" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <?php if (isset($error_message)): ?>
                    <div class="error-message" role="alert">
                        <span class="material-icons-round" style="font-size: 16px;">error</span>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input <?php echo isset($error_message) ? 'error' : ''; ?>"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                        autocomplete="username"
                        aria-describedby="<?php echo isset($error_message) ? 'error-message' : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input <?php echo isset($error_message) ? 'error' : ''; ?>"
                        required
                        autocomplete="current-password"
                        aria-describedby="<?php echo isset($error_message) ? 'error-message' : ''; ?>"
                    >
                </div>

                <button type="submit" class="btn-login">
                    Sign In
                </button>
            </form>

            <div class="auth-footer">
                <div class="auth-links">
                    <a href="forgot-password.php" class="auth-link">Forgot Password?</a>
                    <span class="divider">•</span>
                    <a href="register.php" class="auth-link">Create Account</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form validation and accessibility enhancements
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.auth-form');
            const inputs = form.querySelectorAll('.form-input');
            
            // Real-time validation
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        this.classList.remove('error');
                    }
                });
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!validateField(input)) {
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    // Focus first error
                    const firstError = form.querySelector('.form-input.error');
                    if (firstError) {
                        firstError.focus();
                    }
                }
            });
            
            function validateField(field) {
                const value = field.value.trim();
                let isValid = true;
                
                // Remove existing error state
                field.classList.remove('error');
                
                // Required field validation
                if (field.hasAttribute('required') && !value) {
                    field.classList.add('error');
                    isValid = false;
                }
                
                // Username validation
                if (field.id === 'username' && value && value.length < 3) {
                    field.classList.add('error');
                    isValid = false;
                }
                
                // Password validation
                if (field.id === 'password' && value && value.length < 6) {
                    field.classList.add('error');
                    isValid = false;
                }
                
                return isValid;
            }
            
            // Accessibility: Announce errors to screen readers
            const errorMessage = document.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.setAttribute('aria-live', 'polite');
            }
        });
    </script>
</body>
</html>
