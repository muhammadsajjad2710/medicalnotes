<?php
/**
 * Unified Navigation Component for MedicalNotes
 * Provides consistent navigation across all pages
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['member_id']);
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Get user info if logged in
$username = 'User';
$credits = 0;
if ($isLoggedIn) {
    try {
        require_once __DIR__ . '/../config.php';
        $credits = getUserCredits($_SESSION['member_id']);
        $userInfo = getUserInfo($_SESSION['member_id']);
        $username = $userInfo['username'];
    } catch (Exception $e) {
        error_log("Navigation error: " . $e->getMessage());
    }
}

// Define navigation structure
$navItems = [
    'index' => [
        'label' => 'Dashboard',
        'icon' => 'dashboard',
        'url' => 'index.php',
        'description' => 'Main dashboard and overview'
    ],
    'modules' => [
        'label' => 'Modules',
        'icon' => 'apps',
        'url' => 'modules.php',
        'description' => 'AI module selection'
    ],
    'credits' => [
        'label' => 'Credits',
        'icon' => 'account_balance_wallet',
        'url' => 'credits.php',
        'description' => 'Manage credits and billing'
    ]
];

// Define module navigation
$moduleNavItems = [
    'medicalvoice' => [
        'label' => 'MedicalVoice',
        'icon' => 'mic',
        'url' => 'medicalvoice/',
        'description' => 'AI Audio Transcription',
        'color' => 'primary'
    ],
    'medicalvision' => [
        'label' => 'MedicalVision',
        'icon' => 'visibility',
        'url' => 'medicalvision/',
        'description' => 'AI Document Analysis',
        'color' => 'secondary'
    ]
];
?>

<!-- Unified Navigation Component -->
<nav class="unified-nav" role="navigation" aria-label="Main navigation">
    <!-- Top Navigation Bar -->
    <div class="nav-topbar">
        <div class="nav-container">
            <!-- Logo and Brand -->
            <div class="nav-brand">
                <a href="<?php echo $isLoggedIn ? 'index.php' : 'login.php'; ?>" class="brand-link">
                    <div class="brand-logo">
                        <img src="logo.jpeg" alt="Chief.AI Logo" width="32" height="32">
                    </div>
                    <span class="brand-text">Chief.AI</span>
                    <span class="brand-subtitle">MedicalNotes</span>
                </a>
            </div>

            <!-- Top Navigation Items -->
            <div class="nav-top-items">
                <?php if ($isLoggedIn): ?>
                    <!-- Credits Display -->
                    <div class="credits-display">
                        <span class="credits-icon material-icons-round">account_balance_wallet</span>
                        <span class="credits-amount"><?php echo $credits; ?></span>
                        <span class="credits-label">Credits</span>
                    </div>

                    <!-- User Menu -->
                    <div class="user-menu" role="menubar">
                        <button class="user-menu-toggle" aria-expanded="false" aria-haspopup="true">
                            <div class="user-avatar">
                                <span class="user-initial"><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                            </div>
                            <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                            <span class="material-icons-round">expand_more</span>
                        </button>
                        
                        <div class="user-dropdown" role="menu" aria-hidden="true">
                            <div class="dropdown-header">
                                <div class="user-info">
                                    <div class="user-avatar large">
                                        <span class="user-initial"><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                                    </div>
                                    <div class="user-details">
                                        <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
                                        <div class="user-credits"><?php echo $credits; ?> credits available</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="dropdown-divider"></div>
                            
                            <a href="credits.php" class="dropdown-item" role="menuitem">
                                <span class="material-icons-round">account_balance_wallet</span>
                                Buy Credits
                            </a>
                            
                            <a href="modules.php" class="dropdown-item" role="menuitem">
                                <span class="material-icons-round">apps</span>
                                AI Modules
                            </a>
                            
                            <div class="dropdown-divider"></div>
                            
                            <a href="logout.php" class="dropdown-item" role="menuitem">
                                <span class="material-icons-round">logout</span>
                                Sign Out
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Auth Links -->
                    <div class="auth-links">
                        <a href="login.php" class="btn btn-ghost">Sign In</a>
                        <a href="register.php" class="btn btn-primary">Create Account</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" aria-label="Toggle mobile menu" aria-expanded="false">
                <span class="material-icons-round">menu</span>
            </button>
        </div>
    </div>

    <!-- Main Navigation (if logged in) -->
    <?php if ($isLoggedIn): ?>
        <div class="nav-main">
            <div class="nav-container">
                <ul class="nav-list" role="menubar">
                    <?php foreach ($navItems as $key => $item): ?>
                        <li class="nav-item" role="none">
                            <a href="<?php echo $item['url']; ?>" 
                               class="nav-link <?php echo $currentPage === $key ? 'active' : ''; ?>"
                               role="menuitem"
                               aria-current="<?php echo $currentPage === $key ? 'page' : 'false'; ?>">
                                <span class="nav-icon material-icons-round"><?php echo $item['icon']; ?></span>
                                <span class="nav-label"><?php echo $item['label']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</nav>

<!-- Mobile Navigation Overlay -->
<div class="mobile-nav-overlay" aria-hidden="true">
    <div class="mobile-nav-content">
        <div class="mobile-nav-header">
            <div class="mobile-nav-brand">
                <img src="logo.jpeg" alt="Chief.AI Logo" width="32" height="32">
                <span>Chief.AI MedicalNotes</span>
            </div>
            <button class="mobile-nav-close" aria-label="Close mobile menu">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        
        <?php if ($isLoggedIn): ?>
            <div class="mobile-nav-user">
                <div class="mobile-user-avatar">
                    <span class="user-initial"><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                </div>
                <div class="mobile-user-info">
                    <div class="mobile-user-name"><?php echo htmlspecialchars($username); ?></div>
                    <div class="mobile-user-credits"><?php echo $credits; ?> credits</div>
                </div>
            </div>
            
            <nav class="mobile-nav-menu">
                <ul class="mobile-nav-list">
                    <?php foreach ($navItems as $key => $item): ?>
                        <li class="mobile-nav-item">
                            <a href="<?php echo $item['url']; ?>" 
                               class="mobile-nav-link <?php echo $currentPage === $key ? 'active' : ''; ?>">
                                <span class="mobile-nav-icon material-icons-round"><?php echo $item['icon']; ?></span>
                                <span class="mobile-nav-label"><?php echo $item['label']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    
                    <li class="mobile-nav-divider"></li>
                    
                    <?php foreach ($moduleNavItems as $key => $item): ?>
                        <li class="mobile-nav-item">
                            <a href="<?php echo $item['url']; ?>" class="mobile-nav-link">
                                <span class="mobile-nav-icon material-icons-round"><?php echo $item['icon']; ?></span>
                                <span class="mobile-nav-label"><?php echo $item['label']; ?></span>
                                <span class="mobile-nav-description"><?php echo $item['description']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    
                    <li class="mobile-nav-divider"></li>
                    
                    <li class="mobile-nav-item">
                        <a href="logout.php" class="mobile-nav-link">
                            <span class="mobile-nav-icon material-icons-round">logout</span>
                            <span class="mobile-nav-label">Sign Out</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php else: ?>
            <div class="mobile-auth-links">
                <a href="login.php" class="btn btn-primary btn-full">Sign In</a>
                <a href="register.php" class="btn btn-outline btn-full">Create Account</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Unified Navigation Styles */
.unified-nav {
    position: sticky;
    top: 0;
    z-index: var(--z-fixed);
    background: white;
    border-bottom: 1px solid var(--neutral-200);
    box-shadow: var(--shadow-sm);
}

.nav-topbar {
    background: white;
    border-bottom: 1px solid var(--neutral-200);
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--space-6);
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 64px;
}

/* Brand Styles */
.nav-brand {
    display: flex;
    align-items: center;
}

.brand-link {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    text-decoration: none;
    color: inherit;
}

.brand-logo {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-lg);
    overflow: hidden;
    background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
}

.brand-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.brand-text {
    font-size: var(--text-lg);
    font-weight: 700;
    color: var(--neutral-900);
}

.brand-subtitle {
    font-size: var(--text-xs);
    color: var(--neutral-500);
    font-weight: 500;
}

/* Top Navigation Items */
.nav-top-items {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

/* Credits Display */
.credits-display {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    background: var(--primary-50);
    border: 1px solid var(--primary-200);
    border-radius: var(--radius-lg);
    color: var(--primary-700);
    font-weight: 600;
    font-size: var(--text-sm);
}

.credits-icon {
    font-size: 16px;
}

.credits-amount {
    font-weight: 700;
}

.credits-label {
    font-weight: 500;
}

/* User Menu */
.user-menu {
    position: relative;
}

.user-menu-toggle {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    background: transparent;
    border: 1px solid var(--neutral-200);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all var(--transition-normal);
}

.user-menu-toggle:hover {
    background: var(--neutral-50);
    border-color: var(--neutral-300);
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: var(--text-sm);
}

.user-name {
    font-weight: 500;
    color: var(--neutral-700);
}

/* User Dropdown */
.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: var(--space-2);
    min-width: 280px;
    background: white;
    border: 1px solid var(--neutral-200);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: all var(--transition-normal);
}

.user-menu:hover .user-dropdown,
.user-dropdown:hover {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    padding: var(--space-4);
    border-bottom: 1px solid var(--neutral-200);
}

.user-info {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.user-avatar.large {
    width: 48px;
    height: 48px;
    font-size: var(--text-lg);
}

.user-details {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: var(--neutral-900);
    margin-bottom: var(--space-1);
}

.user-credits {
    font-size: var(--text-sm);
    color: var(--neutral-600);
}

.dropdown-divider {
    height: 1px;
    background: var(--neutral-200);
    margin: var(--space-2) 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3) var(--space-4);
    color: var(--neutral-700);
    text-decoration: none;
    transition: all var(--transition-normal);
}

.dropdown-item:hover {
    background: var(--neutral-50);
    color: var(--neutral-900);
}

.dropdown-item .material-icons-round {
    font-size: 18px;
}

/* Auth Links */
.auth-links {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

/* Main Navigation */
.nav-main {
    background: var(--neutral-50);
    border-bottom: 1px solid var(--neutral-200);
}

.nav-main .nav-container {
    height: 48px;
}

.nav-list {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    display: flex;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    color: var(--neutral-600);
    text-decoration: none;
    border-radius: var(--radius-lg);
    transition: all var(--transition-normal);
    font-weight: 500;
    font-size: var(--text-sm);
}

.nav-link:hover {
    color: var(--neutral-900);
    background: var(--neutral-100);
}

.nav-link.active {
    color: var(--primary-700);
    background: var(--primary-50);
    border: 1px solid var(--primary-200);
}

.nav-icon {
    font-size: 18px;
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
    display: none;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: transparent;
    border: 1px solid var(--neutral-200);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all var(--transition-normal);
}

.mobile-menu-toggle:hover {
    background: var(--neutral-50);
    border-color: var(--neutral-300);
}

/* Mobile Navigation Overlay */
.mobile-nav-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: var(--z-modal);
    opacity: 0;
    visibility: hidden;
    transition: all var(--transition-normal);
}

.mobile-nav-overlay.active {
    opacity: 1;
    visibility: visible;
}

.mobile-nav-content {
    position: absolute;
    top: 0;
    right: 0;
    width: 320px;
    height: 100%;
    background: white;
    transform: translateX(100%);
    transition: transform var(--transition-normal);
    overflow-y: auto;
}

.mobile-nav-overlay.active .mobile-nav-content {
    transform: translateX(0);
}

.mobile-nav-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-6);
    border-bottom: 1px solid var(--neutral-200);
}

.mobile-nav-brand {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    font-weight: 600;
    color: var(--neutral-900);
}

.mobile-nav-close {
    width: 32px;
    height: 32px;
    background: transparent;
    border: 1px solid var(--neutral-200);
    border-radius: var(--radius-lg);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mobile-nav-user {
    padding: var(--space-4);
    border-bottom: 1px solid var(--neutral-200);
}

.mobile-user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: var(--text-lg);
    margin-bottom: var(--space-2);
}

.mobile-user-name {
    font-weight: 600;
    color: var(--neutral-900);
    margin-bottom: var(--space-1);
}

.mobile-user-credits {
    font-size: var(--text-sm);
    color: var(--neutral-600);
}

.mobile-nav-menu {
    padding: var(--space-4);
}

.mobile-nav-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.mobile-nav-item {
    margin-bottom: var(--space-2);
}

.mobile-nav-link {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3);
    color: var(--neutral-700);
    text-decoration: none;
    border-radius: var(--radius-lg);
    transition: all var(--transition-normal);
}

.mobile-nav-link:hover {
    background: var(--neutral-50);
    color: var(--neutral-900);
}

.mobile-nav-link.active {
    color: var(--primary-700);
    background: var(--primary-50);
    border: 1px solid var(--primary-200);
}

.mobile-nav-icon {
    font-size: 20px;
}

.mobile-nav-description {
    font-size: var(--text-xs);
    color: var(--neutral-500);
    margin-top: var(--space-1);
}

.mobile-nav-divider {
    height: 1px;
    background: var(--neutral-200);
    margin: var(--space-4) 0;
}

.mobile-auth-links {
    padding: var(--space-6);
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.btn-full {
    width: 100%;
    justify-content: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .nav-top-items {
        display: none;
    }
    
    .mobile-menu-toggle {
        display: flex;
    }
    
    .nav-main {
        display: none;
    }
    
    .nav-container {
        padding: 0 var(--space-4);
    }
}

@media (max-width: 480px) {
    .nav-container {
        padding: 0 var(--space-3);
    }
    
    .brand-subtitle {
        display: none;
    }
    
    .mobile-nav-content {
        width: 100%;
    }
}
</style>

<script>
// Navigation JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileNavOverlay = document.querySelector('.mobile-nav-overlay');
    const mobileNavClose = document.querySelector('.mobile-nav-close');
    
    // Mobile menu toggle
    if (mobileMenuToggle && mobileNavOverlay) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileNavOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            this.setAttribute('aria-expanded', 'true');
        });
        
        // Close mobile menu
        function closeMobileMenu() {
            mobileNavOverlay.classList.remove('active');
            document.body.style.overflow = '';
            mobileMenuToggle.setAttribute('aria-expanded', 'false');
        }
        
        if (mobileNavClose) {
            mobileNavClose.addEventListener('click', closeMobileMenu);
        }
        
        // Close on overlay click
        mobileNavOverlay.addEventListener('click', function(e) {
            if (e.target === mobileNavOverlay) {
                closeMobileMenu();
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileNavOverlay.classList.contains('active')) {
                closeMobileMenu();
            }
        });
    }
    
    // User dropdown accessibility
    const userMenuToggle = document.querySelector('.user-menu-toggle');
    const userDropdown = document.querySelector('.user-dropdown');
    
    if (userMenuToggle && userDropdown) {
        userMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            userDropdown.setAttribute('aria-hidden', isExpanded);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                userMenuToggle.setAttribute('aria-expanded', 'false');
                userDropdown.setAttribute('aria-hidden', 'true');
            }
        });
    }
    
    // Keyboard navigation for dropdown
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    dropdownItems.forEach(item => {
        item.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});
</script>
