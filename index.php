<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

// Include configuration file
require_once 'config.php';

// Initialize variables
$credits = 0;
$error_message = null;
$success_message = null;

// Get user information and credits
try {
    $credits = getUserCredits($_SESSION['member_id']);
    $userInfo = getUserInfo($_SESSION['member_id']);
    $username = $userInfo['username'];
    
    // Set success message for new users
    if ($credits === 10 && !isset($_SESSION['credits_loaded'])) {
        $success_message = "Welcome! You have 10 free credits to get started.";
        $_SESSION['credits_loaded'] = true;
    }
    
} catch (Exception $e) {
    // Fallback values
    $credits = 10;
    $username = 'User';
    $error_message = "Unable to load user data. You have 10 default credits available.";
    error_log("Error loading user data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chief.AI - Professional Healthcare Solutions</title>
    
    <!-- Google Fonts - Professional Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <!-- Unified Design System -->
    <link rel="stylesheet" href="design-system.css">
    
    <style>
        /* Dashboard-specific styles using design system tokens */
        .dashboard-container {
            min-height: 100vh;
            background: var(--neutral-50);
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-8) var(--space-6);
        }

        /* Hero Section */
        .hero-section {
            text-align: center;
            margin-bottom: var(--space-12);
            padding: var(--space-12) 0;
        }

        .hero-title {
            font-size: var(--text-5xl);
            font-weight: 800;
            color: var(--neutral-900);
            margin-bottom: var(--space-6);
            background: linear-gradient(135deg, var(--primary-600), var(--secondary-600));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
        }

        .hero-subtitle {
            font-size: var(--text-xl);
            color: var(--neutral-600);
            max-width: 600px;
            margin: 0 auto var(--space-8);
            line-height: 1.6;
        }

        /* Welcome Message */
        .welcome-message {
            background: linear-gradient(135deg, var(--primary-50), var(--secondary-50));
            border: 1px solid var(--primary-200);
            border-radius: var(--radius-2xl);
            padding: var(--space-6);
            margin-bottom: var(--space-8);
            text-align: center;
        }

        .welcome-title {
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--primary-700);
            margin-bottom: var(--space-2);
        }

        .welcome-text {
            color: var(--primary-600);
            font-size: var(--text-lg);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-6);
            margin-bottom: var(--space-12);
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-2xl);
            padding: var(--space-6);
            text-align: center;
            border: 1px solid var(--neutral-200);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-normal);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-200);
        }

        .stat-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto var(--space-4);
            border-radius: var(--radius-2xl);
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: var(--text-2xl);
        }

        .stat-number {
            font-size: var(--text-4xl);
            font-weight: 800;
            color: var(--neutral-900);
            margin-bottom: var(--space-2);
        }

        .stat-label {
            font-size: var(--text-lg);
            color: var(--neutral-600);
            font-weight: 500;
        }

        /* Modules Section */
        .modules-section {
            margin-bottom: var(--space-12);
        }

        .section-header {
            text-align: center;
            margin-bottom: var(--space-8);
        }

        .section-title {
            font-size: var(--text-3xl);
            font-weight: 700;
            color: var(--neutral-900);
            margin-bottom: var(--space-4);
        }

        .section-subtitle {
            font-size: var(--text-lg);
            color: var(--neutral-600);
            max-width: 600px;
            margin: 0 auto;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: var(--space-6);
        }

        .module-card {
            background: white;
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            text-align: center;
            border: 1px solid var(--neutral-200);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500), var(--secondary-500));
        }

        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary-300);
        }

        .module-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto var(--space-6);
            border-radius: var(--radius-2xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: var(--text-3xl);
            position: relative;
        }

        .module-icon.primary {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
        }

        .module-icon.secondary {
            background: linear-gradient(135deg, var(--secondary-500), var(--secondary-600));
        }

        .module-title {
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--neutral-900);
            margin-bottom: var(--space-4);
        }

        .module-description {
            color: var(--neutral-600);
            margin-bottom: var(--space-6);
            line-height: 1.6;
            font-size: var(--text-base);
        }

        .module-features {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-2);
            justify-content: center;
            margin-bottom: var(--space-6);
        }

        .feature-tag {
            background: var(--primary-50);
            color: var(--primary-700);
            padding: var(--space-1) var(--space-3);
            border-radius: var(--radius-full);
            font-size: var(--text-xs);
            font-weight: 600;
            border: 1px solid var(--primary-200);
        }

        .module-btn {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            padding: var(--space-3) var(--space-6);
            border-radius: var(--radius-lg);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            transition: all var(--transition-bounce);
            box-shadow: var(--shadow-md);
        }

        .module-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
        }

        /* Quick Actions */
        .quick-actions {
            background: white;
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            border: 1px solid var(--neutral-200);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-8);
        }

        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-4);
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-4);
            background: var(--neutral-50);
            border: 1px solid var(--neutral-200);
            border-radius: var(--radius-lg);
            text-decoration: none;
            color: var(--neutral-700);
            transition: all var(--transition-normal);
        }

        .quick-action-btn:hover {
            background: var(--primary-50);
            border-color: var(--primary-200);
            color: var(--primary-700);
            transform: translateY(-2px);
        }

        .quick-action-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-lg);
            background: var(--primary-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-600);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: var(--space-6) var(--space-4);
            }
            
            .hero-title {
                font-size: var(--text-4xl);
            }
            
            .hero-subtitle {
                font-size: var(--text-lg);
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .modules-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: var(--space-4) var(--space-3);
            }
            
            .hero-title {
                font-size: var(--text-3xl);
            }
            
            .module-card {
                padding: var(--space-6);
            }
        }
    </style>
</head>
<body>
    <!-- Include Unified Navigation -->
    <?php include 'components/navigation.php'; ?>

    <div class="dashboard-container">
        <main class="main-content">
            <!-- Hero Section -->
            <section class="hero-section" role="banner">
                <h1 class="hero-title">Welcome to Chief.AI MedicalNotes</h1>
                <p class="hero-subtitle">
                    Transform your medical workflow with cutting-edge AI-powered solutions. 
                    Professional healthcare AI platform built for accuracy, security, and efficiency.
                </p>
            </section>

            <!-- Welcome Message -->
            <?php if (isset($success_message)): ?>
                <div class="welcome-message" role="alert" aria-live="polite">
                    <h2 class="welcome-title">🎉 Welcome!</h2>
                    <p class="welcome-text"><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <section class="stats-section" aria-labelledby="stats-title">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="material-icons-round">account_balance_wallet</span>
                        </div>
                        <div class="stat-number"><?php echo $credits; ?></div>
                        <div class="stat-label">Credits Available</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="material-icons-round">psychology</span>
                        </div>
                        <div class="stat-number">2</div>
                        <div class="stat-label">AI Modules</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="material-icons-round">security</span>
                        </div>
                        <div class="stat-number">100%</div>
                        <div class="stat-label">HIPAA Compliant</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="material-icons-round">speed</span>
                        </div>
                        <div class="stat-number">< 30s</div>
                        <div class="stat-label">Processing Time</div>
                    </div>
                </div>
            </section>

            <!-- AI Modules Section -->
            <section class="modules-section" aria-labelledby="modules-title">
                <div class="section-header">
                    <h2 id="modules-title" class="section-title">AI-Powered Medical Solutions</h2>
                    <p class="section-subtitle">
                        Choose from our advanced AI modules designed specifically for healthcare professionals
                    </p>
                </div>

                <div class="modules-grid">
                    <!-- MedicalVoice Module -->
                    <div class="module-card">
                        <div class="module-icon primary">
                            <span class="material-icons-round">mic</span>
                        </div>
                        <h3 class="module-title">MedicalVoice</h3>
                        <p class="module-description">
                            Advanced AI-powered audio transcription and medical analysis. 
                            Convert medical dictations, consultations, and recordings into structured text with clinical insights.
                        </p>
                        <div class="module-features">
                            <span class="feature-tag">Speech-to-Text</span>
                            <span class="feature-tag">Medical Analysis</span>
                            <span class="feature-tag">Multi-format</span>
                            <span class="feature-tag">HIPAA Secure</span>
                        </div>
                        <a href="medicalvoice/" class="module-btn">
                            <span class="material-icons-round">play_arrow</span>
                            Launch MedicalVoice
                        </a>
                    </div>

                    <!-- MedicalVision Module -->
                    <div class="module-card">
                        <div class="module-icon secondary">
                            <span class="material-icons-round">visibility</span>
                        </div>
                        <h3 class="module-title">MedicalVision</h3>
                        <p class="module-description">
                            Intelligent document analysis and OCR for medical forms, prescriptions, 
                            and handwritten notes. Extract clinical data with AI-powered accuracy.
                        </p>
                        <div class="module-features">
                            <span class="feature-tag">Document OCR</span>
                            <span class="feature-tag">Handwriting Recognition</span>
                            <span class="feature-tag">FHIR Export</span>
                            <span class="feature-tag">Multi-format</span>
                        </div>
                        <a href="medicalvision/" class="module-btn">
                            <span class="material-icons-round">play_arrow</span>
                            Launch MedicalVision
                        </a>
                    </div>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="quick-actions-section" aria-labelledby="quick-actions-title">
                <div class="quick-actions">
                    <h2 id="quick-actions-title" class="section-title" style="margin-bottom: var(--space-6);">Quick Actions</h2>
                    <div class="quick-actions-grid">
                        <a href="modules.php" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <span class="material-icons-round">apps</span>
                            </div>
                            <div>
                                <div style="font-weight: 600;">Browse Modules</div>
                                <div style="font-size: var(--text-sm); color: var(--neutral-500);">View all AI solutions</div>
                            </div>
                        </a>
                        
                        <a href="credits.php" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <span class="material-icons-round">account_balance_wallet</span>
                            </div>
                            <div>
                                <div style="font-weight: 600;">Buy Credits</div>
                                <div style="font-size: var(--text-sm); color: var(--neutral-500);">Purchase processing credits</div>
                            </div>
                        </a>
                        
                        <a href="buy_credits.php" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <span class="material-icons-round">payment</span>
                            </div>
                            <div>
                                <div style="font-weight: 600;">Payment History</div>
                                <div style="font-size: var(--text-sm); color: var(--neutral-500);">View transactions</div>
                            </div>
                        </a>
                        
                        <a href="logout.php" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <span class="material-icons-round">logout</span>
                            </div>
                            <div>
                                <div style="font-weight: 600;">Sign Out</div>
                                <div style="font-size: var(--text-sm); color: var(--neutral-500);">Secure logout</div>
                            </div>
                        </a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Dashboard JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add loading states for module buttons
            document.querySelectorAll('.module-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Add loading state
                    const originalText = this.innerHTML;
                    this.innerHTML = '<span class="material-icons-round">hourglass_empty</span> Loading...';
                    this.style.pointerEvents = 'none';
                    
                    // Reset after navigation (this will happen naturally)
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.pointerEvents = '';
                    }, 2000);
                });
            });

            // Accessibility: Announce page load to screen readers
            const heroTitle = document.querySelector('.hero-title');
            if (heroTitle) {
                heroTitle.setAttribute('aria-live', 'polite');
            }

            // Keyboard navigation enhancement
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + K to focus search (if we add one later)
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    // Focus search input when implemented
                }
            });
        });
    </script>
</body>
</html>
