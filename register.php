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

$error_message = null;
$success_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = "Invalid request. Please try again.";
    } else {
        // Process registration
        require_once 'config.php';
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error_message = "All fields are required.";
        } elseif (strlen($username) < 3) {
            $error_message = "Username must be at least 3 characters long.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address.";
        } elseif (strlen($password) < 8) {
            $error_message = "Password must be at least 8 characters long.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } else {
            try {
                $pdo = getDatabaseConnection();
                if ($pdo) {
                    // Check if username or email already exists
                    $stmt = $pdo->prepare("SELECT member_id FROM members WHERE username = ? OR email = ?");
                    $stmt->execute([$username, $email]);
                    
                    if ($stmt->rowCount() > 0) {
                        $error_message = "Username or email already exists.";
                    } else {
                        // Create new user
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO members (username, email, password_hash, credits) VALUES (?, ?, ?, 10)");
                        $stmt->execute([$username, $email, $password_hash]);
                        
                        $user_id = $pdo->lastInsertId();
                        
                        // Log successful registration
                        error_log("New user registered: User ID $user_id, Username: $username, Email: $email, IP: " . $_SERVER['REMOTE_ADDR']);
                        
                        $success_message = "Account created successfully! You can now sign in.";
                        
                        // Clear form data
                        $_POST = [];
                    }
                } else {
                    $error_message = "System temporarily unavailable. Please try again.";
                }
            } catch (Exception $e) {
                error_log("Registration error: " . $e->getMessage());
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
    <title>Create Account - Chief.AI - MedicalNotes</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <!-- Unified Design System -->
    <link rel="stylesheet" href="design-system.css">
    
    <style>
        /* Register-specific styles using design system tokens */
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
            max-width: 520px;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-4);
        }

        .form-group {
            margin-bottom: var(--space-6);
        }

        .form-group.full-width {
            grid-column: 1 / -1;
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

        .form-input.success {
            border-color: var(--success-500);
            box-shadow: 0 0 0 3px var(--success-100);
        }

        .error-message {
            color: var(--error-600);
            font-size: var(--text-sm);
            margin-top: var(--space-2);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .success-message {
            color: var(--success-600);
            font-size: var(--text-sm);
            margin-top: var(--space-2);
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-3);
            background: var(--success-50);
            border: 1px solid var(--success-200);
            border-radius: var(--radius-lg);
        }

        .password-requirements {
            font-size: var(--text-xs);
            color: var(--neutral-500);
            margin-top: var(--space-2);
            line-height: 1.4;
        }

        .btn-register {
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

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-register:active {
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
            justify-content: center;
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
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
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

                <?php if (isset($success_message)): ?>
                    <div class="success-message" role="alert">
                        <span class="material-icons-round" style="font-size: 16px;">check_circle</span>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input <?php echo isset($error_message) && (empty($_POST['username'] ?? '') || (isset($_POST['username']) && strlen($_POST['username']) < 3)) ? 'error' : ''; ?>"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            required
                            autocomplete="username"
                            minlength="3"
                        >
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input <?php echo isset($error_message) && (empty($_POST['email'] ?? '') || (isset($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))) ? 'error' : ''; ?>"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input <?php echo isset($error_message) && (empty($_POST['password'] ?? '') || (isset($_POST['password']) && strlen($_POST['password']) < 8)) ? 'error' : ''; ?>"
                        required
                        autocomplete="new-password"
                        minlength="8"
                    >
                    <div class="password-requirements">
                        Must be at least 8 characters long
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-input <?php echo isset($error_message) && (empty($_POST['confirm_password'] ?? '') || (isset($_POST['password'], $_POST['confirm_password']) && $_POST['password'] !== $_POST['confirm_password'])) ? 'error' : ''; ?>"
                        required
                        autocomplete="new-password"
                        minlength="8"
                    >
                </div>

                <button type="submit" class="btn-register">
                    Create Account
                </button>
            </form>

            <div class="auth-footer">
                <div class="auth-links">
                    <span>Already have an account?</span>
                    <a href="login.php" class="auth-link">Sign In</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form validation and accessibility enhancements
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.auth-form');
            const inputs = form.querySelectorAll('.form-input');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // Real-time validation
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        this.classList.remove('error');
                    }
                    if (this.classList.contains('success')) {
                        this.classList.remove('success');
                    }
                });
            });
            
            // Password confirmation validation
            confirmPassword.addEventListener('input', function() {
                if (password.value && this.value) {
                    if (password.value === this.value) {
                        this.classList.remove('error');
                        this.classList.add('success');
                    } else {
                        this.classList.remove('success');
                        this.classList.add('error');
                    }
                }
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!validateField(input)) {
                        isValid = false;
                    }
                });
                
                // Additional password confirmation check
                if (password.value !== confirmPassword.value) {
                    confirmPassword.classList.add('error');
                    isValid = false;
                }
                
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
                
                // Remove existing states
                field.classList.remove('error', 'success');
                
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
                
                // Email validation
                if (field.id === 'email' && value && !field.checkValidity()) {
                    field.classList.add('error');
                    isValid = false;
                }
                
                // Password validation
                if (field.id === 'password' && value && value.length < 8) {
                    field.classList.add('error');
                    isValid = false;
                }
                
                // Add success state for valid fields
                if (isValid && value) {
                    field.classList.add('success');
                }
                
                return isValid;
            }
            
            // Accessibility: Announce messages to screen readers
            const errorMessage = document.querySelector('.error-message');
            const successMessage = document.querySelector('.success-message');
            
            if (errorMessage) {
                errorMessage.setAttribute('aria-live', 'polite');
            }
            if (successMessage) {
                successMessage.setAttribute('aria-live', 'polite');
            }
        });
    </script>
</body>
</html>
