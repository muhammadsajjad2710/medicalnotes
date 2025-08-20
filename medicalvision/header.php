<?php
// Session already started in index.php, no need to start again
// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit;
}

// Get user information from database
$userId = $_SESSION['member_id'];
$stmt = $conn->prepare("SELECT username, credits FROM members WHERE member_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($username, $credits);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedicalVision - MedicalNotes AI Document Analysis Platform</title>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <!-- Unified Design System -->
    <link rel="stylesheet" href="../design-system.css">
    
    <style>
        /* MedicalVision-specific styles */
        body {
            background: var(--neutral-50);
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            background: white;
            border-right: 1px solid var(--neutral-200);
            width: 280px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: var(--z-fixed);
            transition: all var(--transition-normal);
            overflow-y: auto;
            box-shadow: var(--shadow-sm);
        }

        .sidebar-header {
            padding: var(--space-6);
            border-bottom: 1px solid var(--neutral-200);
            background: var(--neutral-50);
            text-align: center;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-3);
            margin-bottom: var(--space-4);
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, var(--secondary-500), var(--secondary-600));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: var(--text-lg);
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: var(--radius-lg);
        }

        .brand-name {
            font-size: var(--text-lg);
            font-weight: 700;
            color: var(--neutral-900);
        }

        .module-title {
            font-size: var(--text-sm);
            color: var(--neutral-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
        }

        .sidebar-nav {
            padding: var(--space-4);
        }

        .nav-item {
            margin-bottom: var(--space-2);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3) var(--space-4);
            text-decoration: none;
            color: var(--neutral-600);
            border-radius: var(--radius-lg);
            transition: all var(--transition-normal);
            font-weight: 500;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--secondary-50);
            color: var(--secondary-700);
            transform: translateX(4px);
        }

        .nav-link .material-icons-round {
            font-size: var(--text-lg);
            width: 24px;
            text-align: center;
        }

        .nav-text {
            font-size: var(--text-sm);
        }

        /* Content Area */
        .content-area {
            flex: 1;
            margin-left: 280px;
            transition: margin-left var(--transition-normal);
            min-height: 100vh;
            background: var(--neutral-50);
        }

        .content-area.full-width {
            margin-left: 0;
        }

        /* Top Navigation */
        .top-nav {
            background: white;
            border-bottom: 1px solid var(--neutral-200);
            padding: var(--space-4) var(--space-6);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: var(--z-sticky);
            box-shadow: var(--shadow-sm);
        }

        .top-nav-left {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--neutral-600);
            cursor: pointer;
            padding: var(--space-2);
            border-radius: var(--radius-md);
            transition: all var(--transition-normal);
            display: none;
        }

        .sidebar-toggle:hover {
            background: var(--neutral-100);
            color: var(--neutral-900);
        }

        .page-title {
            font-size: var(--text-xl);
            font-weight: 600;
            color: var(--neutral-900);
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .page-title .material-icons-round {
            color: var(--secondary-500);
            font-size: var(--text-xl);
        }

        .top-nav-right {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-2) var(--space-3);
            background: var(--neutral-50);
            border-radius: var(--radius-lg);
            border: 1px solid var(--neutral-200);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--secondary-500), var(--secondary-600));
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: var(--text-sm);
        }

        .user-name {
            font-size: var(--text-sm);
            font-weight: 500;
            color: var(--neutral-700);
        }

        .credits-display {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-3);
            background: var(--accent-50);
            border: 1px solid var(--accent-200);
            border-radius: var(--radius-lg);
            color: var(--accent-700);
            font-weight: 500;
            font-size: var(--text-sm);
        }

        .credits-number {
            font-weight: 700;
            font-size: var(--text-base);
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-3);
            background: var(--neutral-100);
            border: 1px solid var(--neutral-300);
            border-radius: var(--radius-lg);
            color: var(--neutral-700);
            text-decoration: none;
            font-size: var(--text-sm);
            font-weight: 500;
            transition: all var(--transition-normal);
        }

        .back-btn:hover {
            background: var(--neutral-200);
            color: var(--neutral-900);
            transform: translateX(-2px);
        }

        /* Mobile Responsiveness */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: var(--z-modal);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .content-area {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .top-nav {
                padding: var(--space-3) var(--space-4);
            }

            .top-nav-right {
                gap: var(--space-2);
            }

            .user-info {
                padding: var(--space-2);
            }

            .user-name {
                display: none;
            }

            .credits-display {
                padding: var(--space-2);
            }

            .credits-label {
                display: none;
            }
        }

        /* Animation */
        .sidebar {
            animation: slideInLeft 0.4s ease-out;
        }

        .content-area {
            animation: fadeIn 0.6s ease-out;
        }

        .nav-link {
            animation: fadeIn 0.8s ease-out;
            animation-fill-mode: both;
        }

        .nav-link:nth-child(1) { animation-delay: 0.1s; }
        .nav-link:nth-child(2) { animation-delay: 0.2s; }
        .nav-link:nth-child(3) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebarMenu">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <div class="logo-icon">
                        <img src="../logo.jpeg" alt="MedicalNotes Logo" width="40" height="40">
                    </div>
                    <span class="brand-name">MedicalNotes</span>
                </div>
                <div class="module-title">MedicalVision Module</div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="#upload" class="nav-link" id="nav-upload">
                        <span class="material-icons-round">cloud_upload</span>
                        <span class="nav-text">Document Upload</span>
                    </a>
                </div>
                
                <!-- AI Analysis and View Results removed from sidebar -->
                
                <div class="nav-item">
                    <a href="../credits.php" class="nav-link">
                        <span class="material-icons-round">account_balance_wallet</span>
                        <span class="nav-text">Credits</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="../index.php" class="nav-link">
                        <span class="material-icons-round">dashboard</span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="content-area" id="contentArea">
            <!-- Top Navigation -->
            <nav class="top-nav">
                <div class="top-nav-left">
                    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                        <span class="material-icons-round">menu</span>
                    </button>
                    
                    <h1 class="page-title">
                        <span class="material-icons-round">visibility</span>
                        MedicalVision Document Analysis
                    </h1>
                </div>

                <div class="top-nav-right">
                    <div class="credits-display">
                        <span class="material-icons-round">account_balance_wallet</span>
                        <span class="credits-number"><?php echo $credits; ?></span>
                        <span class="credits-label">Credits</span>
                    </div>
                    
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($username, 0, 1)); ?>
                        </div>
                        <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                    </div>
                    
                    <a href="../logout.php" class="back-btn">
                        <span class="material-icons-round">logout</span>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>

            <!-- Page Content Container -->
            <div class="container" style="padding-top: var(--space-6);">