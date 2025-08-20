<!-- AI Processing Section -->
<!-- 
🍎 SENIOR APPLE TEST ENGINEER - TESTING INSTRUCTIONS

=== COMPREHENSIVE TESTING PROTOCOL ===

1. FRONTEND TESTING:
   - Open Chrome DevTools → Console tab
   - Look for "MedicalVoice Processing initialized" message
   - Use: window.testMedicalVoice.runFullTest() for complete system test
   - Monitor Network tab for AJAX requests

2. BACKEND TESTING:
   - Check server error logs for detailed processing information
   - Look for "=== MEDICALVOICE AI PROCESSING DEBUG START ==="
   - Monitor session state tracking and credit deduction

3. PYTHON INTEGRATION:
   - Verify Python binary detection in logs
   - Check AssemblyAI and OpenAI API calls
   - Monitor progress updates and error handling

4. SUCCESS CRITERIA:
   - Click "Start AI Processing" → 1 credit deducted
   - Progress bar animates smoothly
   - Python executes without timeout
   - Results generated and displayed
   - Download functionality works

5. DEBUGGING COMMANDS:
   - window.testMedicalVoice.debugSetup() - Check system setup
   - window.testMedicalVoice.testUpload() - Test upload functionality
   - window.testMedicalVoice.testProcessing() - Test processing
   - window.testMedicalVoice.testResults() - Test results display
   - window.testMedicalVoice.runFullTest() - Run complete test suite

=== END TESTING INSTRUCTIONS ===
-->

<h2 class="mb-4 text-success">
    <i class="fas fa-microphone"></i> AI Processing
</h2>

<!-- Enhanced Debug Information Section -->
<div class="card mb-4" style="background: #f8f9fa; border: 1px solid #dee2e6;">
    <div class="card-header">
        <h5 class="mb-0">🔍 Enhanced Debug Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Session Status:</h6>
                <ul class="list-unstyled">
                    <li><strong>Uploaded File:</strong> <?php echo isset($_SESSION['uploaded_file']) ? basename($_SESSION['uploaded_file']) : 'Not set'; ?></li>
                    <li><strong>File Exists:</strong> <?php echo isset($_SESSION['uploaded_file']) && file_exists($_SESSION['uploaded_file']) ? '✅ Yes' : '❌ No'; ?></li>
                    <li><strong>File Size:</strong> <?php echo isset($_SESSION['uploaded_file']) && file_exists($_SESSION['uploaded_file']) ? formatFileSize(filesize($_SESSION['uploaded_file'])) : 'N/A'; ?></li>
                    <li><strong>Log File:</strong> <?php echo isset($_SESSION['log_file']) ? basename($_SESSION['log_file']) : 'Not set'; ?></li>
                    <li><strong>JSON File:</strong> <?php echo isset($_SESSION['json_file']) ? basename($_SESSION['json_file']) : 'Not set'; ?></li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>System Status:</h6>
                <ul class="list-unstyled">
                    <li><strong>Python Script:</strong> <?php echo file_exists(__DIR__ . '/process_audio.py') ? '✅ Found' : '❌ Missing'; ?></li>
                    <li><strong>Virtual Env:</strong> <?php echo file_exists(__DIR__ . '/myenv') ? '✅ Found' : '❌ Missing'; ?></li>
                    <li><strong>Storage Dir:</strong> <?php echo is_dir(__DIR__ . '/storage/uploads/audio') ? '✅ Writable' : '❌ Not Writable'; ?></li>
                    <li><strong>Session ID:</strong> <?php echo session_id(); ?></li>
                    <li><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></li>
                    <li><strong>Memory Limit:</strong> <?php echo ini_get('memory_limit'); ?></li>
                    <li><strong>Max Execution Time:</strong> <?php echo ini_get('max_execution_time'); ?>s</li>
                </ul>
            </div>
        </div>
        <div class="mt-2">
            <small class="text-muted">This debug information helps identify issues with the AI processing pipeline. Check the error logs for detailed processing information.</small>
        </div>
        
        <!-- Testing Panel -->
        <div class="mt-3 p-3" style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px;">
            <h6 class="text-primary mb-2">
                <i class="fas fa-vial"></i> Testing & Debugging Tools
            </h6>
            <p class="small text-muted mb-2">Open browser console and use these commands for comprehensive testing:</p>
            <div class="row">
                <div class="col-md-6">
                    <button class="btn btn-sm btn-outline-primary mb-1" onclick="console.log('Testing upload functionality...'); window.testMedicalVoice?.testUpload()">
                        <i class="fas fa-upload"></i> Test Upload
                    </button>
                    <button class="btn btn-sm btn-outline-primary mb-1" onclick="console.log('Testing processing functionality...'); window.testMedicalVoice?.testProcessing()">
                        <i class="fas fa-cog"></i> Test Processing
                    </button>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-sm btn-outline-primary mb-1" onclick="console.log('Testing results functionality...'); window.testMedicalVoice?.testResults()">
                        <i class="fas fa-chart-line"></i> Test Results
                    </button>
                    <button class="btn btn-sm btn-outline-success mb-1" onclick="console.log('Running full system test...'); window.testMedicalVoice?.runFullTest()">
                        <i class="fas fa-play"></i> Full Test
                    </button>
                </div>
            </div>
            <div class="mt-2">
                <small class="text-muted">
                    <strong>Console Commands:</strong><br>
                    • <code>window.testMedicalVoice.runFullTest()</code> - Complete system test<br>
                    • <code>window.testMedicalVoice.debugSetup()</code> - Check system setup<br>
                    • <code>window.testMedicalVoice.testSessionState()</code> - Session debugging
                </small>
            </div>
        </div>
    </div>
</div>

<!-- File Status Display -->
<div class="file-status-section mb-4">
    <?php if (isset($_SESSION['uploaded_file']) && file_exists($_SESSION['uploaded_file'])): ?>
        <div class="file-status-success">
            <div class="status-content">
                <div class="status-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="status-info">
                    <h5 class="status-title">✅ File Ready for Processing</h5>
                    <p class="status-file"><strong>File:</strong> <?php echo basename($_SESSION['uploaded_file']); ?></p>
                    <p class="status-size"><strong>Size:</strong> <?php echo formatFileSize(filesize($_SESSION['uploaded_file'])); ?></p>
                    <p class="status-ready text-success">Ready to convert with AI</p>
                </div>
            </div>
        </div>
        
        <!-- Processing Form -->
        <!-- Enhanced Processing Form with AJAX -->
        <div class="processing-form mt-3">
            <div id="processingForm" class="d-inline">
                <input type="hidden" name="action" value="convert">
                <button type="button" class="btn btn-success btn-lg" id="startProcessingBtn" onclick="startAIProcessing()">
                    <i class="fas fa-play me-2"></i> Start AI Processing
                </button>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="changeFileBtn">
                <i class="fas fa-edit me-1"></i> Change File
            </button>
        </div>
        
        <!-- Real-time Progress Display -->
        <div class="processing-progress" id="processingProgress" style="display: none;">
            <div class="progress-header">
                <h5 class="progress-title">
                    <i class="fas fa-cog fa-spin me-2"></i>AI Processing in Progress
                </h5>
                <div class="progress-stage" id="progressStage">Initializing...</div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" id="progressBar">
                    <div class="progress-fill" id="progressFill" style="width: 0%"></div>
                </div>
                <div class="progress-percentage" id="progressPercentage">0%</div>
            </div>
            <div class="progress-message" id="progressMessage">Starting processing...</div>
            <div class="progress-time" id="progressTime">Estimated time: Calculating...</div>
        </div>
    <?php else: ?>
        <div class="file-status-warning">
            <div class="status-content">
                <div class="status-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="status-info">
                    <h5 class="status-title">⚠️ No File Uploaded</h5>
                    <p class="status-message">Please upload an audio file first to proceed with AI processing.</p>
                    <a href="#upload" class="btn btn-primary btn-sm">
                        <i class="fas fa-upload me-2"></i>Upload Audio File
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Processing Results -->
<?php if (isset($_SESSION['json_file']) && file_exists($_SESSION['json_file'])): ?>
    <div class="processing-results mt-4">
        <h4 class="mb-3 text-primary">
            <i class="fas fa-chart-line me-2"></i>Processing Results
        </h4>
        
        <?php
        $jsonContent = file_get_contents($_SESSION['json_file']);
        $data = json_decode($jsonContent, true);
        
        if ($data && isset($data['transcribed_text']) && (isset($data['chatgpt_response']) || isset($data['medical_analysis']))): ?>
            <div class="results-container">
                <!-- Enhanced Results Header -->
                <div class="results-header">
                    <h4 class="results-main-title">
                        <i class="fas fa-chart-line me-2"></i>AI Processing Results
                    </h4>
                    <?php if (isset($data['processing_stats']['word_count'])): ?>
                        <div class="results-stats">
                            <span class="stat-item">
                                <i class="fas fa-file-alt me-1"></i>
                                <?php echo number_format($data['processing_stats']['word_count']); ?> words
                            </span>
                            <?php if (isset($data['processing_stats']['audio_duration'])): ?>
                                <span class="stat-item">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo gmdate("i:s", $data['processing_stats']['audio_duration']); ?> duration
                                </span>
                            <?php endif; ?>
                            <span class="stat-item">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('M j, Y g:i A', strtotime($data['timestamp'])); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Transcription Result -->
                <div class="result-card mb-3">
                    <div class="result-header">
                        <h5 class="result-title">
                            <i class="fas fa-microphone me-2"></i>Speech Transcription
                        </h5>
                        <div class="result-actions-mini">
                            <button class="btn btn-outline-primary btn-sm" onclick="copyToClipboard('transcription')">
                                <i class="fas fa-copy me-1"></i>Copy
                            </button>
                        </div>
                    </div>
                    <div class="result-content">
                        <div class="result-text" id="transcription-text"><?php echo nl2br(htmlspecialchars($data['transcribed_text'])); ?></div>
                    </div>
                </div>
                
                <!-- AI Medical Analysis Result -->
                <div class="result-card mb-3">
                    <div class="result-header">
                        <h5 class="result-title">
                            <i class="fas fa-brain me-2"></i>AI Medical Analysis
                        </h5>
                        <div class="result-actions-mini">
                            <button class="btn btn-outline-primary btn-sm" onclick="copyToClipboard('analysis')">
                                <i class="fas fa-copy me-1"></i>Copy
                            </button>
                        </div>
                    </div>
                    <div class="result-content">
                        <div class="result-text medical-analysis" id="analysis-text">
                            <?php 
                            $analysis = $data['medical_analysis'] ?? $data['chatgpt_response'];
                            echo nl2br(htmlspecialchars($analysis));
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Features (if available) -->
                <?php if (isset($data['ai_summary']) || isset($data['key_highlights']) || isset($data['chapters'])): ?>
                    <div class="enhanced-features">
                        <h5 class="features-title">
                            <i class="fas fa-star me-2"></i>Enhanced AI Features
                        </h5>
                        
                        <?php if (isset($data['ai_summary'])): ?>
                            <div class="feature-card">
                                <h6><i class="fas fa-list-ul me-2"></i>AI Summary</h6>
                                <p><?php echo nl2br(htmlspecialchars($data['ai_summary'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($data['key_highlights']) && !empty($data['key_highlights'])): ?>
                            <div class="feature-card">
                                <h6><i class="fas fa-highlight me-2"></i>Key Highlights</h6>
                                <ul class="highlights-list">
                                    <?php foreach (array_slice($data['key_highlights'], 0, 5) as $highlight): ?>
                                        <li><?php echo htmlspecialchars($highlight['text']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Action Buttons -->
            <div class="result-actions mt-3">
                <button class="btn btn-outline-primary btn-sm" onclick="downloadResults()">
                    <i class="fas fa-download me-1"></i>Download Results
                </button>
                <button class="btn btn-outline-secondary btn-sm ms-2" onclick="clearResults()">
                    <i class="fas fa-trash me-1"></i>Clear Results
                </button>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Processing results are incomplete or corrupted.
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

include('database.php');
$userId = $_SESSION['member_id'];

        // ✅ Handle Convert Action
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'convert') {
            
            // === ENHANCED DEBUGGING - AI PROCESSING START ===
            error_log("=== MEDICALVOICE AI PROCESSING DEBUG START ===");
            error_log("MedicalVoice: Convert action received at " . date('Y-m-d H:i:s'));
            error_log("MedicalVoice: POST data: " . print_r($_POST, true));
            error_log("MedicalVoice: Session data: " . print_r($_SESSION, true));
            error_log("MedicalVoice: Server time: " . date('Y-m-d H:i:s'));
            error_log("MedicalVoice: Memory usage: " . memory_get_usage(true) . " bytes");
            
            // === SESSION STATE TRACKING ===
            error_log("=== SESSION STATE TRACKING ===");
            error_log("MedicalVoice: Session ID: " . session_id());
            error_log("MedicalVoice: User ID: " . $_SESSION['member_id']);
            error_log("MedicalVoice: Uploaded file: " . ($_SESSION['uploaded_file'] ?? 'Not set'));
            error_log("MedicalVoice: Log file: " . ($_SESSION['log_file'] ?? 'Not set'));
            error_log("MedicalVoice: JSON file: " . ($_SESSION['json_file'] ?? 'Not set'));
            error_log("MedicalVoice: Processing complete: " . ($_SESSION['processing_complete'] ?? 'Not set'));

    // Check credits
    error_log("MedicalVoice: 🔍 STEP 1 - Checking credits for user ID: " . $userId);
    $stmt = $conn->prepare("SELECT credits FROM members WHERE member_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($credits);
    $stmt->fetch();
    $stmt->close();
    
    error_log("MedicalVoice: ✅ User has " . $credits . " credits");

    if ($credits <= 0) {
        error_log("MedicalVoice: ❌ ERROR - User has no credits, stopping processing");
        echo "<div class='alert alert-danger mt-3 p-3'>
                ❌ You have no credits left. 
                <a href='../buy_credits.php' class='btn btn-primary btn-sm ms-2'>Buy Credits</a>
              </div>";
        exit;
    }

    // Deduct 1 credit
    error_log("MedicalVoice:  STEP 2 - Deducting 1 credit from user");
    $stmt = $conn->prepare("UPDATE members SET credits = credits - 1 WHERE member_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    error_log("MedicalVoice: ✅ Credit deduction completed");

    if (isset($_SESSION['uploaded_file'])) {
        $audioFile = $_SESSION['uploaded_file'];
        $logFile = $_SESSION['log_file'];
        
        error_log("MedicalVoice: 🔍 STEP 3 - File validation and preparation");
        error_log("MedicalVoice: Audio file path: " . $audioFile);
        error_log("MedicalVoice: Log file path: " . $logFile);
        error_log("MedicalVoice: Audio file exists: " . (file_exists($audioFile) ? 'Yes' : 'No'));
        error_log("MedicalVoice: Audio file size: " . (file_exists($audioFile) ? filesize($audioFile) : 'N/A') . " bytes");
        error_log("MedicalVoice: Audio file permissions: " . (file_exists($audioFile) ? substr(sprintf('%o', fileperms($audioFile)), -4) : 'N/A'));
        error_log("MedicalVoice: Audio file readable: " . (file_exists($audioFile) ? (is_readable($audioFile) ? 'Yes' : 'No') : 'N/A'));

        // Create a process lock to prevent multiple executions
        $lockFile = $logFile . '.lock';
        error_log("MedicalVoice: Lock file path: " . $lockFile);
        
        if (file_exists($lockFile)) {
            $lockTime = filemtime($lockFile);
            error_log("MedicalVoice: Lock file exists, created: " . date('Y-m-d H:i:s', $lockTime));
            if (time() - $lockTime < 300) { // 5 minutes lock
                error_log("MedicalVoice: ❌ ERROR - Processing already in progress, stopping");
                echo "<div class='alert alert-warning mt-3 p-3'>
                        ⚠️ Processing already in progress. Please wait a few minutes.
                      </div>";
                exit();
            } else {
                // Remove stale lock
                error_log("MedicalVoice: Removing stale lock file");
                unlink($lockFile);
            }
        }

        // Create lock file
        error_log("MedicalVoice: Creating lock file");
        file_put_contents($lockFile, time());

        // Clear any existing log file
        if (file_exists($logFile)) {
            error_log("MedicalVoice: Removing existing log file");
            unlink($logFile);
        }

        $pythonBinary = __DIR__ . "/myenv/bin/python"; // Default path
        $pythonScript = __DIR__ . "/process_audio.py";
        
        error_log("MedicalVoice: 🔍 STEP 4 - Python environment validation");
        error_log("MedicalVoice: Python script path: " . $pythonScript);
        error_log("MedicalVoice: Python script exists: " . (file_exists($pythonScript) ? 'Yes' : 'No'));
        error_log("MedicalVoice: Python script size: " . (file_exists($pythonScript) ? filesize($pythonScript) : 'N/A') . " bytes");
        error_log("MedicalVoice: Python script readable: " . (file_exists($pythonScript) ? (is_readable($pythonScript) ? 'Yes' : 'No') : 'N/A'));

        // Validate script and python binary
        if (!file_exists($pythonScript)) {
            error_log("MedicalVoice: ❌ CRITICAL ERROR - Python script not found at: " . $pythonScript);
            echo "<div class='alert alert-danger mt-3'>❌ Processing script not available.</div>";
            exit();
        }

        // Find the correct Python binary for Windows
        $possiblePaths = [
            "python",
            "python3",
            "py",
            __DIR__ . "/myenv/Scripts/python.exe",
            __DIR__ . "/myenv/bin/python.exe",
            __DIR__ . "/myenv/bin/python",
            __DIR__ . "/myenv/Scripts/python",
        ];

        error_log("MedicalVoice: Testing Python paths: " . implode(', ', $possiblePaths));
        
        $pythonBinary = null;
        foreach ($possiblePaths as $path) {
            error_log("MedicalVoice: 🔍 Testing Python path: " . $path);
            
            if ($path === "python" || $path === "python3" || $path === "py") {
                $testCommand = $path . " --version 2>&1";
                error_log("MedicalVoice: Testing command: " . $testCommand);
                $output = shell_exec($testCommand);
                error_log("MedicalVoice: Test output: " . ($output ? $output : 'No output'));
                
                if ($output && strpos($output, "Python") !== false) {
                    $pythonBinary = $path;
                    error_log("MedicalVoice: ✅ Found working Python at: " . $path);
                    break;
                }
            } else {
                if (file_exists($path)) {
                    error_log("MedicalVoice: Python file exists at: " . $path);
                    $testCommand = $path . " --version 2>&1";
                    error_log("MedicalVoice: Testing command: " . $testCommand);
                    $output = shell_exec($testCommand);
                    error_log("MedicalVoice: Test output: " . ($output ? $output : 'No output'));
                    
                    if ($output && strpos($output, "Python") !== false) {
                        $pythonBinary = $path;
                        error_log("MedicalVoice: ✅ Found working Python at: " . $path);
                        break;
                    }
                } else {
                    error_log("MedicalVoice: Python file does not exist at: " . $path);
                }
            }
        }

        if (!$pythonBinary) {
            error_log("MedicalVoice: ❌ CRITICAL ERROR - No working Python binary found");
            echo "<div class='alert alert-danger mt-3'>
                    ❌ Processing environment not configured properly. Please contact support.
                  </div>";
            exit();
        }
        
        error_log("MedicalVoice: ✅ Using Python binary: " . $pythonBinary);

        // Build Command for Windows with proper path handling
        if (in_array($pythonBinary, ["python", "python3", "py"])) {
            $command = $pythonBinary . ' "' . str_replace('/', '\\', $pythonScript) . '" "' . str_replace('/', '\\', $audioFile) . '" "' . str_replace('/', '\\', $logFile) . '"';
        } else {
            $command = '"' . str_replace('/', '\\', $pythonBinary) . '" "' . str_replace('/', '\\', $pythonScript) . '" "' . str_replace('/', '\\', $audioFile) . '" "' . str_replace('/', '\\', $logFile) . '"';
        }
        
        error_log("MedicalVoice: 🔍 STEP 5 - Command execution preparation");
        error_log("MedicalVoice: Built command: " . $command);
        error_log("MedicalVoice: Audio file path: " . $audioFile);
        error_log("MedicalVoice: Log file path: " . $logFile);
        error_log("MedicalVoice: Current working directory: " . getcwd());
        error_log("MedicalVoice: Script directory: " . __DIR__);

        // Execute Command with timeout and better error handling
        $output = '';
        $returnCode = -1;
        
        // Log the command being executed
        error_log("MedicalVoice:  STEP 6 - Executing Python command");
        error_log("MedicalVoice: Executing command: " . $command);
        error_log("MedicalVoice: Command execution started at: " . date('Y-m-d H:i:s'));
        
        try {
            $descriptorspec = array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("pipe", "w")
            );

            $process = proc_open($command, $descriptorspec, $pipes);
            
            if (is_resource($process)) {
                error_log("MedicalVoice: ✅ proc_open successful, process started");
                stream_set_blocking($pipes[1], 0);
                stream_set_blocking($pipes[2], 0);
                
                $startTime = time();
                $timeout = 120; // Increased timeout to 2 minutes
                error_log("MedicalVoice: Process timeout set to: " . $timeout . " seconds");
                
                while (proc_get_status($process)['running'] && (time() - $startTime) < $timeout) {
                    $output .= stream_get_contents($pipes[1]);
                    $error = stream_get_contents($pipes[2]);
                    if ($error) {
                        $output .= $error;
                        error_log("MedicalVoice: Python stderr output: " . $error);
                    }
                    usleep(100000);
                }
                
                $output .= stream_get_contents($pipes[1]);
                $error = stream_get_contents($pipes[2]);
                if ($error) {
                    $output .= $error;
                    error_log("MedicalVoice: Final Python stderr output: " . $error);
                }
                
                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                
                $returnCode = proc_close($process);
                error_log("MedicalVoice: ✅ Process completed with return code: " . $returnCode);
                error_log("MedicalVoice: Process execution time: " . (time() - $startTime) . " seconds");
            } else {
                error_log("MedicalVoice: ⚠️ proc_open failed, falling back to shell_exec");
                $output = shell_exec($command . " 2>&1");
                $returnCode = 0; // Assume success for shell_exec
            }
        } catch (Exception $e) {
            error_log("MedicalVoice: ❌ EXCEPTION during process execution: " . $e->getMessage());
            error_log("MedicalVoice: Exception trace: " . $e->getTraceAsString());
            $output = "Exception: " . $e->getMessage();
            $returnCode = -1;
        }

        // Log processing for monitoring
        if ($returnCode !== 0) {
            error_log("MedicalVoice: ❌ Processing failed with return code: " . $returnCode);
        }

        // Parse the output to find the final JSON result (not progress updates)
        error_log("MedicalVoice: 🔍 STEP 7 - Processing output analysis");
        error_log("MedicalVoice: Raw output length: " . strlen($output));
        error_log("MedicalVoice: Raw output: " . substr($output, 0, 500) . (strlen($output) > 500 ? '...' : ''));
        
        $responseData = null;
        $lines = explode("\n", $output);
        error_log("MedicalVoice: Output has " . count($lines) . " lines");
        
        // Look for the final JSON response (contains 'json_file')
        foreach (array_reverse($lines) as $line) {
            $line = trim($line);
            if (!empty($line) && strpos($line, '{') === 0) {
                error_log("MedicalVoice: Found JSON line: " . substr($line, 0, 100) . (strlen($line) > 100 ? '...' : ''));
                $jsonData = json_decode($line, true);
                if ($jsonData && isset($jsonData['json_file'])) {
                    $responseData = $jsonData;
                    error_log("MedicalVoice: ✅ Found valid response data with json_file: " . $jsonData['json_file']);
                    break;
                } else {
                    error_log("MedicalVoice: JSON decode failed or missing json_file key");
                    if ($jsonData) {
                        error_log("MedicalVoice: JSON keys found: " . implode(', ', array_keys($jsonData)));
                    }
                }
            }
        }

        if ($responseData && isset($responseData['json_file']) && (!isset($responseData['status']) || $responseData['status'] === 'success')) {
            error_log("MedicalVoice: ✅ STEP 8 - Processing successful, setting session data");
            $jsonFile = $responseData['json_file'];
            $_SESSION['json_file'] = $jsonFile;
            $_SESSION['processing_complete'] = true;
            error_log("MedicalVoice: JSON file path: " . $jsonFile);
            
            // === SESSION STATE AFTER SUCCESS ===
            error_log("=== SESSION STATE AFTER SUCCESS ===");
            error_log("MedicalVoice: Session ID: " . session_id());
            error_log("MedicalVoice: JSON file set: " . $_SESSION['json_file']);
            error_log("MedicalVoice: Processing complete: " . $_SESSION['processing_complete']);
            error_log("MedicalVoice: File exists: " . (file_exists($_SESSION['json_file']) ? 'Yes' : 'No'));
            error_log("MedicalVoice: File size: " . (file_exists($_SESSION['json_file']) ? filesize($_SESSION['json_file']) : 'N/A') . " bytes");

            // Remove lock file on success
            if (file_exists($lockFile)) {
                unlink($lockFile);
                error_log("MedicalVoice: Lock file removed on success");
            }

            // Enhanced success message with processing stats
            echo "<div class='alert alert-success mt-3 p-3'>
                    <div class='d-flex align-items-center'>
                        <div class='success-icon me-3'>
                            <i class='fas fa-check-circle fa-2x text-success'></i>
                        </div>
                        <div>
                            <h5 class='mb-1'>✅ AI Processing Completed Successfully!</h5>
                            <p class='mb-1'>Your audio has been transcribed and analyzed.</p>";
            
            // Show processing stats if available
            if (isset($responseData['processing_stats'])) {
                $stats = $responseData['processing_stats'];
                echo "<small class='text-muted'>";
                if (isset($stats['word_count'])) {
                    echo "Words: " . number_format($stats['word_count']) . " • ";
                }
                if (isset($stats['audio_duration'])) {
                    echo "Duration: " . gmdate("i:s", $stats['audio_duration']) . " • ";
                }
                if (isset($stats['transcription_confidence'])) {
                    echo "Confidence: " . round($stats['transcription_confidence'] * 100, 1) . "%";
                }
                echo "</small>";
            }
            
            echo "      </div>
                    </div>
                    <div class='mt-3'>
                        <p class='mb-0'>
                            <i class='fas fa-arrow-down me-2'></i>
                            <strong>Next:</strong> Scroll down to <strong>Read Response</strong> section to view your results.
                        </p>
                    </div>
                  </div>";

        } else {
            error_log("MedicalVoice: ❌ STEP 8 - Processing failed - no valid response data found");
            
            // === COMPREHENSIVE FALLBACK ERROR HANDLING ===
            error_log("MedicalVoice: === COMPREHENSIVE ERROR ANALYSIS ===");
            
            // Check if Python script failed
            if ($returnCode !== 0) {
                error_log("MedicalVoice: CRITICAL - Python execution failed (Code: $returnCode)");
                echo "<div class='alert alert-danger mt-3 p-3'>
                        <div class='d-flex align-items-center'>
                            <div class='error-icon me-3'>
                                <i class='fas fa-exclamation-triangle fa-2x text-danger'></i>
                            </div>
                            <div>
                                <h5 class='mb-1'>❌ Python Execution Failed</h5>
                                <p class='mb-0'>Return Code: $returnCode - The Python script could not complete.</p>
                            </div>
                        </div>
                      </div>";
            }
            
            // Check if output contains errors
            if (strpos($output, 'error') !== false) {
                error_log("MedicalVoice: ERROR - Python script error detected in output");
                echo "<div class='alert alert-warning mt-3 p-3'>
                        <div class='d-flex align-items-center'>
                            <div class='warning-icon me-3'>
                                <i class='fas fa-exclamation-triangle fa-2x text-warning'></i>
                            </div>
                            <div>
                                <h5 class='mb-1'>⚠️ Python Script Error</h5>
                                <p class='mb-0'>The Python script encountered an error during execution.</p>
                            </div>
                        </div>
                      </div>";
            }
            
            // Check if JSON file was created
            if (isset($_SESSION['uploaded_file'])) {
                $expectedJson = str_replace('/files/', '/json/', $_SESSION['uploaded_file']) . '.json';
                $expectedJson = str_replace('\\files\\', '\\json\\', $expectedJson); // Windows path fix
                
                error_log("MedicalVoice: Checking for expected JSON file: " . $expectedJson);
                
                if (!file_exists($expectedJson)) {
                    error_log("MedicalVoice: ERROR - Results file not generated at expected location");
                    echo "<div class='alert alert-danger mt-3 p-3'>
                            <div class='d-flex align-items-center'>
                                <div class='error-icon me-3'>
                                    <i class='fas fa-file-excel fa-2x text-danger'></i>
                                </div>
                                <div>
                                    <h5 class='mb-1'>❌ Results File Not Generated</h5>
                                    <p class='mb-0'>Expected file: " . basename($expectedJson) . "</p>
                                    <p class='mb-0'>This indicates the AI processing did not complete successfully.</p>
                                </div>
                            </div>
                          </div>";
                } else {
                    error_log("MedicalVoice: SUCCESS - JSON file found but not in response data");
                    echo "<div class='alert alert-info mt-3 p-3'>
                            <div class='d-flex align-items-center'>
                                <div class='info-icon me-3'>
                                    <i class='fas fa-info-circle fa-2x text-info'></i>
                                </div>
                                <div>
                                    <h5 class='mb-1'>ℹ️ Results File Generated</h5>
                                    <p class='mb-0'>The results file was created but there was an issue with the response.</p>
                                    <p class='mb-0'>File: " . basename($expectedJson) . "</p>
                                </div>
                            </div>
                          </div>";
                }
            }
            
            // Remove lock file on failure
            if (file_exists($lockFile)) {
                unlink($lockFile);
                error_log("MedicalVoice: Lock file removed on failure");
            }

            // Enhanced error reporting for debugging
            echo "<div class='alert alert-danger mt-3 p-3'>
                    <div class='d-flex align-items-center'>
                        <div class='error-icon me-3'>
                            <i class='fas fa-exclamation-triangle fa-2x text-danger'></i>
                        </div>
                        <div>
                            <h5 class='mb-1'>❌ Processing Failed</h5>
                            <p class='mb-0'>The AI processing could not be completed.</p>
                        </div>
                    </div>
                  </div>";
            
            // Always show debug information for troubleshooting
            echo "<div class='card mt-3 p-3' style='background: #f8f9fa;'>
                    <h5>🔍 Comprehensive Debug Information</h5>
                    <p><strong>Return Code:</strong> " . ($returnCode ?? 'Unknown') . "</p>
                    <p><strong>Command:</strong> <code>" . htmlspecialchars($command ?? 'Not set') . "</code></p>
                    <p><strong>Response Data Found:</strong> " . (isset($responseData) ? 'Yes' : 'No') . "</p>";
            if (isset($responseData)) {
                echo "<p><strong>Response Keys:</strong> " . implode(', ', array_keys($responseData)) . "</p>";
            }
            echo "<p><strong>Raw Output Length:</strong> " . strlen($output ?? '') . " characters</p>
                    <p><strong>Raw Output:</strong></p>
                    <pre style='background: #fff; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto; font-size: 11px;'>" . htmlspecialchars($output ?? 'No output') . "</pre>
                    
                    <div class='mt-3'>
                        <h6>📋 Next Steps:</h6>
                        <ol>
                            <li>Check the error logs for detailed information</li>
                            <li>Verify Python environment is working</li>
                            <li>Check if all required dependencies are installed</li>
                            <li>Verify API keys are valid</li>
                            <li>Check storage directory permissions</li>
                            <li>Verify audio file format and size</li>
                        </ol>
                    </div>
                  </div>";
        }

        error_log("=== MEDICALVOICE AI PROCESSING DEBUG END ===");

    } else {
        error_log("MedicalVoice: ❌ ERROR - No uploaded file found in session");
        echo "<div class='alert alert-warning mt-3 p-3'>No uploaded file found in session.</div>";
    }
}
?>

<!-- Enhanced AI Processing Styles -->
<style>
.file-status-section {
    margin-bottom: 2rem;
}

.file-status-success, .file-status-warning {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border: 1px solid rgba(0,0,0,0.05);
}

.file-status-success {
    border-left: 4px solid #48bb78;
}

.file-status-warning {
    border-left: 4px solid #ed8936;
}

.status-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.status-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.file-status-success .status-icon {
    background: linear-gradient(135deg, #48bb78, #38a169);
}

.file-status-warning .status-icon {
    background: linear-gradient(135deg, #ed8936, #dd6b20);
}

.status-info {
    flex: 1;
}

.status-title {
    margin: 0 0 0.5rem 0;
    color: #2d3748;
    font-weight: 600;
    font-size: 1.1rem;
}

.status-file, .status-size, .status-ready, .status-message {
    margin: 0.25rem 0;
    color: #4a5568;
    font-size: 0.95rem;
}

.status-ready {
    color: #48bb78 !important;
    font-weight: 600;
}

.status-message {
    color: #718096;
    margin-bottom: 1rem;
}

.processing-form {
    text-align: center;
}

/* Processing Results */
.processing-results {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border: 1px solid rgba(0,0,0,0.05);
}

.results-container {
    margin-bottom: 1.5rem;
}

.result-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid rgba(0,0,0,0.05);
}

.result-header {
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.result-title {
    margin: 0;
    color: #2d3748;
    font-weight: 600;
    font-size: 1.1rem;
}

.result-content {
    line-height: 1.6;
}

.result-text {
    margin: 0;
    color: #4a5568;
    font-size: 0.95rem;
    white-space: pre-wrap;
}

.result-actions {
    text-align: center;
}

/* Enhanced Results Display Styles */
.results-header {
    text-align: center;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.results-main-title {
    margin: 0 0 1rem 0;
    font-weight: 700;
    font-size: 1.5rem;
}

.results-stats {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255,255,255,0.1);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.result-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e2e8f0;
}

.result-actions-mini {
    display: flex;
    gap: 0.5rem;
}

.medical-analysis {
    line-height: 1.7;
    font-size: 1rem;
}

.enhanced-features {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
}

.features-title {
    color: #2d3748;
    font-weight: 600;
    margin-bottom: 1.5rem;
    text-align: center;
}

.feature-card {
    background: white;
    padding: 1.25rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.05);
}

.feature-card h6 {
    color: #4a5568;
    font-weight: 600;
    margin-bottom: 0.75rem;
    font-size: 1rem;
}

.highlights-list {
    margin: 0;
    padding-left: 1.5rem;
}

.highlights-list li {
    margin-bottom: 0.5rem;
    line-height: 1.5;
    color: #4a5568;
}

/* Enhanced button animations */
.btn {
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.btn:hover {
    transform: translateY(-2px);
}

.btn:active {
    transform: translateY(0);
}

/* Copy button success state */
.btn.btn-success {
    background-color: #48bb78 !important;
    border-color: #48bb78 !important;
}

/* Responsive enhancements */
@media (max-width: 768px) {
    .results-stats {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .stat-item {
        justify-content: center;
    }
    
    .result-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .result-actions-mini {
        justify-content: center;
    }
}

/* Enhanced Processing Progress Styles */
.processing-progress {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-top: 1.5rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border: 1px solid rgba(0,0,0,0.05);
    animation: fadeInUp 0.5s ease;
}

.progress-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.progress-title {
    color: #2d3748;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.progress-stage {
    color: #667eea;
    font-weight: 500;
    font-size: 1rem;
}

.progress-bar-container {
    position: relative;
    margin-bottom: 1rem;
}

.progress-bar {
    width: 100%;
    height: 12px;
    background: #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
    position: relative;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 6px;
    transition: width 0.3s ease;
    position: relative;
    overflow: hidden;
}

.progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background-image: linear-gradient(
        -45deg,
        rgba(255, 255, 255, .2) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, .2) 50%,
        rgba(255, 255, 255, .2) 75%,
        transparent 75%,
        transparent
    );
    background-size: 50px 50px;
    animation: move 2s linear infinite;
}

@keyframes move {
    0% { background-position: 0 0; }
    100% { background-position: 50px 50px; }
}

.progress-percentage {
    text-align: center;
    font-weight: 600;
    color: #667eea;
    margin-top: 0.5rem;
    font-size: 1.1rem;
}

.progress-message {
    text-align: center;
    color: #4a5568;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.progress-time {
    text-align: center;
    color: #718096;
    font-size: 0.85rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .status-content {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .file-status-success .status-icon::after,
    .file-status-warning .status-icon::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .processing-form {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .processing-form .btn {
        width: 100%;
    }
    
    .processing-progress {
        padding: 1.5rem;
    }
}
</style>

<!-- Enhanced JavaScript for AI Processing -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Change file button functionality
    const changeFileBtn = document.getElementById('changeFileBtn');
    if (changeFileBtn) {
        changeFileBtn.addEventListener('click', function() {
            // Scroll to upload section
            const uploadSection = document.getElementById('upload');
            if (uploadSection) {
                uploadSection.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    }
});

// Enhanced copy to clipboard function
function copyToClipboard(type) {
    let text = '';
    if (type === 'transcription') {
        text = document.getElementById('transcription-text').textContent;
    } else if (type === 'analysis') {
        text = document.getElementById('analysis-text').textContent;
    }
    
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-primary');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Failed to copy to clipboard');
    });
}

// Enhanced download results function
function downloadResults() {
    const transcription = document.getElementById('transcription-text')?.textContent || '';
    const analysis = document.getElementById('analysis-text')?.textContent || '';
    
    // Get additional data if available
    const statsElements = document.querySelectorAll('.stat-item');
    let stats = '';
    statsElements.forEach(stat => {
        stats += stat.textContent + '\n';
    });
    
    const timestamp = new Date().toLocaleString();
    const content = `MEDICAL VOICE AI PROCESSING RESULTS
${'='.repeat(50)}

PROCESSING INFORMATION:
${stats}
Generated on: ${timestamp}

SPEECH TRANSCRIPTION:
${'='.repeat(30)}
${transcription}

AI MEDICAL ANALYSIS:
${'='.repeat(30)}
${analysis}

${'='.repeat(50)}
This report was generated by Medical Voice AI
Professional healthcare documentation system
`;
    
    const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `medicalvoice_results_${new Date().toISOString().slice(0,10)}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Clear results function
function clearResults() {
    if (confirm('Are you sure you want to clear the processing results?')) {
        // Send AJAX request to clear results
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear_results'
        })
        .then(response => {
            // Reload page to refresh results
            window.location.reload();
        })
        .catch(error => {
            console.error('Error clearing results:', error);
            alert('Failed to clear results. Please try again.');
        });
    }
}

// Prevent any default form submission behavior
document.addEventListener('DOMContentLoaded', function() {
    // Remove any existing form elements that might cause conflicts
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
    });
});

// AI Processing AJAX Handler
function startAIProcessing() {
    console.log('=== STARTING AI PROCESSING ===');
    console.log('Timestamp:', new Date().toISOString());
    console.log('Current URL:', window.location.href);
    console.log('Session storage:', Object.keys(sessionStorage));
    
    // Get the button and form container
    const startBtn = document.getElementById('startProcessingBtn');
    const formContainer = document.getElementById('processingForm');
    
    // Validate that we have the required elements
    if (!startBtn || !formContainer) {
        console.error('❌ Required elements not found');
        console.error('Start button:', !!startBtn);
        console.error('Form container:', !!formContainer);
        alert('Error: Processing form not properly initialized');
        return;
    }
    
    console.log('✅ Required elements found');
    console.log('Start button text:', startBtn.textContent);
    console.log('Form container HTML:', formContainer.innerHTML.substring(0, 200));
    
    // Disable button and show loading state
    startBtn.disabled = true;
    startBtn.innerHTML = '<i class="fas fa-cog fa-spin me-2"></i>Processing...';
    
    // Show progress section
    const progressSection = document.getElementById('processingProgress');
    if (progressSection) {
        progressSection.style.display = 'block';
    }
    
    // Update workflow step if function exists
    if (typeof updateWorkflowStep === 'function') {
        updateWorkflowStep('transcription', 'processing');
        updateWorkflowStep('analysis', 'processing');
    }
    
    // Create FormData manually
    const formData = new FormData();
    formData.append('action', 'convert');
    
    // Start progress simulation
    simulateAIProgress();
    
    // Make AJAX request
                    fetch('./python_process.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('=== AJAX RESPONSE RECEIVED ===');
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        console.log('Response type:', response.type);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            console.error('❌ HTTP Error:', response.status, response.statusText);
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return response.text();
    })
    .then(html => {
        console.log('=== RESPONSE CONTENT ANALYSIS ===');
        console.log('Response received, length:', html.length);
        console.log('Response preview:', html.substring(0, 500));
        
        // Check for common error patterns
        if (html.includes('error') || html.includes('Error')) {
            console.warn('⚠️ Response contains error indicators');
        }
        if (html.includes('success') || html.includes('Success')) {
            console.log('✅ Response contains success indicators');
        }
        
        // Parse the HTML response to extract success/error messages
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Look for success or error messages
        const successAlert = doc.querySelector('.alert-success');
        const errorAlert = doc.querySelector('.alert-danger');
        const warningAlert = doc.querySelector('.alert-warning');
        
        if (successAlert) {
            console.log('✅ SUCCESS DETECTED');
            console.log('Success alert content:', successAlert.textContent);
            console.log('Success alert HTML:', successAlert.outerHTML.substring(0, 300));
            
            // Success - replace the form area with the success message
            const formContainer = document.querySelector('.processing-form');
            if (formContainer) {
                console.log('✅ Updating form container with success message');
                formContainer.innerHTML = successAlert.outerHTML;
                console.log('Form container updated successfully');
            } else {
                console.error('❌ Form container not found for update');
            }
            
            // Update workflow steps
            if (typeof updateWorkflowStep === 'function') {
                updateWorkflowStep('transcription', 'completed');
                updateWorkflowStep('analysis', 'completed');
                updateWorkflowStep('results', 'ready');
            }
            
            // Show success notification
            if (typeof showNotification === 'function') {
                showNotification('AI Processing completed successfully!', 'success');
            } else {
                alert('AI Processing completed successfully!');
            }
            
            // Hide progress section
            if (progressSection) {
                progressSection.style.display = 'none';
            }
            
            // Reload page after a delay to show results
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            
        } else if (errorAlert || warningAlert) {
            console.log('⚠️ ERROR/WARNING DETECTED');
            const alert = errorAlert || warningAlert;
            console.log('Alert type:', alert.className);
            console.log('Alert content:', alert.textContent);
            console.log('Alert HTML:', alert.outerHTML.substring(0, 300));
            
            // Error or warning - show the message
            const formContainer = document.querySelector('.processing-form');
            if (formContainer) {
                console.log('✅ Updating form container with error/warning message');
                formContainer.innerHTML = alert.outerHTML;
                console.log('Form container updated with error message');
            } else {
                console.error('❌ Form container not found for error update');
            }
            
            // Reset workflow steps
            if (typeof updateWorkflowStep === 'function') {
                updateWorkflowStep('transcription', 'ready');
                updateWorkflowStep('analysis', 'pending');
            }
            
            // Show error notification
            if (typeof showNotification === 'function') {
                showNotification('AI Processing failed. Please check the details above.', 'error');
            } else {
                alert('AI Processing failed. Please check the details above.');
            }
            
            // Hide progress section
            if (progressSection) {
                progressSection.style.display = 'none';
            }
            
        } else {
            console.log('No clear success/error message found');
            // No clear success/error message found
            console.warn('No clear success/error message found in response');
            
            // Check if there's any content that might indicate success
            if (html.includes('successfully') || html.includes('completed') || html.includes('✅')) {
                console.log('Assuming success based on content');
                // Assume success
                if (typeof updateWorkflowStep === 'function') {
                    updateWorkflowStep('transcription', 'completed');
                    updateWorkflowStep('analysis', 'completed');
                    updateWorkflowStep('results', 'ready');
                }
                
                if (typeof showNotification === 'function') {
                    showNotification('AI Processing completed!', 'success');
                } else {
                    alert('AI Processing completed!');
                }
                
                // Reload page after a delay
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                console.log('Assuming failure based on content');
                // Assume failure
                if (typeof updateWorkflowStep === 'function') {
                    updateWorkflowStep('transcription', 'ready');
                    updateWorkflowStep('analysis', 'pending');
                }
                
                if (typeof showNotification === 'function') {
                    showNotification('AI Processing failed. Please try again.', 'error');
                } else {
                    alert('AI Processing failed. Please try again.');
                }
            }
            
            // Hide progress section
            if (progressSection) {
                progressSection.style.display = 'none';
            }
        }
    })
    .catch(error => {
        console.error('=== AI PROCESSING ERROR ===');
        console.error('Error type:', error.constructor.name);
        console.error('Error message:', error.message);
        console.error('Error stack:', error.stack);
        console.error('Error occurred at:', new Date().toISOString());
        
        // Reset button state
        startBtn.disabled = false;
        startBtn.innerHTML = '<i class="fas fa-play me-2"></i> Start AI Processing';
        console.log('✅ Button state reset');
        
        // Hide progress section
        if (progressSection) {
            progressSection.style.display = 'none';
            console.log('✅ Progress section hidden');
        }
        
        // Reset workflow steps
        if (typeof updateWorkflowStep === 'function') {
            updateWorkflowStep('transcription', 'ready');
            updateWorkflowStep('analysis', 'pending');
            console.log('✅ Workflow steps reset');
        } else {
            console.warn('⚠️ updateWorkflowStep function not available');
        }
        
        // Show error notification
        if (typeof showNotification === 'function') {
            showNotification('AI Processing failed: ' + error.message, 'error');
            console.log('✅ Error notification shown via showNotification');
        } else {
            alert('AI Processing failed: ' + error.message);
            console.log('✅ Error notification shown via alert');
        }
        
        console.log('=== ERROR HANDLING COMPLETED ===');
    });
}

// Progress simulation for AI processing
function simulateAIProgress() {
    const progressFill = document.getElementById('progressFill');
    const progressPercentage = document.getElementById('progressPercentage');
    const progressMessage = document.getElementById('progressMessage');
    const progressStage = document.getElementById('progressStage');
    
    if (!progressFill || !progressPercentage || !progressMessage || !progressStage) {
        console.warn('Progress elements not found');
        return;
    }
    
    let progress = 0;
    const stages = [
        'Initializing AI processing...',
        'Loading audio file...',
        'Starting speech recognition...',
        'Processing audio with AssemblyAI...',
        'Analyzing medical content...',
        'Generating insights with OpenAI...',
        'Finalizing results...'
    ];
    
    const interval = setInterval(() => {
        progress += Math.random() * 8;
        if (progress > 95) progress = 95;
        
        progressFill.style.width = progress + '%';
        progressPercentage.textContent = Math.round(progress) + '%';
        
        // Update stage based on progress
        const stageIndex = Math.floor((progress / 100) * stages.length);
        if (stageIndex < stages.length) {
            progressStage.textContent = stages[stageIndex];
        }
        
        if (progress < 20) {
            progressMessage.textContent = 'Setting up AI processing environment...';
        } else if (progress < 40) {
            progressMessage.textContent = 'Converting speech to text...';
        } else if (progress < 60) {
            progressMessage.textContent = 'Analyzing medical terminology...';
        } else if (progress < 80) {
            progressMessage.textContent = 'Generating medical insights...';
        } else {
            progressMessage.textContent = 'Finalizing analysis results...';
        }
        
        if (progress >= 95) {
            clearInterval(interval);
        }
    }, 300);
}

// Debug function to check if everything is loaded
function debugProcessingSetup() {
    console.log('=== MedicalVoice Processing Debug ===');
    console.log('Start button:', document.getElementById('startProcessingBtn'));
    console.log('Form container:', document.getElementById('processingForm'));
    console.log('Progress section:', document.getElementById('processingProgress'));
    console.log('updateWorkflowStep function:', typeof updateWorkflowStep);
    console.log('showNotification function:', typeof showNotification);
    console.log('Current URL:', window.location.href);
    console.log('Session storage:', sessionStorage);
    console.log('=====================================');
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('MedicalVoice Processing initialized');
    debugProcessingSetup();
    
    // Ensure the start button has the correct event handler
    const startBtn = document.getElementById('startProcessingBtn');
    if (startBtn) {
        // Remove any existing click handlers
        startBtn.replaceWith(startBtn.cloneNode(true));
        
        // Get the new button and add the handler
        const newStartBtn = document.getElementById('startProcessingBtn');
        if (newStartBtn) {
            newStartBtn.addEventListener('click', startAIProcessing);
        }
    }
    
    // Initialize comprehensive testing functions
    initializeTestingFunctions();
});

// === COMPREHENSIVE TESTING FUNCTIONS ===
function initializeTestingFunctions() {
    console.log('=== MedicalVoice Testing Functions Initialized ===');
    
    // Add testing functions to global scope for console access
    window.testMedicalVoice = {
        debugSetup: debugProcessingSetup,
        testUpload: testUploadFunctionality,
        testProcessing: testProcessingFunctionality,
        testResults: testResultsFunctionality,
        testDownload: testDownloadFunctionality,
        testNewAnalysis: testNewAnalysisReset,
        testSessionState: testSessionState,
        runFullTest: runFullSystemTest
    };
    
    console.log('Testing functions available. Use: window.testMedicalVoice.runFullTest()');
}

// === UPLOAD FUNCTIONALITY TESTING ===
function testUploadFunctionality() {
    console.log('=== TESTING UPLOAD FUNCTIONALITY ===');
    
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('audioFile');
    
    console.log('Upload area found:', !!uploadArea);
    console.log('File input found:', !!fileInput);
    
    if (uploadArea) {
        console.log('Upload area HTML:', uploadArea.innerHTML.substring(0, 200));
        console.log('Upload area classes:', uploadArea.className);
    }
    
    if (fileInput) {
        console.log('File input accept:', fileInput.accept);
        console.log('File input multiple:', fileInput.multiple);
    }
    
    // Test drag and drop events
    if (uploadArea) {
        const events = ['dragenter', 'dragover', 'dragleave', 'drop'];
        events.forEach(event => {
            console.log(`${event} event listener:`, uploadArea.on[event] ? 'Present' : 'Missing');
        });
    }
    
    return {
        uploadArea: !!uploadArea,
        fileInput: !!fileInput,
        dragDropEvents: events ? events.length : 0
    };
}

// === PROCESSING FUNCTIONALITY TESTING ===
function testProcessingFunctionality() {
    console.log('=== TESTING PROCESSING FUNCTIONALITY ===');
    
    const startBtn = document.getElementById('startProcessingBtn');
    const formContainer = document.getElementById('processingForm');
    const progressSection = document.getElementById('processingProgress');
    
    console.log('Start button found:', !!startBtn);
    console.log('Form container found:', !!formContainer);
    console.log('Progress section found:', !!progressSection);
    
    if (startBtn) {
        console.log('Start button text:', startBtn.textContent);
        console.log('Start button disabled:', startBtn.disabled);
        console.log('Start button onclick:', startBtn.onclick);
    }
    
    if (formContainer) {
        console.log('Form container HTML:', formContainer.innerHTML.substring(0, 200));
    }
    
    if (progressSection) {
        console.log('Progress section display:', progressSection.style.display);
        console.log('Progress elements:', {
            progressFill: !!document.getElementById('progressFill'),
            progressPercentage: !!document.getElementById('progressPercentage'),
            progressMessage: !!document.getElementById('progressMessage'),
            progressStage: !!document.getElementById('progressStage')
        });
    }
    
    return {
        startButton: !!startBtn,
        formContainer: !!formContainer,
        progressSection: !!progressSection,
        progressElements: progressSection ? 4 : 0
    };
}

// === RESULTS FUNCTIONALITY TESTING ===
function testResultsFunctionality() {
    console.log('=== TESTING RESULTS FUNCTIONALITY ===');
    
    const resultsSection = document.querySelector('.processing-results');
    const downloadBtn = document.querySelector('.btn-download');
    const newAnalysisBtn = document.querySelector('#startNewAnalysis');
    
    console.log('Results section found:', !!resultsSection);
    console.log('Download button found:', !!downloadBtn);
    console.log('New analysis button found:', !!newAnalysisBtn);
    
    if (resultsSection) {
        console.log('Results section HTML:', resultsSection.innerHTML.substring(0, 300));
        console.log('Results section display:', resultsSection.style.display);
    }
    
    if (downloadBtn) {
        console.log('Download button href:', downloadBtn.href);
        console.log('Download button onclick:', downloadBtn.onclick);
    }
    
    if (newAnalysisBtn) {
        console.log('New analysis button onclick:', newAnalysisBtn.onclick);
    }
    
    return {
        resultsSection: !!resultsSection,
        downloadButton: !!downloadBtn,
        newAnalysisButton: !!newAnalysisBtn
    };
}

// === DOWNLOAD FUNCTIONALITY TESTING ===
function testDownloadFunctionality() {
    console.log('=== TESTING DOWNLOAD FUNCTIONALITY ===');
    
    const downloadBtn = document.querySelector('.btn-download');
    if (!downloadBtn) {
        console.log('Download button not found');
        return false;
    }
    
    console.log('Download button found:', downloadBtn);
    console.log('Download href:', downloadBtn.href);
    console.log('Download onclick:', downloadBtn.onclick);
    
    // Test if file exists
    if (downloadBtn.href && downloadBtn.href !== '#') {
        fetch(downloadBtn.href)
            .then(response => {
                console.log('Download test response:', response.status, response.ok);
                if (response.ok) {
                    console.log('✅ Download file accessible');
                } else {
                    console.log('❌ Download file not accessible');
                }
            })
            .catch(error => {
                console.error('Download test failed:', error);
            });
    } else {
        console.log('Download button has no valid href');
    }
    
    return true;
}

// === NEW ANALYSIS RESET TESTING ===
function testNewAnalysisReset() {
    console.log('=== TESTING NEW ANALYSIS RESET ===');
    
    // Check current session state
    const currentUploadedFile = sessionStorage.getItem('uploaded_file');
    const currentProcessingState = document.querySelector('.processing-form')?.innerHTML;
    
    console.log('Current uploaded file (session):', currentUploadedFile);
    console.log('Current processing state:', currentProcessingState?.substring(0, 200));
    
    // Look for new analysis button
    const newAnalysisBtn = document.querySelector('#startNewAnalysis');
    if (newAnalysisBtn) {
        console.log('New analysis button found, testing click...');
        
        // Store current state
        const beforeState = {
            uploadedFile: currentUploadedFile,
            processingState: currentProcessingState
        };
        
        // Click button
        newAnalysisBtn.click();
        console.log('New analysis button clicked');
        
        // Check if session was cleared
        setTimeout(() => {
            const afterState = {
                uploadedFile: sessionStorage.getItem('uploaded_file'),
                processingState: document.querySelector('.processing-form')?.innerHTML
            };
            
            console.log('State before reset:', beforeState);
            console.log('State after reset:', afterState);
            
            const wasReset = beforeState.uploadedFile !== afterState.uploadedFile;
            console.log('Session was reset:', wasReset);
        }, 1000);
        
        return true;
    } else {
        console.log('New analysis button not found');
        return false;
    }
}

// === SESSION STATE TESTING ===
function testSessionState() {
    console.log('=== TESTING SESSION STATE ===');
    
    // Check session storage
    const sessionData = {};
    for (let i = 0; i < sessionStorage.length; i++) {
        const key = sessionStorage.key(i);
        sessionData[key] = sessionStorage.getItem(key);
    }
    
    console.log('Session storage data:', sessionData);
    
    // Check local storage
    const localData = {};
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        localData[key] = localStorage.getItem(key);
    }
    
    console.log('Local storage data:', localData);
    
    // Check cookies
    console.log('Cookies:', document.cookie);
    
    // Check current page state
    const pageState = {
        url: window.location.href,
        hash: window.location.hash,
        title: document.title,
        bodyClasses: document.body.className
    };
    
    console.log('Page state:', pageState);
    
    return {
        sessionStorage: Object.keys(sessionData).length,
        localStorage: Object.keys(localData).length,
        cookies: document.cookie ? document.cookie.split(';').length : 0,
        pageState: pageState
    };
}

// === FULL SYSTEM TEST ===
function runFullSystemTest() {
    console.log('=== RUNNING FULL MEDICALVOICE SYSTEM TEST ===');
    console.log('Test started at:', new Date().toISOString());
    
    const results = {
        upload: testUploadFunctionality(),
        processing: testProcessingFunctionality(),
        results: testResultsFunctionality(),
        download: testDownloadFunctionality(),
        newAnalysis: testNewAnalysisReset(),
        session: testSessionState()
    };
    
    console.log('=== FULL TEST RESULTS ===');
    console.table(results);
    
    // Calculate overall score
    const totalTests = Object.keys(results).length;
    const passedTests = Object.values(results).filter(result => result !== false).length;
    const score = (passedTests / totalTests) * 100;
    
    console.log(`Overall Test Score: ${score.toFixed(1)}% (${passedTests}/${totalTests})`);
    
    if (score >= 90) {
        console.log('✅ EXCELLENT: System is working properly');
    } else if (score >= 70) {
        console.log('⚠️ GOOD: Minor issues detected');
    } else if (score >= 50) {
        console.log('❌ FAIR: Several issues detected');
    } else {
        console.log('🚨 POOR: Major issues detected');
    }
    
    return results;
}
</script>

<!-- Read Response Section -->
<h2 id="read" class="mt-5 mb-4 text-info">
    <i class="fas fa-book-open"></i> Read Response
</h2>

<form action="" method="post" class="mb-4">
    <input type="hidden" name="action" value="read">
    <button type="submit" name="read" class="btn btn-info btn-lg">
        <i class="fas fa-eye"></i> Read Response
    </button>
</form>

<?php
// ✅ Handle Read Response Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'read') {

    if (isset($_SESSION['json_file'])) {
        $jsonFile = $_SESSION['json_file'];

        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $responseData = json_decode($jsonData, true);

            if (isset($responseData['transcribed_text']) && isset($responseData['chatgpt_response'])) {
                echo "<div class='card mt-3 p-4 shadow-sm'>
                        <h4 class='mb-3 text-primary'>Transcribed Text:</h4>
                        <p class='border rounded p-3 bg-light'>" . nl2br(htmlspecialchars($responseData['transcribed_text'])) . "</p>
                        <h4 class='mt-4 mb-3 text-success'>ChatGPT Response:</h4>
                        <p class='border rounded p-3 bg-light'>" . nl2br(htmlspecialchars($responseData['chatgpt_response'])) . "</p>
                      </div>";
            } else {
                echo "<div class='alert alert-warning mt-3 p-3'>Invalid response data in JSON file.</div>";
            }
        } else {
            echo "<div class='alert alert-danger mt-3 p-3'>JSON file not found.</div>";
        }
    } else {
        echo "<div class='alert alert-warning mt-3 p-3'>No JSON file path found in session.</div>";
    }
}
?>

<!-- Post-Read Action: Start a new upload session -->
<div class="mt-4">
    <a href="clear_session.php?anchor=upload" class="btn btn-primary btn-lg">
        <i class="fas fa-file-audio me-2"></i>Start New Audio Upload
    </a>
    
</div>
