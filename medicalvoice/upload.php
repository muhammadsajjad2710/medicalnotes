<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session data should NEVER be cleared automatically during normal workflow
// Only the clear_session.php file should clear session data

// Define storage paths inside medicalvoice
$baseDir = __DIR__ . '/storage/uploads/audio';
$audioDir = $baseDir . '/files/';
$jsonDir = $baseDir . '/json/';
$logDir = $baseDir . '/logs/';

// ✅ Ensure directories exist
foreach ([$audioDir, $jsonDir, $logDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Test endpoint to verify upload handler is working
if (isset($_GET['test']) && $_GET['test'] === 'upload') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Upload handler is working correctly',
        'timestamp' => date('Y-m-d H:i:s'),
        'directories' => [
            'base' => $baseDir,
            'audio' => $audioDir,
            'json' => $jsonDir,
            'logs' => $logDir
        ],
        'permissions' => [
            'audio_writable' => is_writable($audioDir),
            'json_writable' => is_writable($jsonDir),
            'logs_writable' => is_writable($logDir)
        ],
        'session_status' => session_status(),
        'php_upload_settings' => [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_file_uploads' => ini_get('max_file_uploads')
        ]
    ]);
    exit;
}

// Handle file upload via AJAX - MUST BE BEFORE ANY HTML OUTPUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_file') {
    
    $response = array();
    
    // Enhanced error logging
    error_log("MedicalVoice: Upload request received");
    error_log("MedicalVoice: POST data: " . print_r($_POST, true));
    error_log("MedicalVoice: FILES data: " . print_r($_FILES, true));
    
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        // Check file size (50MB limit)
        if ($_FILES['audio_file']['size'] > 50 * 1024 * 1024) {
            $response['success'] = false;
            $response['message'] = "File size exceeds 50MB limit.";
            error_log("MedicalVoice: File size too large: " . $_FILES['audio_file']['size']);
        } else {
            $fileTmp = $_FILES['audio_file']['tmp_name'];
            $fileName = basename($_FILES['audio_file']['name']);
            $targetFile = $audioDir . $fileName;
            
            error_log("MedicalVoice: Processing file: $fileName");
            error_log("MedicalVoice: Temp file: $fileTmp");
            error_log("MedicalVoice: Target file: $targetFile");
            error_log("MedicalVoice: Audio directory exists: " . (is_dir($audioDir) ? 'Yes' : 'No'));
            error_log("MedicalVoice: Audio directory writable: " . (is_writable($audioDir) ? 'Yes' : 'No'));

            // Check if file already exists
            if (file_exists($targetFile)) {
                // Set session data even for existing files
                $_SESSION['uploaded_file'] = $targetFile;
                $_SESSION['log_file'] = $logDir . pathinfo($fileName, PATHINFO_FILENAME) . '.log';
                
                $response['success'] = true;
                $response['message'] = "File '$fileName' already exists. No need to upload again.";
                $response['file_path'] = $targetFile;
                $response['file_name'] = $fileName;
                $response['already_exists'] = true;
                error_log("MedicalVoice: File already exists, using existing file");
            } else {
                if (move_uploaded_file($fileTmp, $targetFile)) {
                    $_SESSION['uploaded_file'] = $targetFile;
                    $_SESSION['log_file'] = $logDir . pathinfo($fileName, PATHINFO_FILENAME) . '.log';
                    
                    $response['success'] = true;
                    $response['message'] = "File '$fileName' uploaded successfully!";
                    $response['file_path'] = $targetFile;
                    $response['file_name'] = $fileName;
                    $response['already_exists'] = false;
                    error_log("MedicalVoice: File uploaded successfully to: $targetFile");
                } else {
                    $response['success'] = false;
                    $response['message'] = "Failed to upload file. Please try again.";
                    error_log("MedicalVoice: move_uploaded_file failed for: $fileTmp to $targetFile");
                    error_log("MedicalVoice: PHP upload error: " . error_get_last()['message'] ?? 'Unknown error');
                }
            }
        }
    } else {
        $response['success'] = false;
        $response['message'] = "No file uploaded or an error occurred.";
        if (isset($_FILES['audio_file'])) {
            $errorMsg = getUploadErrorMessage($_FILES['audio_file']['error']);
            $response['message'] = "Upload error: " . $errorMsg;
            error_log("MedicalVoice: Upload error: " . $errorMsg);
        }
        error_log("MedicalVoice: No valid file in upload request");
    }
    
    error_log("MedicalVoice: Sending response: " . json_encode($response));
    
    // Return JSON response for AJAX - MUST EXIT HERE
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // CRITICAL: Stop execution here
}

// Helper function to get upload error messages
function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_OK:
            return 'No errors.';
        case UPLOAD_ERR_INI_SIZE:
            return 'Larger than upload_max_filesize.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'Larger than form MAX_FILE_SIZE.';
        case UPLOAD_ERR_PARTIAL:
            return 'Partial upload.';
        case UPLOAD_ERR_NO_FILE:
            return 'No file.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'No temporary directory.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Can\'t write to disk.';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension.';
        default:
            return 'Unknown upload error.';
    }
}

// ONLY DISPLAY HTML IF THIS IS NOT AN AJAX REQUEST
if (!isset($_POST['action']) || $_POST['action'] !== 'upload_file') {
?>
<!-- Simple Audio Upload Interface -->
<div class="upload-container">
    <!-- Upload Area -->
    <div class="upload-area" id="uploadArea">
        <div class="upload-content">
            <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <h3 class="upload-title">Upload Medical Audio File</h3>
            <p class="upload-description">
                Click to browse or drag and drop your audio file here
            </p>
            <div class="upload-formats">
                <span class="format-tag">MP3</span>
                <span class="format-tag">M4A</span>
                <span class="format-tag">WAV</span>
                <span class="format-tag">AAC</span>
            </div>
            <div class="upload-hint">
                <small class="text-muted">Maximum file size: 50MB</small>
            </div>
        </div>
        
        <!-- Hidden File Input -->
        <input type="file" name="audio_file" id="audioFile" class="file-input" accept="audio/*" required>
    </div>

    <!-- Enhanced File Preview with Audio Player -->
    <div class="file-preview" id="filePreview" style="display: none;">
        <div class="preview-header">
            <h4 class="preview-title">
                <i class="fas fa-file-audio me-2"></i>File Selected & Preview
            </h4>
        </div>
        <div class="preview-content">
            <div class="file-info">
                <div class="file-icon">
                    <i class="fas fa-music"></i>
                </div>
                <div class="file-details">
                    <h4 class="file-name" id="fileName"></h4>
                    <p class="file-size" id="fileSize"></p>
                    <p class="file-type" id="fileType"></p>
                    <p class="file-status text-success">
                        <i class="fas fa-check-circle me-1"></i>File validated and ready
                    </p>
                </div>
            </div>
            
            <!-- Audio Preview Player -->
            <div class="audio-preview" id="audioPreview" style="display: none;">
                <div class="audio-player-container">
                    <h5 class="audio-preview-title">
                        <i class="fas fa-play-circle me-2"></i>Audio Preview
                    </h5>
                    <audio controls class="audio-player" id="audioPlayer">
                        <source id="audioSource" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                    <div class="audio-info" id="audioInfo">
                        <small class="text-muted">Click play to preview your audio before processing</small>
                    </div>
                </div>
            </div>
            
            <div class="preview-actions">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="changeFileBtn">
                    <i class="fas fa-edit me-1"></i>Change File
                </button>
                <button type="button" class="btn btn-primary btn-lg" id="uploadBtn">
                    <i class="fas fa-upload me-2"></i>Upload Audio File
                </button>
            </div>
        </div>
    </div>

    <!-- Upload Progress -->
    <div class="upload-progress" id="uploadProgress" style="display: none;">
        <div class="progress-content">
            <div class="progress-icon">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <div class="progress-info">
                <h5 class="progress-title">Uploading Audio File</h5>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p class="progress-text">Please wait while we upload your file...</p>
            </div>
        </div>
    </div>
</div>



<?php
// Display current session status
if (isset($_SESSION['uploaded_file']) && file_exists($_SESSION['uploaded_file'])) {
    $currentFile = basename($_SESSION['uploaded_file']);
    echo "<div class='upload-success'>
            <div class='success-content'>
                <div class='success-icon'>
                    <i class='fas fa-check-circle'></i>
                </div>
                <div class='success-text'>
                    <h4>✅ File Ready for Processing</h4>
                    <p><strong>Current File:</strong> $currentFile</p>
                    <p class='text-muted'>Ready for AI processing</p>
                    <div class='next-step-info mt-3'>
                        <p class='next-step-text text-primary'>
                            <i class='fas fa-arrow-down me-2'></i>
                            <strong>Next:</strong> Scroll down to <strong>AI Processing</strong> section to analyze your audio.
                        </p>
                    </div>
                </div>
            </div>
          </div>";
}
?>

<!-- Simple Upload Styles -->
<style>
.upload-container {
    width: 100%;
}

.upload-area {
    border: 2px dashed #e2e8f0;
    border-radius: 16px;
    padding: 3rem 2rem;
    text-align: center;
    background: #f8fafc;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.upload-area:hover {
    border-color: #667eea;
    background: #f1f5f9;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
}

.upload-area.dragover {
    border-color: #667eea;
    background: #e0e7ff;
    transform: scale(1.01);
    box-shadow: 0 12px 30px rgba(102, 126, 234, 0.2);
}

.upload-content {
    pointer-events: none;
}

.upload-icon {
    font-size: 3.5rem;
    color: #667eea;
    margin-bottom: 1.5rem;
    opacity: 0.8;
}

.upload-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.75rem;
}

.upload-description {
    color: #718096;
    margin-bottom: 1.5rem;
    font-size: 1rem;
    line-height: 1.5;
}

.upload-formats {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.format-tag {
    background: #667eea;
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 16px;
    font-size: 0.85rem;
    font-weight: 500;
}

.upload-hint {
    margin-top: 1rem;
}

.file-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

/* File Preview */
.file-preview {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-top: 1.5rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border: 1px solid rgba(0,0,0,0.05);
}

.preview-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.preview-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2d3748;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
    flex-wrap: wrap;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex: 1;
}

.file-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.file-name {
    margin: 0 0 0.5rem 0;
    color: #2d3748;
    font-weight: 600;
    font-size: 1.1rem;
}

.file-size {
    margin: 0 0 0.5rem 0;
    color: #718096;
    font-size: 0.9rem;
}

.file-status {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 500;
}

.preview-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

/* Upload Progress */
.upload-progress {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-top: 1.5rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    text-align: center;
}

.progress-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    justify-content: center;
}

.progress-icon {
    font-size: 2rem;
    color: #667eea;
}

.progress-info {
    flex: 1;
    max-width: 400px;
}

.progress-title {
    margin: 0 0 1rem 0;
    color: #2d3748;
    font-weight: 600;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    width: 0%;
    transition: width 0.3s ease;
    border-radius: 4px;
}

.progress-text {
    color: #718096;
    margin: 0;
    font-size: 0.9rem;
}

/* Enhanced Audio Preview Styles */
.audio-preview {
    margin: 1.5rem 0;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.audio-preview-title {
    color: #2d3748;
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 1rem;
}

.audio-player-container {
    text-align: center;
}

.audio-player {
    width: 100%;
    max-width: 400px;
    margin: 1rem 0;
    border-radius: 8px;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.audio-info {
    margin-top: 0.75rem;
}

.file-type {
    margin: 0.25rem 0;
    color: #718096;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Success/Error Messages */
.upload-success, .upload-error {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    margin-top: 1.5rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.next-step-info {
    background: #e8f4fd;
    border: 1px solid #bee3f8;
    border-radius: 8px;
    padding: 0.75rem;
    margin-top: 1rem;
}

.next-step-text {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 500;
}

.success-content, .error-content {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.success-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #48bb78, #38a169);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.error-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #f56565, #e53e3e);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.success-text h4, .error-text h4 {
    margin: 0 0 0.5rem 0;
    color: #2d3748;
    font-weight: 600;
}

.success-text p, .error-text p {
    margin: 0.25rem 0;
    color: #718096;
}

/* Responsive Design */
@media (max-width: 768px) {
    .upload-area {
        padding: 2rem 1rem;
    }
    
    .preview-content {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
    }
    
    .preview-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .preview-actions .btn {
        width: 100%;
    }
    
    .success-content, .error-content {
        flex-direction: column;
        text-align: center;
    }
    
    .progress-content {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<!-- Simple Upload JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('audioFile');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const uploadBtn = document.getElementById('uploadBtn');
    const changeFileBtn = document.getElementById('changeFileBtn');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressFill = document.getElementById('progressFill');
    
    let selectedFile = null;

    // Single click handler for file selection
    uploadArea.addEventListener('click', function(e) {
        if (e.target === uploadArea || e.target.closest('.upload-content')) {
            fileInput.click();
        }
    });

    // Drag and drop functionality
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
            handleFile(files[0]);
        }
    });

    // File input change
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    });

    // Change file button
    changeFileBtn.addEventListener('click', function() {
        resetUpload();
        fileInput.click();
    });

    function handleFile(file) {
        // Enhanced file validation
        const supportedTypes = ['audio/mpeg', 'audio/mp4', 'audio/wav', 'audio/aac', 'audio/flac', 'audio/ogg'];
        const supportedExtensions = ['.mp3', '.m4a', '.wav', '.aac', '.flac', '.ogg'];
        
        if (!file.type.startsWith('audio/') && !supportedTypes.includes(file.type)) {
            showMessage('error', 'Please select a supported audio file (MP3, M4A, WAV, AAC, FLAC, or OGG).');
            return;
        }

        // Validate file size (configurable, default 50MB)
        const maxSize = 50 * 1024 * 1024; // 50MB
        if (file.size > maxSize) {
            showMessage('error', `File size exceeds ${Math.round(maxSize / (1024 * 1024))}MB limit. Please select a smaller file.`);
            return;
        }

        // Validate minimum file size (1KB)
        if (file.size < 1024) {
            showMessage('error', 'File is too small. Please select a valid audio file.');
            return;
        }

        selectedFile = file;
        
        // Enhanced file preview with audio player
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        
        // Show file type
        const fileTypeElement = document.getElementById('fileType');
        if (fileTypeElement) {
            fileTypeElement.textContent = `Type: ${file.type || 'Unknown'} • Format: ${getFileExtension(file.name).toUpperCase()}`;
        }
        
        // Create audio preview
        createAudioPreview(file);
        
        filePreview.style.display = 'block';
        uploadArea.style.display = 'none';
    }

    function createAudioPreview(file) {
        const audioPreview = document.getElementById('audioPreview');
        const audioPlayer = document.getElementById('audioPlayer');
        const audioSource = document.getElementById('audioSource');
        const audioInfo = document.getElementById('audioInfo');
        
        if (audioPreview && audioPlayer && audioSource) {
            // Create object URL for audio preview
            const audioURL = URL.createObjectURL(file);
            audioSource.src = audioURL;
            audioSource.type = file.type;
            audioPlayer.load();
            
            // Show audio preview
            audioPreview.style.display = 'block';
            
            // Update audio info when metadata loads
            audioPlayer.addEventListener('loadedmetadata', function() {
                const duration = Math.round(audioPlayer.duration);
                const minutes = Math.floor(duration / 60);
                const seconds = duration % 60;
                audioInfo.innerHTML = `
                    <small class="text-success">
                        <i class="fas fa-info-circle me-1"></i>
                        Duration: ${minutes}:${seconds.toString().padStart(2, '0')} • 
                        Ready for AI processing
                    </small>
                `;
            });
            
            // Cleanup URL when done
            audioPlayer.addEventListener('ended', function() {
                URL.revokeObjectURL(audioURL);
            });
        }
    }

    function getFileExtension(filename) {
        return filename.slice((filename.lastIndexOf(".") - 1 >>> 0) + 2);
    }

    function resetUpload() {
        selectedFile = null;
        fileInput.value = '';
        filePreview.style.display = 'none';
        uploadArea.style.display = 'block';
        uploadProgress.style.display = 'none';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Upload button click
    uploadBtn.addEventListener('click', function() {
        if (!selectedFile) {
            showMessage('error', 'Please select a file first.');
            return;
        }

        // Show progress
        uploadProgress.style.display = 'block';
        filePreview.style.display = 'none';
        
        // Create FormData for AJAX upload
        const formData = new FormData();
        formData.append('action', 'upload_file');
        formData.append('audio_file', selectedFile);

        // Simulate progress
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 25;
            if (progress > 100) progress = 100;
            progressFill.style.width = progress + '%';
            
            if (progress === 100) {
                clearInterval(interval);
            }
        }, 300);

        // AJAX upload
        fetch('upload.php', {  // Fixed: Point to upload.php specifically
            method: 'POST',
            body: formData
        })
        .then(response => {
            return response.text();
        })
        .then(rawResponse => {
            // Try to parse as JSON
            try {
                const data = JSON.parse(rawResponse);
                
                clearInterval(interval);
                progressFill.style.width = '100%';
                
                setTimeout(() => {
                    uploadProgress.style.display = 'none';
                    
                    if (data.success) {
                        if (data.already_exists) {
                            showMessage('success', data.message + ' File is ready for processing.');
                        } else {
                            showMessage('success', data.message + ' File is ready for processing.');
                        }
                        
                        // Reload page to update session
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showMessage('error', data.message);
                        filePreview.style.display = 'block';
                    }
                }, 800);
                
            } catch (parseError) {
                // Show error message
                clearInterval(interval);
                uploadProgress.style.display = 'none';
                showMessage('error', 'Upload failed: Server response error. Please try again.');
                filePreview.style.display = 'block';
            }
        })
        .catch(error => {
            clearInterval(interval);
            uploadProgress.style.display = 'none';
            showMessage('error', 'Upload failed: Network error. Please try again.');
            filePreview.style.display = 'block';
        });
    });

    function showMessage(type, message) {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.upload-success, .upload-error');
        existingMessages.forEach(msg => msg.remove());

        const messageDiv = document.createElement('div');
        messageDiv.className = `upload-${type}`;
        messageDiv.innerHTML = `
            <div class="${type}-content">
                <div class="${type}-icon">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                </div>
                <div class="${type}-text">
                    <h4>${type === 'success' ? '✅ ' : '❌ '}${type === 'success' ? 'Success' : 'Error'}</h4>
                    <p>${message}</p>
                </div>
            </div>
        `;
        
        // Insert after upload container
        uploadArea.parentNode.insertBefore(messageDiv, uploadArea.nextSibling);
        
        // Remove message after 8 seconds
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 8000);
    }
});
</script>
<?php
}
?>
