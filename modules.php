<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

// Include configuration file
require_once 'config.php';

// Get user information and credits
try {
    $credits = getUserCredits($_SESSION['member_id']);
    $userInfo = getUserInfo($_SESSION['member_id']);
    $username = $userInfo['username'];
} catch (Exception $e) {
    $credits = 10;
    $username = 'User';
    error_log("Error loading user data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Modules - Chief.AI MedicalNotes</title>
    
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
            --secondary-50: #F5F3FF;
            --secondary-100: #EDE9FE;
            --secondary-200: #DDD6FE;
            --secondary-300: #C4B5FD;
            --secondary-400: #A78BFA;
            --secondary-500: #8B5CF6;
            --secondary-600: #7C3AED;
            --secondary-700: #6D28D9;
            --secondary-800: #5B21B6;
            --secondary-900: #4C1D95;
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

        .navbar {
            background: white;
            border-bottom: 1px solid var(--neutral-200);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--neutral-900);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .navbar-nav {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-link {
            text-decoration: none;
            color: var(--neutral-600);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            transition: var(--transition);
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-600);
            background: var(--primary-50);
        }

        .nav-link.active {
            color: var(--primary-600);
            background: var(--primary-50);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -1rem;
            left: 50%;
            transform: translateX(-50%);
            width: 2rem;
            height: 2px;
            background: var(--primary-500);
            border-radius: 1px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
        }

        .user-name {
            color: var(--neutral-700);
            font-weight: 500;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-ghost {
            background: transparent;
            color: var(--neutral-600);
            border: 1px solid var(--neutral-200);
        }

        .btn-ghost:hover {
            background: var(--neutral-50);
            border-color: var(--neutral-300);
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 3rem;
            font-weight: 800;
            color: var(--neutral-900);
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            font-size: 1.25rem;
            color: var(--neutral-600);
            max-width: 600px;
            margin: 0 auto;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .module-card {
            background: white;
            border-radius: var(--radius-2xl);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--neutral-200);
            transition: var(--transition);
            text-align: center;
        }

        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary-200);
        }

        .module-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            border-radius: var(--radius-xl);
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }

        .module-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--neutral-900);
            margin-bottom: 1rem;
        }

        .module-description {
            color: var(--neutral-600);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .module-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .feature-tag {
            background: var(--primary-50);
            color: var(--primary-700);
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 500;
            border: 1px solid var(--primary-200);
        }

        .module-btn {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--radius-lg);
            transition: var(--transition);
        }

        .module-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .credits-display {
            background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
            color: white;
            padding: 2rem;
            border-radius: var(--radius-2xl);
            text-align: center;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-xl);
        }

        .credits-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .credits-label {
            font-size: 1.125rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        .btn-primary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--radius-lg);
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.125rem;
        }

        @media (max-width: 768px) {
            .navbar-container {
                padding: 1rem;
            }
            
            .navbar-nav {
                display: none;
            }
            
            .container {
                padding: 1rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .modules-grid {
                grid-template-columns: 1fr;
            }
            
            .module-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Professional Navigation Bar -->
    <nav class="navbar" id="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
                <div class="logo-icon">
                    <img src="/logo.jpeg" alt="Chief.AI Logo" width="32" height="32">
                </div>
                <span>MedicalNotes</span>
            </a>
            
            <ul class="navbar-nav">
                <li><a href="index.php" class="nav-link">Dashboard</a></li>
                <li><a href="modules.php" class="nav-link active">Modules</a></li>
                <li><a href="credits.php" class="nav-link">Credits</a></li>
            </ul>
            
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </div>
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                </div>
                <a href="logout.php" class="btn btn-ghost btn-sm">
                    <span class="material-icons-round">logout</span>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">AI Modules</h1>
                <p class="page-subtitle">
                    Access our powerful AI-powered healthcare modules designed to transform your medical workflow
                </p>
            </div>

            <!-- Credits Display -->
            <div class="credits-display">
                <div class="credits-number"><?php echo $credits; ?></div>
                <div class="credits-label">Credits Available</div>
                <a href="credits.php" class="btn btn-primary btn-lg">
                    <span class="material-icons-round">account_balance_wallet</span>
                    Manage Credits
                </a>
            </div>

            <!-- AI Modules Grid -->
            <div class="modules-grid">
                <!-- MedicalVoice Module -->
                <div class="module-card">
                    <div class="module-icon">
                        <span class="material-icons-round">mic</span>
                    </div>
                    <h3 class="module-title">MedicalVoice</h3>
                    <p class="module-description">
                        Advanced AI-powered voice transcription with medical terminology support. 
                        Convert audio recordings to structured medical documentation instantly.
                    </p>
                    <div class="module-features">
                        <span class="feature-tag">High Accuracy</span>
                        <span class="feature-tag">HIPAA Compliant</span>
                        <span class="feature-tag">Real-time</span>
                    </div>
                    <a href="medicalvoice/index.php" class="btn module-btn">
                        <span class="material-icons-round">arrow_forward</span>
                        Launch MedicalVoice
                    </a>
                </div>

                <!-- MedicalVision Module -->
                <div class="module-card">
                    <div class="module-icon">
                        <span class="material-icons-round">visibility</span>
                    </div>
                    <h3 class="module-title">MedicalVision</h3>
                    <p class="module-description">
                        Intelligent document analysis with OCR technology and AI-powered insights. 
                        Extract, analyze, and structure medical documents automatically.
                    </p>
                    <div class="module-features">
                        <span class="feature-tag">Advanced OCR</span>
                        <span class="feature-tag">AI Analysis</span>
                        <span class="feature-tag">FHIR Ready</span>
                    </div>
                    <a href="medicalvision/index.php" class="btn module-btn">
                        <span class="material-icons-round">arrow_forward</span>
                        Launch MedicalVision
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
