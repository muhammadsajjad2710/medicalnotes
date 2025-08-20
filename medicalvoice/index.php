<?php
// Start session early for proper authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit;
}

include('database.php');
include('header.php');

// Only clear when explicitly requested
if (isset($_GET['clear_session']) && $_GET['clear_session'] === '1') {
	unset($_SESSION['uploaded_file'], $_SESSION['log_file'], $_SESSION['json_file'], $_SESSION['processing_complete']);
}

$userId = $_SESSION['member_id'];
$stmt = $conn->prepare("SELECT credits FROM members WHERE member_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($credits);
$stmt->fetch();
$stmt->close();

// Debug: Check session data (remove in production)
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
    echo "<h4>Session Debug Info:</h4>";
    echo "<p><strong>Uploaded File:</strong> " . (isset($_SESSION['uploaded_file']) ? $_SESSION['uploaded_file'] : 'Not set') . "</p>";
    echo "<p><strong>File Exists:</strong> " . (isset($_SESSION['uploaded_file']) && file_exists($_SESSION['uploaded_file']) ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>Log File:</strong> " . (isset($_SESSION['log_file']) ? $_SESSION['log_file'] : 'Not set') . "</p>";
    echo "<p><strong>JSON File:</strong> " . (isset($_SESSION['json_file']) ? $_SESSION['json_file'] : 'Not set') . "</p>";
    echo "</div>";
}
?>

<!-- Professional Dashboard with World-Class UI -->
<div class="dashboard-container">
    <!-- Hero Section -->
    <section class="hero-section" role="banner">
        <div class="hero-content">
            <h1 class="hero-title">Chief.AI MedicalVoice</h1>
            <p class="hero-subtitle">
                Transform your medical workflow with cutting-edge AI-powered voice transcription. 
                Built by Chief.AI - where intelligence meets innovation.
            </p>
            
            <!-- Professional Workflow Progress Tracker -->
            <div class="workflow-progress animate-fade-in-up" role="progressbar" aria-label="MedicalVoice workflow progress">
                <div class="workflow-step active" data-step="upload" role="button" tabindex="0" aria-label="Upload Audio - Ready">
                    <div class="step-icon">
                        <span class="material-icons-round">cloud_upload</span>
                    </div>
                    <div class="step-label">Upload Audio</div>
                    <div class="step-status">Ready</div>
                </div>
                
                <div class="workflow-step" data-step="transcription" role="button" tabindex="0" aria-label="AI Transcription - Pending">
                    <div class="step-icon">
                        <span class="material-icons-round">mic</span>
                    </div>
                    <div class="step-label">AI Transcription</div>
                    <div class="step-status">Pending</div>
                </div>
                
                <div class="workflow-step" data-step="analysis" role="button" tabindex="0" aria-label="Medical Analysis - Pending">
                    <div class="step-icon">
                        <span class="material-icons-round">psychology</span>
                    </div>
                    <div class="step-label">Medical Analysis</div>
                    <div class="step-status">Pending</div>
                </div>
                
                <div class="workflow-step" data-step="results" role="button" tabindex="0" aria-label="Results & Download - Pending">
                    <div class="step-icon">
                        <span class="material-icons-round">insights</span>
                    </div>
                    <div class="step-label">Results & Download</div>
                    <div class="step-status">Pending</div>
                </div>
            </div>
            
            <!-- ARIA Live Region for Status Updates -->
            <div aria-live="polite" aria-atomic="true" class="sr-only" id="statusAnnouncer"></div>
        </div>
    </section>

    <!-- Credits Display -->
    <div class="credits-display animate-fade-in-up">
        <div class="credits-number"><?php echo $credits; ?></div>
        <div class="credits-label">Credits Available</div>
        <a href="../credits.php" class="btn btn-primary">
            <span class="material-icons-round">account_balance_wallet</span>
            Buy Credits
        </a>
    </div>

    <!-- Upload Section -->
    <section id="upload" class="upload-section animate-fade-in-up">
        <div class="upload-area" id="uploadArea">
            <div class="upload-icon">
                <span class="material-icons-round">cloud_upload</span>
            </div>
            <div class="upload-text">Drop your audio file here</div>
            <div class="upload-hint">or click to browse (MP3, WAV, M4A up to 25MB)</div>
            <input type="file" id="audioFile" accept="audio/*" style="display: none;">
        </div>
        
        <!-- Enhanced Upload Progress -->
        <div class="upload-progress" id="uploadProgress" style="display: none;">
            <div class="progress-header">
                <h5 class="progress-title">
                    <span class="material-icons-round">upload</span>
                    Uploading Audio File
                </h5>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar">
                    <div class="progress-fill" id="uploadProgressFill" style="width: 0%"></div>
                </div>
                <div class="progress-percentage" id="uploadProgressPercentage">0%</div>
            </div>
            <div class="progress-message" id="uploadProgressMessage">Preparing upload...</div>
        </div>
    </section>

    <!-- Feature Cards -->
    <section class="features-section animate-fade-in-up">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="material-icons-round">mic</span>
                </div>
                <h3 class="feature-title">High Accuracy</h3>
                <p class="feature-description">
                    State-of-the-art speech recognition with medical terminology support
                </p>
                <div class="feature-meta">
                    <span class="meta-item">
                        <span class="material-icons-round">check_circle</span>
                        99.5% Accuracy
                    </span>
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <span class="material-icons-round">security</span>
                </div>
                <h3 class="feature-title">HIPAA Compliant</h3>
                <p class="feature-description">
                    Enterprise-grade security for your sensitive medical data
                </p>
                <div class="feature-meta">
                    <span class="meta-item">
                        <span class="material-icons-round">verified</span>
                        HIPAA Certified
                    </span>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="material-icons-round">speed</span>
                </div>
                <h3 class="feature-title">Fast Processing</h3>
                <p class="feature-description">
                    Quick turnaround times for urgent medical documentation needs
                </p>
                <div class="feature-meta">
                    <span class="meta-item">
                        <span class="material-icons-round">timer</span>
                        < 2 min avg
                    </span>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* Professional Dashboard Styles */
.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Premium Hero Section */
.hero-section {
    background: linear-gradient(135deg, var(--neutral-50) 0%, var(--primary-50) 100%);
    padding: var(--space-16) var(--space-6);
    text-align: center;
    position: relative;
    overflow: hidden;
    margin-bottom: var(--space-8);
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="%23DBEAFE" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
}

.hero-title {
    font-size: 3rem;
    font-weight: 800;
    color: var(--neutral-900);
    margin-bottom: var(--space-4);
    line-height: 1.1;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: fadeInUp 1s ease-out;
}

.hero-subtitle {
    font-size: 1.25rem;
    color: var(--neutral-600);
    max-width: 600px;
    margin: 0 auto var(--space-6);
    line-height: 1.6;
    animation: fadeInUp 1s ease-out 0.2s both;
}

/* Premium Upload Area */
.upload-area {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 2px dashed var(--primary-200);
    border-radius: var(--radius-2xl);
    padding: var(--space-12);
    text-align: center;
    transition: all var(--transition-normal);
    position: relative;
    overflow: hidden;
}

.upload-area::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, var(--primary-50), var(--secondary-50));
    opacity: 0;
    transition: opacity var(--transition-normal);
}

.upload-area:hover::before {
    opacity: 0.3;
}

.upload-area:hover {
    border-color: var(--primary);
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
}

.upload-area.dragover {
    border-color: var(--accent);
    background: var(--accent-50);
    transform: scale(1.02);
}

.upload-icon {
    font-size: 4rem;
    color: var(--primary);
    margin-bottom: var(--space-4);
    transition: all var(--transition-bounce);
}

.upload-area:hover .upload-icon {
    transform: scale(1.1) rotate(5deg);
    color: var(--primary-dark);
}

.upload-text {
    font-size: 1.25rem;
    color: var(--neutral-700);
    margin-bottom: var(--space-4);
    font-weight: 600;
}

.upload-hint {
    color: var(--neutral-500);
    margin-bottom: var(--space-6);
    font-size: 0.875rem;
}

/* Premium Feature Cards */
.feature-card {
    background: var(--neutral-50);
    border-radius: var(--radius-2xl);
    padding: var(--space-6);
    border: 1px solid var(--neutral-200);
    transition: all var(--transition-normal);
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    height: 280px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    transform: scaleX(0);
    transition: transform var(--transition-bounce);
}

.feature-card:hover::before {
    transform: scaleX(1);
}

.feature-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-xl);
    border-color: var(--primary-100);
}

.feature-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: var(--radius-xl);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-bottom: var(--space-4);
    transition: all var(--transition-bounce);
}

.feature-card:hover .feature-icon {
    transform: scale(1.1) rotate(5deg);
    box-shadow: var(--shadow-lg);
}

.feature-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--neutral-900);
    margin-bottom: var(--space-3);
    transition: color var(--transition-normal);
}

.feature-card:hover .feature-title {
    color: var(--primary);
}

.feature-description {
    color: var(--neutral-600);
    line-height: 1.6;
    flex-grow: 1;
}

.feature-meta {
    margin-top: var(--space-4);
    padding-top: var(--space-3);
    border-top: 1px solid var(--neutral-200);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    color: var(--accent-700);
    font-size: var(--text-sm);
    font-weight: 500;
}

.meta-item .material-icons-round {
    font-size: 1rem;
    color: var(--accent-500);
}

/* Professional Workflow Progress Tracker */
.workflow-progress {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: var(--space-8) auto;
    max-width: 800px;
    position: relative;
    z-index: 2;
}

.workflow-progress::before {
    content: '';
    position: absolute;
    top: 30px;
    left: 40px;
    right: 40px;
    height: 2px;
    background: var(--neutral-300);
    z-index: -1;
}

.workflow-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-2);
    position: relative;
    transition: all var(--transition-normal);
}

.step-icon {
    width: 60px;
    height: 60px;
    background: var(--neutral-100);
    border: 2px solid var(--neutral-300);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--neutral-500);
    font-size: 1.5rem;
    transition: all var(--transition-bounce);
    position: relative;
    z-index: 2;
}

.workflow-step.active .step-icon {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
    box-shadow: var(--shadow-lg);
}

.workflow-step.completed .step-icon {
    background: var(--accent);
    border-color: var(--accent);
    color: white;
}

.workflow-step.completed .step-icon::after {
    content: '✓';
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--accent);
    color: white;
    border-radius: var(--radius-full);
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
}

.step-label {
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--neutral-700);
    text-align: center;
    max-width: 100px;
}

.step-status {
    font-size: var(--text-xs);
    color: var(--neutral-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 500;
}

.workflow-step.active .step-label {
    color: var(--primary);
}

.workflow-step.active .step-status {
    color: var(--primary);
}

/* Enhanced Upload Progress */
.upload-progress {
    background: var(--neutral-50);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    margin-top: var(--space-6);
    border: 1px solid var(--neutral-200);
    box-shadow: var(--shadow-sm);
}

.progress-header {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    margin-bottom: var(--space-4);
}

.progress-title {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--neutral-900);
    margin: 0;
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.progress-title .material-icons-round {
    color: var(--primary);
}

.progress-bar-container {
    position: relative;
    margin-bottom: var(--space-4);
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: var(--neutral-200);
    border-radius: var(--radius-full);
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    border-radius: var(--radius-full);
    transition: width var(--transition-normal);
    position: relative;
}

.progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.progress-percentage {
    position: absolute;
    top: -25px;
    right: 0;
    background: var(--primary);
    color: white;
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-sm);
    font-size: var(--text-sm);
    font-weight: 600;
}

.progress-message {
    color: var(--neutral-600);
    font-size: var(--text-sm);
    text-align: center;
}

/* Premium Credits Display */
.credits-display {
    background: linear-gradient(135deg, var(--primary-50), var(--secondary-50));
    border-radius: var(--radius-2xl);
    padding: var(--space-6);
    text-align: center;
    border: 1px solid var(--primary-100);
    box-shadow: var(--shadow-md);
    margin-bottom: var(--space-8);
}

.credits-number {
    font-size: 3rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: var(--space-2);
}

.credits-label {
    color: var(--neutral-600);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 0.875rem;
    margin-bottom: var(--space-4);
}

/* Premium Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
}
        
/* CSS Variables for Consistency */
:root {
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --transition-normal: all 0.3s ease;
    --transition-bounce: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    --transition-slow: all 0.5s ease;
    --space-1: 0.25rem;
    --space-2: 0.5rem;
    --space-3: 0.75rem;
    --space-4: 1rem;
    --space-6: 1.5rem;
    --space-8: 2rem;
    --space-12: 3rem;
    --space-16: 4rem;
    --radius-sm: 0.375rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    --radius-xl: 1rem;
    --radius-2xl: 1.5rem;
    --radius-full: 9999px;
    --text-xs: 0.75rem;
    --text-sm: 0.875rem;
    --text-base: 1rem;
    --text-lg: 1.125rem;
    --text-xl: 1.25rem;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    --neutral-400: #9CA3AF;
}

/* Features Grid */
.features-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

/* Feature Cards */
.feature-card {
    background: var(--bg-white);
    border-radius: 1rem;
    border: 1px solid var(--border-muted);
    overflow: hidden;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
}

.feature-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary);
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary);
}

.feature-header {
    background: var(--bg-gray-50);
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-muted);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.feature-icon {
    width: 80px;
    height: 80px;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0;
    font-size: 2rem;
    color: white;
    transition: var(--transition);
}

.upload-icon {
    background: var(--primary);
}

.convert-icon {
    background: var(--secondary);
}

.feature-card:hover .feature-icon {
    transform: scale(1.1);
}

.feature-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.feature-description {
    color: var(--text-muted);
    margin: 0;
    line-height: 1.6;
}

.feature-content {
    padding: 1.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .credits-content {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .credits-info {
        flex-direction: column;
        text-align: center;
    }
    
    .feature-header {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
    
    /* Mobile Workflow Progress */
    .workflow-progress {
        flex-direction: column;
        gap: var(--space-4);
        max-width: 100%;
        margin: var(--space-6) auto;
    }
    
    .workflow-progress::before {
        display: none; /* Hide connecting line on mobile */
    }
    
    .workflow-step {
        flex-direction: row;
        gap: var(--space-3);
        width: 100%;
        justify-content: flex-start;
        padding: var(--space-3);
        background: var(--neutral-50);
        border-radius: var(--radius-lg);
        border: 1px solid var(--neutral-200);
    }
    
    .step-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .step-label {
        font-size: var(--text-base);
        font-weight: 600;
        text-align: left;
        max-width: none;
    }
    
    .step-status {
        margin-left: auto;
        font-size: var(--text-xs);
        padding: var(--space-1) var(--space-2);
        background: var(--neutral-200);
        border-radius: var(--radius-sm);
        color: var(--neutral-600);
    }
    
    /* Mobile Feature Cards */
    .features-grid {
        grid-template-columns: 1fr;
        gap: var(--space-4);
    }
    
    .feature-card {
        height: auto;
        min-height: 200px;
    }
    
    /* Mobile Upload Area */
    .upload-area {
        padding: var(--space-6);
        margin: 0 var(--space-2);
    }
    
    .upload-icon {
        font-size: 3rem;
    }
    
    .upload-text {
        font-size: 1.125rem;
    }
    
    .upload-hint {
        font-size: 0.875rem;
    }
    
    /* Mobile Progress Bars */
    .upload-progress {
        margin: var(--space-4) var(--space-2);
        padding: var(--space-4);
    }
    
    .progress-title {
        font-size: var(--text-base);
    }
}

@media (max-width: 480px) {
    .hero-title {
        font-size: 1.75rem;
    }
    
    .credits-number {
        font-size: 2.5rem;
    }
    
    .upload-area {
        padding: var(--space-4);
    }
    
    .feature-card {
        margin: 0 var(--space-2);
    }
    
    .workflow-step {
        padding: var(--space-2);
    }
    
    .step-label {
        font-size: var(--text-sm);
    }
    
    .notification {
        right: 10px;
        left: 10px;
        max-width: none;
        min-width: auto;
    }
}

/* Enhanced Button Styles */
.btn {
    border-radius: 9999px; /* pill style */
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    transition: var(--transition);
    border: none;
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    font-size: 1rem;
    cursor: pointer;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn:focus {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}
        
.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}
        
.btn-outline-primary {
    background: transparent;
    border: 2px solid var(--primary);
    color: var(--primary);
}
        
.btn-outline-primary:hover {
    background: var(--primary);
    color: white;
}

/* Loading States */
.loading {
    opacity: 0.7;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid rgba(37, 99, 235, 0.3);
    border-top: 2px solid var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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

/* Focus Management */
*:focus {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

/* High Contrast Mode Support */
@media (prefers-contrast: high) {
    .feature-card {
        border-width: 2px;
    }
    
    .btn {
        border-width: 2px;
    }
    
    .workflow-step {
        border-width: 2px;
    }
    
    .progress-bar {
        border: 2px solid var(--neutral-400);
    }
}

/* Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
    
    .workflow-step:hover {
        transform: none;
    }
    
    .feature-card:hover {
        transform: none;
    }
}

        /* Features Grid Layout */
        .features-section {
            margin-top: var(--space-12);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--space-6);
            margin-top: var(--space-8);
        }

        /* Upload Section Styling */
        .upload-section {
            margin: var(--space-8) 0;
        }

        /* Enhanced Button Styling */
        .btn {
            padding: var(--space-3) var(--space-6);
            border-radius: var(--radius-full);
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-bounce);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            font-size: 1rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left var(--transition-slow);
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: var(--space-4);
            }
            
            .upload-area {
                padding: var(--space-8);
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .credits-number {
                font-size: 2.5rem;
            }
            
            .upload-area {
                padding: var(--space-6);
            }
        }

        /* CSS Variables for consistent styling */
        :root {
            --radius-lg: 12px;
            --space-3: 12px;
            --space-4: 16px;
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --primary: #007bff;
            --transition-bounce: 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        /* Premium Notifications */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-4);
            box-shadow: var(--shadow-xl);
            border-left: 4px solid var(--primary);
            display: flex;
            align-items: center;
            gap: var(--space-3);
            z-index: 10000;
            transform: translateX(400px);
            transition: transform var(--transition-bounce);
            max-width: 400px;
            min-width: 300px;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification-success {
            border-left-color: var(--accent);
        }

        .notification-error {
            border-left-color: #EF4444;
        }

        .notification .material-icons-round {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .notification-success .material-icons-round {
            color: var(--accent);
        }

        .notification-error .material-icons-round {
            color: #EF4444;
        }
        
        .notification .btn-sm {
            margin-left: auto;
            flex-shrink: 0;
        }

        /* Premium Spinner */
        .premium-spinner {
            display: inline-block;
            position: relative;
            width: 40px;
            height: 40px;
            margin-bottom: var(--space-4);
        }

        .spinner-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 3px solid transparent;
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }

        .spinner-ring:nth-child(1) {
            animation-delay: -0.45s;
        }

        .spinner-ring:nth-child(2) {
            animation-delay: -0.3s;
        }

        .spinner-ring:nth-child(3) {
            animation-delay: -0.15s;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
</style>

<?php include('footer.php'); ?>

<script>
// Always reset client-side UI sections on load so the page is fresh after refresh
document.addEventListener('DOMContentLoaded', function() {
    // Clear any stored file data in JavaScript
    if (typeof selectedFile !== 'undefined') {
        selectedFile = null;
    }

    // Reset file input if exists
    const fileInput = document.getElementById('audioFile');
    if (fileInput) {
        fileInput.value = '';
    }

    // Hide any preview sections
    const filePreview = document.getElementById('filePreview');
    if (filePreview) {
        filePreview.style.display = 'none';
    }

    // Show upload area
    const uploadArea = document.querySelector('.upload-area');
    if (uploadArea) {
        uploadArea.style.display = 'block';
    }

    // Clear any progress indicators
    const progressElements = document.querySelectorAll('.processing-progress, .upload-progress');
    progressElements.forEach(el => {
        el.style.display = 'none';
    });

    // Clear any success/error messages
    const messageElements = document.querySelectorAll('.upload-success, .upload-error, .processing-success, .processing-error');
    messageElements.forEach(el => {
        el.remove();
    });
});
</script>

<script>
        // Premium Chief.AI MedicalVoice Interactions
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('uploadArea');
            const audioFile = document.getElementById('audioFile');
            
            // Initialize accessibility enhancements
            enhanceAccessibility();
            
            // Enhanced drag and drop functionality
            uploadArea.addEventListener('click', () => audioFile.click());
            
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileUpload(files[0]);
                }
            });
            
            audioFile.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFileUpload(e.target.files[0]);
                }
            });
            
            function handleFileUpload(file) {
                console.log('=== UPLOAD PROCESS STARTED ===');
                console.log('File:', file);
                console.log('File name:', file.name);
                console.log('File size:', file.size);
                console.log('File type:', file.type);
                
                // Set last operation for retry mechanism
                setLastOperation('upload', file);
                
                // Validate file type
                if (!file.type.startsWith('audio/')) {
                    console.error('Invalid file type:', file.type);
                    showNotification('Please select a valid audio file', 'error');
                    return;
                }
                
                // Validate file size (25MB limit)
                if (file.size > 25 * 1024 * 1024) {
                    console.error('File too large:', file.size);
                    showNotification('File size must be less than 25MB', 'error');
                    return;
                }
                
                console.log('✅ File validation passed');
                
                // Show upload progress
                showUploadProgress();
                
                // Update workflow step
                updateWorkflowStep('upload', 'processing');
                
                // Create FormData and upload
                const formData = new FormData();
                formData.append('action', 'upload_file');
                formData.append('audio_file', file);
                
                console.log('✅ FormData created');
                console.log('FormData entries:', Array.from(formData.entries()));
                
                // Simulate upload progress
                simulateUploadProgress();
                
                console.log('🔄 Sending fetch request to upload.php...');
                fetch('./upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('✅ Response received:', response);
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    return response.json();
                })
                .then(data => {
                    console.log('✅ JSON parsed successfully:', data);
                    if (data.success) {
                        // Update workflow step
                        updateWorkflowStep('upload', 'completed');
                        updateWorkflowStep('transcription', 'ready');
                        
                        showNotification('Audio file uploaded successfully!', 'success');
                        
                        // Hide upload progress
                        hideUploadProgress();
                        
                        // Redirect to processing section
                        setTimeout(() => {
                            window.location.href = '#convert';
                        }, 1500);
                    } else {
                        showNotification(data.message || 'Upload failed', 'error');
                        resetUploadArea();
                        hideUploadProgress();
                        updateWorkflowStep('upload', 'ready');
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    let errorMessage = 'Upload failed. Please try again.';
                    
                    // Try to get more specific error information
                    if (error.name === 'TypeError' && error.message.includes('JSON')) {
                        errorMessage = 'Server response error. Please check console for details.';
                    } else if (error.name === 'NetworkError') {
                        errorMessage = 'Network error. Please check your connection.';
                    }
                    
                    // Enhanced error handling with fallback display
                    try {
                        showNotification(errorMessage, 'error');
                    } catch (notificationError) {
                        console.error('Notification display failed:', notificationError);
                        // Fallback: show error in page
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-danger mt-3';
                        errorDiv.innerHTML = `
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span>${errorMessage}</span>
                                <button class="btn btn-sm btn-outline-primary ms-auto" onclick="this.parentElement.parentElement.remove()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                        
                        // Insert error message after upload area
                        const uploadSection = document.getElementById('upload');
                        if (uploadSection) {
                            uploadSection.appendChild(errorDiv);
                        }
                    }
                    
                    resetUploadArea();
                    hideUploadProgress();
                    updateWorkflowStep('upload', 'ready');
                });
            }
            
            function showUploadProgress() {
                const uploadProgress = document.getElementById('uploadProgress');
                if (uploadProgress) {
                    uploadProgress.style.display = 'block';
                }
            }
            
            function hideUploadProgress() {
                const uploadProgress = document.getElementById('uploadProgress');
                if (uploadProgress) {
                    uploadProgress.style.display = 'none';
                }
            }
            
            function simulateUploadProgress() {
                const progressFill = document.getElementById('uploadProgressFill');
                const progressPercentage = document.getElementById('uploadProgressPercentage');
                const progressMessage = document.getElementById('uploadProgressMessage');
                
                if (!progressFill || !progressPercentage || !progressMessage) return;
                
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;
                    
                    progressFill.style.width = progress + '%';
                    progressPercentage.textContent = Math.round(progress) + '%';
                    
                    if (progress < 30) {
                        progressMessage.textContent = 'Preparing file for upload...';
                    } else if (progress < 60) {
                        progressMessage.textContent = 'Uploading audio file...';
                    } else if (progress < 90) {
                        progressMessage.textContent = 'Finalizing upload...';
                    }
                    
                    if (progress >= 90) {
                        clearInterval(interval);
                    }
                }, 200);
            }
            
            // Enhanced accessibility
            function enhanceAccessibility() {
                // Add ARIA labels and roles
                const navLinks = document.querySelectorAll('.nav-link');
                navLinks.forEach((link, index) => {
                    link.setAttribute('role', 'menuitem');
                    link.setAttribute('aria-label', link.textContent.trim());
                });
                
                // Add keyboard navigation for workflow steps
                const workflowSteps = document.querySelectorAll('.workflow-step');
                workflowSteps.forEach((step, index) => {
                    step.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.click();
                        }
                    });
                    
                    step.addEventListener('click', function() {
                        // Update active step
                        workflowSteps.forEach(s => s.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Announce status change to screen readers
                        announceStatusChange(this);
                    });
                });
                
                // Add focus management for upload area
                const uploadArea = document.getElementById('uploadArea');
                if (uploadArea) {
                    uploadArea.setAttribute('tabindex', '0');
                    uploadArea.setAttribute('role', 'button');
                    uploadArea.setAttribute('aria-label', 'Audio file upload area. Drop audio files here or click to browse.');
                    
                    uploadArea.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            document.getElementById('audioFile').click();
                        }
                    });
                }
            }
            
            function announceStatusChange(stepElement) {
                const statusAnnouncer = document.getElementById('statusAnnouncer');
                if (statusAnnouncer) {
                    const stepLabel = stepElement.querySelector('.step-label').textContent;
                    const stepStatus = stepElement.querySelector('.step-status').textContent;
                    statusAnnouncer.textContent = `${stepLabel} step is now ${stepStatus}`;
                    
                    // Clear announcement after a delay
                    setTimeout(() => {
                        statusAnnouncer.textContent = '';
                    }, 3000);
                }
            }
            
            function updateWorkflowStep(stepName, status) {
                const stepElement = document.querySelector(`[data-step="${stepName}"]`);
                if (!stepElement) return;
                
                // Remove all status classes
                stepElement.classList.remove('active', 'completed', 'ready', 'processing');
                
                // Add new status class
                stepElement.classList.add(status);
                
                // Update step status text
                const statusElement = stepElement.querySelector('.step-status');
                if (statusElement) {
                    statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                }
                
                // Update step icon
                const iconElement = stepElement.querySelector('.step-icon');
                if (iconElement) {
                    switch (status) {
                        case 'completed':
                            iconElement.style.background = 'var(--accent)';
                            iconElement.style.borderColor = 'var(--accent)';
                            iconElement.style.color = 'white';
                            break;
                        case 'active':
                        case 'ready':
                            iconElement.style.background = 'var(--primary)';
                            iconElement.style.borderColor = 'var(--primary)';
                            iconElement.style.color = 'white';
                            break;
                        case 'processing':
                            iconElement.style.background = 'var(--secondary)';
                            iconElement.style.borderColor = 'var(--secondary)';
                            iconElement.style.color = 'white';
                            break;
                        default:
                            iconElement.style.background = 'var(--neutral-100)';
                            iconElement.style.borderColor = 'var(--neutral-300)';
                            iconElement.style.color = 'var(--neutral-500)';
                    }
                }
                
                // Announce status change to screen readers
                announceStatusChange(stepElement);
            }
            
            function resetUploadArea() {
                uploadArea.innerHTML = `
                    <div class="upload-icon">
                        <span class="material-icons-round">cloud_upload</span>
                    </div>
                    <div class="upload-text">Drop your audio file here</div>
                    <div class="upload-hint">or click to browse (MP3, WAV, M4A up to 25MB)</div>
                    <input type="file" id="audioFile" accept="audio/*" style="display: none;">
                `;
                
                // Re-attach event listeners
                const newFileInput = uploadArea.querySelector('#audioFile');
                if (newFileInput) {
                    newFileInput.addEventListener('change', (e) => {
                        if (e.target.files.length > 0) {
                            handleFileUpload(e.target.files[0]);
                        }
                    });
                }
            }
            
            function showNotification(message, type) {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `notification notification-${type}`;
                
                // Add retry button for errors
                let notificationContent = `
                    <span class="material-icons-round">${type === 'success' ? 'check_circle' : 'error'}</span>
                    <span>${message}</span>
                `;
                
                if (type === 'error' && message.includes('failed')) {
                    notificationContent += `
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="window.retryLastOperation()">
                            <span class="material-icons-round">refresh</span>
                            Retry
                        </button>
                    `;
                }
                
                notification.innerHTML = notificationContent;
                
                // Add to page
                document.body.appendChild(notification);
                
                // Show notification
                setTimeout(() => notification.classList.add('show'), 100);
                
                // Remove after 5 seconds
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
            }
            
            // Retry mechanism for failed operations
            let lastOperation = null;
            let lastOperationData = null;
            
            // Make retry function globally accessible
            window.retryLastOperation = function() {
                if (lastOperation && lastOperationData) {
                    console.log('Retrying last operation:', lastOperation);
                    
                    // Show retry notification
                    showNotification('Retrying operation...', 'success');
                    
                    // Execute retry based on operation type
                    switch (lastOperation) {
                        case 'upload':
                            handleFileUpload(lastOperationData);
                            break;
                        case 'transcription':
                            // Retry transcription if implemented
                            showNotification('Transcription retry not yet implemented', 'error');
                            break;
                        default:
                            showNotification('Retry not available for this operation', 'error');
                    }
                } else {
                    showNotification('No operation to retry', 'error');
                }
            }
            
            function setLastOperation(operation, data) {
                lastOperation = operation;
                lastOperationData = data;
            }
            
            // Make setLastOperation globally accessible
            window.setLastOperation = setLastOperation;
            
            // Enhanced scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            // Observe all animated elements
            document.querySelectorAll('.animate-fade-in-up').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                observer.observe(el);
            });
            
            // Enhanced feature card interactions
            document.querySelectorAll('.feature-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
    });
});
</script>
