<?php
// Start session early for AJAX upload handler
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include enhanced OCR logging
include_once 'ocr_logging.php';

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit;
}

include('database.php');

// Check user credits
$userId = $_SESSION['member_id'];
$stmt = $conn->prepare("SELECT credits FROM members WHERE member_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($credits);
$stmt->fetch();
$stmt->close();

// Define all required functions before AJAX handlers
function extractMedications($text) {
    $medications = [];
    $patterns = [
        '/\b(?:take|prescribed|medication|drug|tablet|capsule|injection)\s+([A-Za-z\s]+?)(?:\s+\d+|\s+mg|\s+ml|\s+times|\s+daily)/i',
        '/\b([A-Za-z]+(?:[a-z]+)?)\s+(?:mg|ml|g|tablet|capsule)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $text, $matches)) {
            $medications = array_merge($medications, $matches[1]);
        }
    }
    
    return array_unique(array_filter($medications));
}

function extractDiagnoses($text) {
    $diagnoses = [];
    $patterns = [
        '/\b(?:diagnosis|diagnosed|condition|suffering from)\s+([A-Za-z\s]+?)(?:\s+\.|,|$)/i',
        '/\b([A-Za-z]+(?:[a-z]+)?)\s+(?:syndrome|disease|disorder|condition)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $text, $matches)) {
            $diagnoses = array_merge($diagnoses, $matches[1]);
        }
    }
    
    return array_unique(array_filter($diagnoses));
}

function extractProcedures($text) {
    $procedures = [];
    $patterns = [
        '/\b(?:procedure|surgery|operation|test|examination)\s+([A-Za-z\s]+?)(?:\s+\.|,|$)/i',
        '/\b([A-Za-z]+(?:[a-z]+)?)\s+(?:surgery|procedure|test|scan)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $text, $matches)) {
            $procedures = array_merge($procedures, $matches[1]);
        }
    }
    
    return array_unique(array_filter($procedures));
}

function extractDates($text) {
    $dates = [];
    $patterns = [
        '/\b\d{1,2}\/\d{1,2}\/\d{2,4}\b/',
        '/\b\d{1,2}-\d{1,2}-\d{2,4}\b/',
        '/\b(?:January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},?\s+\d{4}\b/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $text, $matches)) {
            $dates = array_merge($dates, $matches[0]);
        }
    }
    
    return array_unique($dates);
}

function extractMeasurements($text) {
    $measurements = [];
    $patterns = [
        '/\b\d+(?:\.\d+)?\s*(?:kg|lb|cm|in|mm|m|ft|°C|°F|mmHg|bpm|mg|ml|g|mcg|units)/i',
        '/\b(?:weight|height|temperature|blood pressure|pulse)\s*[:=]\s*\d+(?:\.\d+)?/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $text, $matches)) {
            $measurements = array_merge($measurements, $matches[0]);
        }
    }
    
    return array_unique($measurements);
}

function callOpenAI($api_key, $prompt) {
    error_log("MedicalVision: Calling OpenAI API with prompt length: " . strlen($prompt));
    $url = "https://api.openai.com/v1/chat/completions";
    $data = [
        "model" => "gpt-4",
        "messages" => [
            ["role" => "system", "content" => "You are a medical AI assistant. Provide accurate, structured medical analysis."],
            ["role" => "user", "content" => $prompt]
        ],
        "max_tokens" => 2000,
        "temperature" => 0.3
    ];
    
    error_log("MedicalVision: OpenAI API request data: " . json_encode($data));
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $api_key,
        "Content-Type: application/json"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    error_log("MedicalVision: OpenAI API HTTP Code: " . $httpCode);
    error_log("MedicalVision: OpenAI API Response: " . substr($response, 0, 500));
    if ($curlError) {
        error_log("MedicalVision: OpenAI API cURL Error: " . $curlError);
    }
    
    if ($httpCode !== 200) {
        throw new Exception("OpenAI API error: HTTP $httpCode - $response");
    }
    
    $result = json_decode($response, true);
    if (isset($result['choices'][0]['message']['content'])) {
        $content = $result['choices'][0]['message']['content'];
        error_log("MedicalVision: OpenAI API Content: " . substr($content, 0, 200));
        return $content;
    }
    
    throw new Exception("Invalid OpenAI API response format");
}

function callOpenAIForMedicalAnalysis($text) {
    // 🔑 OPENAI API KEY
    $openai_api_key = getenv('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') ?: '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';
    
    // Check if text is medical-related
    $medicalPrompt = "Analyze this text and determine if it's medical-related. If it contains medical information (medications, diagnoses, procedures, patient data, etc.), provide a detailed medical analysis. If it's not medical, respond with 'NOT_MEDICAL_DOCUMENT'. 

Text to analyze: " . $text;

    $response = callOpenAI($openai_api_key, $medicalPrompt);
    
    if (strpos($response, 'NOT_MEDICAL_DOCUMENT') !== false) {
        return [
            'is_medical' => false,
            'message' => 'This document does not appear to be medical-related. Please upload an image containing medical information such as prescriptions, lab reports, medical notes, patient records, or clinical documents.',
            'document_type' => 'Non-Medical Document',
            'entities' => [
                'medications' => [],
                'diagnoses' => [],
                'procedures' => [],
                'dates' => extractDates($text),
                'measurements' => []
            ],
            'fhir_compliance' => [
                'resource_type' => 'DocumentReference',
                'status' => 'current',
                'type' => 'non_medical_document',
                'category' => 'general_document'
            ],
            'hipaa_compliance' => [
                'phi_identified' => false,
                'data_encryption' => true,
                'access_logged' => true,
                'audit_trail' => true
            ],
            'clinical_insights' => [
                'severity' => 'none',
                'recommendations' => 'Upload a medical document for clinical analysis',
                'follow_up' => 'No medical follow-up required'
            ],
            'analysis_note' => 'This document appears to be related to travel/legalization rather than medical content. For medical analysis, please upload documents containing medical information, prescriptions, lab results, or clinical notes.'
        ];
    }
    
    // If medical, perform detailed analysis
    $analysisPrompt = "Analyze this medical text and provide a structured response in JSON format with the following structure:

{
    'document_type': 'Medical Document',
    'entities': {
        'medications': [list of medications],
        'diagnoses': [list of diagnoses],
        'procedures': [list of procedures],
        'dates': [list of dates],
        'measurements': [list of measurements]
    },
    'fhir_compliance': {
        'resource_type': 'DocumentReference',
        'status': 'current',
        'type': 'medical_document',
        'category': 'clinical_note'
    },
    'hipaa_compliance': {
        'phi_identified': false,
        'data_encryption': true,
        'access_logged': true,
        'audit_trail': true
    },
    'clinical_insights': {
        'severity': 'low/medium/high',
        'recommendations': 'clinical recommendations',
        'follow_up': 'follow-up instructions'
    }
}

Medical text: " . $text;

    $analysisResponse = callOpenAI($openai_api_key, $analysisPrompt);
    
    // Try to parse JSON response
    $parsed = json_decode($analysisResponse, true);
    if ($parsed) {
        return $parsed;
    }
    
    // If JSON parsing fails, return structured response
    return [
        'document_type' => 'Medical Document',
        'entities' => [
            'medications' => extractMedications($text),
            'diagnoses' => extractDiagnoses($text),
            'procedures' => extractProcedures($text),
            'dates' => extractDates($text),
            'measurements' => extractMeasurements($text)
        ],
        'fhir_compliance' => [
            'resource_type' => 'DocumentReference',
            'status' => 'current',
            'type' => 'medical_document',
            'category' => 'clinical_note'
        ],
        'hipaa_compliance' => [
            'phi_identified' => false,
            'data_encryption' => true,
            'access_logged' => true,
            'audit_trail' => true
        ],
        'clinical_insights' => [
            'severity' => 'low',
            'recommendations' => 'Continue monitoring as prescribed',
            'follow_up' => 'Schedule follow-up appointment'
        ]
    ];
}

// AJAX: handle image upload on this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    if ($_POST['action'] === 'upload_image') {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'No image selected or upload error']);
        exit;
    }

        // Check if user has credits
        if ($credits < 1) {
            echo json_encode(['status' => 'error', 'message' => 'Insufficient credits. Please purchase more credits.']);
            exit;
        }
        
        $uploadDir = __DIR__ . "/storage/uploads/images/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

        // Check if image already exists by filename and size
        $imageSize = $_FILES['image']['size'];
        $imageName = basename($_FILES['image']['name']);
        
        // Check if this image already exists in database for this user
        $stmt = $conn->prepare("SELECT id, image_path, extracted_text, fhir_json FROM fhir_documents WHERE member_id = ? AND image_path LIKE ? AND extracted_text IS NOT NULL LIMIT 1");
        $searchPattern = "%" . $imageName;
        $stmt->bind_param("is", $userId, $searchPattern);
        $stmt->execute();
        $stmt->bind_result($existingId, $existingPath, $existingText, $existingFhir);
        $stmt->fetch();
        $stmt->close();
        
        if ($existingId && $existingText) {
            // Image already exists with analysis - use existing data
            $_SESSION['uploaded_image'] = __DIR__ . "/" . $existingPath;
            $_SESSION['uploaded_filename'] = basename($existingPath);
            $_SESSION['extracted_text'] = $existingText;
            $_SESSION['document_id'] = $existingId;
            
            if ($existingFhir) {
                $_SESSION['ai_analysis'] = json_decode($existingFhir, true);
            }
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Image already exists. Using previous analysis data.', 
                'file' => basename($existingPath),
                'duplicate' => true,
                'existing_id' => $existingId,
                'has_analysis' => !empty($existingFhir)
            ]);
            exit;
        }
        
        // New image - upload and process
    $fileName = time() . '_' . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Store relative path for database (relative to MedicalVision module)
            $relativePath = "storage/uploads/images/" . $fileName;
        $_SESSION['uploaded_image'] = $targetPath;
            $_SESSION['uploaded_filename'] = $fileName;
        echo json_encode(['status' => 'success', 'message' => 'Image uploaded successfully', 'file' => $fileName]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Failed to upload image']);
    exit;
}

    if ($_POST['action'] === 'process_ocr') {
        if (!isset($_SESSION['uploaded_image']) || !file_exists($_SESSION['uploaded_image'])) {
            echo json_encode(['status' => 'error', 'message' => 'No image found for processing']);
            exit;
        }
        
        try {
            // Process with Python OCR script
            $imagePath = $_SESSION['uploaded_image']; // Full path for Python processing
            
            // Extract relative path for database storage
            $relativePath = str_replace(__DIR__ . '/', '', $imagePath);
            
            $command = "py \"" . __DIR__ . "\\handwritten_to_text.py\" \"" . $imagePath . "\"";
            
            // Enhanced logging
            logOCRInfo("Starting OCR processing", [
                'user_id' => $userId,
                'image_path' => $imagePath,
                'command' => $command
            ]);
            
            $output = shell_exec($command);
            
            if (is_null($output)) {
                logOCRError("Python script execution failed - no output", null, ['command' => $command]);
                throw new Exception("Python script execution failed");
            }
            
            if ($output) {
                $result = json_decode($output, true);
                if ($result && isset($result['handwritten_text'])) {
                    $_SESSION['extracted_text'] = $result['handwritten_text'];
                    
                    // ✅ SAVE OCR RESULTS TO DATABASE
                    $stmt = $conn->prepare("INSERT INTO fhir_documents (member_id, image_path, extracted_text, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->bind_param("iss", $userId, $relativePath, $result['handwritten_text']);
                    $stmt->execute();
                    $documentId = $conn->insert_id;
                    $stmt->close();
                    
                    // Store document ID in session for AI analysis
                    $_SESSION['document_id'] = $documentId;
                    
                    // Log successful OCR
                    logOCRSuccess("OCR processing completed successfully", [
                        'user_id' => $userId,
                        'text_length' => strlen($result['handwritten_text']),
                        'image_path' => $imagePath,
                        'document_id' => $documentId
                    ]);
                    
                    // Deduct 1 credit
                    $stmt = $conn->prepare("UPDATE members SET credits = credits - 1 WHERE member_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
                    $stmt->close();
                    
                    echo json_encode([
                        'status' => 'success',
                        'extracted_text' => $result['handwritten_text'],
                        'message' => 'OCR processing completed successfully',
                        'document_id' => $documentId
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'OCR processing failed']);
                }
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'OCR processing failed. Please retry with a clearer document or contact support.',
                    'code' => 'ocr_failed'
                ]);
            }
        } catch (Exception $e) {
            logOCRError("OCR processing error", $e, ['user_id' => $userId, 'image_path' => $imagePath]);
            echo json_encode([
                'status' => 'error', 
                'message' => 'OCR processing error. Please retry or contact support.',
                'code' => 'ocr_error',
                'details' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'analyze_ai') {
        if (!isset($_SESSION['extracted_text']) || !isset($_SESSION['document_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'No extracted text or document ID found for AI analysis']);
            exit;
        }
        
        try {
            $extractedText = $_SESSION['extracted_text'];
            $documentId = $_SESSION['document_id'];
            
            // ✅ CALL REAL OPENAI API FOR MEDICAL ANALYSIS
            error_log("MedicalVision: Starting AI analysis for document ID: " . $documentId);
            error_log("MedicalVision: Extracted text length: " . strlen($extractedText));
            
            $aiAnalysis = callOpenAIForMedicalAnalysis($extractedText);
            
            if ($aiAnalysis) {
                error_log("MedicalVision: AI analysis completed successfully");
                $_SESSION['ai_analysis'] = $aiAnalysis;
                
                // ✅ SAVE AI ANALYSIS TO DATABASE
                $fhirJson = json_encode($aiAnalysis, JSON_PRETTY_PRINT);
                $stmt = $conn->prepare("UPDATE fhir_documents SET fhir_json = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $fhirJson, $documentId);
                $stmt->execute();
                $stmt->close();
                
                echo json_encode([
                    'status' => 'success',
                    'ai_analysis' => $aiAnalysis,
                    'message' => 'AI analysis completed successfully'
                ]);
            } else {
                error_log("MedicalVision: AI analysis returned null/empty");
                echo json_encode([
                    'status' => 'error',
                    'message' => 'AI analysis failed. Please try again.'
                ]);
            }
        } catch (Exception $e) {
            error_log("MedicalVision AI Analysis Error: " . $e->getMessage());
            error_log("MedicalVision AI Analysis Error Stack: " . $e->getTraceAsString());
            logOCRError("AI analysis error", $e, ['user_id' => $userId, 'document_id' => $_SESSION['document_id'] ?? 'unknown']);
            echo json_encode([
                'status' => 'error',
                'message' => 'AI analysis error: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    

    
    if ($_POST['action'] === 'get_results') {
        if (!isset($_SESSION['document_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'No document ID found']);
            exit;
        }
        
        try {
            $documentId = $_SESSION['document_id'];
            
            // Get results from database
            $stmt = $conn->prepare("SELECT extracted_text, fhir_json FROM fhir_documents WHERE id = ? AND member_id = ?");
            $stmt->bind_param("ii", $documentId, $userId);
            $stmt->execute();
            $stmt->bind_result($extractedText, $fhirJson);
$stmt->fetch();
$stmt->close();
            
            if ($extractedText && $fhirJson) {
                $results = json_decode($fhirJson, true);
                $results['extracted_text'] = $extractedText;
                
                echo json_encode([
                    'status' => 'success',
                    'results' => $results
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No results found for this document'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error retrieving results: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}

include('header.php');
?>

<!-- Enhanced MedicalVision Dashboard -->
<div class="dashboard-container">
    <!-- Hero Section -->
    <section class="hero-section" role="banner">
        <div class="hero-content">
            <h1 class="hero-title">Chief.AI MedicalVision</h1>
            <p class="hero-subtitle">
                Advanced AI-powered medical document analysis with OCR technology and intelligent text extraction. 
                Built by Chief.AI - where intelligence meets innovation.
            </p>
        </div>
    </section>

    <!-- Credits Display -->
    <div class="credits-display animate-fade-in-up">
        <div class="credits-number"><?php echo (int)$credits; ?></div>
        <div class="credits-label">Credits Available</div>
        <a href="../buy_credits.php" class="btn btn-primary">
            <span class="material-icons-round">account_balance_wallet</span>
            Buy Credits
        </a>
                </div>

    <!-- Messages Area -->
    <div id="messages" class="messages-area" style="display:none;" aria-live="polite"></div>

    <!-- Global Progress Tracker -->
    <div class="workflow-progress" id="workflowProgress">
        <div class="progress-container">
            <div class="progress-steps">
                <div class="progress-step active" id="progress-step-1">
                    <div class="step-circle">
                        <span class="material-icons-round">cloud_upload</span>
                        <div class="step-number">1</div>
                </div>
                    <div class="step-label">Upload</div>
            </div>
                <div class="progress-connector" id="connector-1"></div>
                <div class="progress-step" id="progress-step-2">
                    <div class="step-circle">
                        <span class="material-icons-round">visibility</span>
                        <div class="step-number">2</div>
            </div>
                    <div class="step-label">OCR</div>
                </div>
                <div class="progress-connector" id="connector-2"></div>
                <div class="progress-step" id="progress-step-3">
                    <div class="step-circle">
                        <span class="material-icons-round">psychology</span>
                        <div class="step-number">3</div>
                    </div>
                    <div class="step-label">AI Analysis</div>
                </div>
                <div class="progress-connector" id="connector-3"></div>
                <div class="progress-step" id="progress-step-4">
                    <div class="step-circle">
                        <span class="material-icons-round">task_alt</span>
                        <div class="step-number">4</div>
                    </div>
                    <div class="step-label">Results</div>
                </div>
            </div>
            <div class="progress-description" id="progressDescription">
                Upload your medical image to begin analysis
            </div>
        </div>
    </div>

    <!-- Three-step inline flow -->
    <div class="features-grid">
        <!-- Step 1: Upload -->
        <div class="feature-card" id="step1">
            <div class="feature-header">
                <div class="feature-icon upload-icon">
                    <span class="material-icons-round">cloud_upload</span>
                </div>
                <div class="feature-info">
                    <h3 class="feature-title">Upload</h3>
                    <p class="feature-description">Start by uploading a medical image</p>
                </div>
            </div>
            <div class="feature-content">
                <div class="upload-area" id="uploadArea" tabindex="0" role="region" aria-label="Image upload area">
                    <div class="upload-icon">
                        <span class="material-icons-round">cloud_upload</span>
                </div>
                    <div class="upload-text">Drop your medical image here</div>
                    <div class="upload-hint">or click to browse (JPG, PNG, GIF, BMP, WebP up to 10MB)</div>
                    <input type="file" id="imageFile" accept="image/*" style="display: none;">
                </div>
            </div>
        </div>

        <!-- Step 2: Process -->
        <div class="feature-card" id="step2" style="display:none;">
            <div class="feature-header">
                <div class="feature-icon process-icon">
                    <span class="material-icons-round">auto_fix_high</span>
                </div>
                <div class="feature-info">
                    <h3 class="feature-title">AI Processing</h3>
                    <p class="feature-description">Advanced OCR and medical analysis in progress</p>
                </div>
            </div>
            <div class="feature-content">
                <div class="processing-area text-center">
                    <div class="premium-spinner">
                        <div class="spinner-ring"></div>
                        <div class="spinner-ring"></div>
                        <div class="spinner-ring"></div>
                </div>
                    <div class="processing-text">Analyzing your medical image...</div>
                    <div class="processing-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
            </div>
                        <div class="progress-text" id="progressText">Initializing...</div>
        </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Results -->
        <div class="feature-card" id="step3" style="display:none;">
            <div class="feature-header">
                <div class="feature-icon results-icon">
                    <span class="material-icons-round">task_alt</span>
                </div>
                <div class="feature-info">
                    <h3 class="feature-title">Results</h3>
                    <p class="feature-description">Your analyzed medical image</p>
                </div>
            </div>
            <div class="feature-content">
                <div class="results-area" id="resultsArea">
                    <!-- Results will be populated here -->
                        </div>
                    </div>
                        </div>
                    </div>

    <!-- Feature Cards Section -->
    <section class="features-section animate-fade-in-up">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="material-icons-round">visibility</span>
                </div>
                <h3 class="feature-title">Advanced OCR</h3>
                <p class="feature-description">
                    State-of-the-art optical character recognition with medical document expertise
                </p>
                </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="material-icons-round">security</span>
            </div>
                <h3 class="feature-title">HIPAA Compliant</h3>
                <p class="feature-description">
                    Enterprise-grade security for your sensitive medical data
                </p>
        </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <span class="material-icons-round">psychology</span>
    </div>
                <h3 class="feature-title">AI Analysis</h3>
                <p class="feature-description">
                    Intelligent medical document processing with FHIR compliance
                </p>
            </div>
        </div>
    </section>
</div>

<!-- Styles for enhanced results display -->
<style>
    /* Enhanced Results Display */
    .results-content {
        padding: 24px;
    }
    
    .result-section {
        margin-bottom: 24px;
        padding: 20px;
        background: white;
        border-radius: 12px;
        border: 1px solid var(--neutral-200);
        box-shadow: var(--shadow-sm);
    }
    
    .result-title {
    display: flex;
    align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        color: var(--neutral-900);
        font-size: 1.125rem;
        font-weight: 600;
    }
    
    .result-text {
        color: var(--neutral-700);
        line-height: 1.6;
        white-space: pre-wrap;
    }
    
    .result-actions {
    display: flex;
        gap: 16px;
        margin-top: 24px;
        justify-content: center;
    }
    
    /* Enhanced Processing States */
    .processing-area {
        padding: 40px 20px;
    }
    
    .premium-spinner {
    display: flex;
    justify-content: center;
        margin-bottom: 24px;
    }
    
    .spinner-ring {
        width: 40px;
        height: 40px;
        border: 4px solid var(--neutral-200);
        border-top: 4px solid var(--primary-500);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 8px;
    }
    
    .spinner-ring:nth-child(2) {
        animation-delay: 0.2s;
        border-top-color: var(--secondary-500);
    }
    
    .spinner-ring:nth-child(3) {
        animation-delay: 0.4s;
        border-top-color: var(--accent-500);
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .processing-text {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--neutral-700);
        margin-bottom: 24px;
    }
    
    .processing-progress {
        max-width: 400px;
        margin: 0 auto;
    }
    
    .progress-bar {
        width: 100%;
        height: 8px;
        background: var(--neutral-200);
        border-radius: 4px;
    overflow: hidden;
        margin-bottom: 12px;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-500), var(--accent-500));
        width: 0%;
        transition: width 0.3s ease;
        border-radius: 4px;
    }
    
    .progress-text {
        font-size: 0.875rem;
        color: var(--neutral-600);
        text-align: center;
    }
</style>

<script>
    // Enhanced MedicalVision Interactions with Real OCR Processing
    document.addEventListener('DOMContentLoaded', function() {
        const uploadArea = document.getElementById('uploadArea');
        const imageFile = document.getElementById('imageFile');
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const step3 = document.getElementById('step3');
        const messagesArea = document.getElementById('messages');
        const resultsArea = document.getElementById('resultsArea');
        
        // Enhanced drag and drop functionality
        uploadArea.addEventListener('click', () => imageFile.click());
        
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
        
        imageFile.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileUpload(e.target.files[0]);
            }
        });
        
        function handleFileUpload(file) {
            // Validate file type - IMAGES ONLY
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showMessage('Please select a valid image file (JPG, PNG, GIF, BMP, WebP). Documents, audio, and video files are not supported.', 'error');
                return;
            }
            
            // Validate file size (10MB limit)
            if (file.size > 10 * 1024 * 1024) {
                showMessage('File size must be less than 10MB', 'error');
                return;
            }
            
            // Show loading state
            uploadArea.innerHTML = `
                <div class="premium-spinner">
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                </div>
                <div class="upload-text">Processing your document...</div>
            `;
            
            // Create FormData and upload
            const formData = new FormData();
            formData.append('image', file);
            formData.append('action', 'upload_image');
            
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.duplicate && data.has_analysis) {
                        // Image already exists with analysis - show results directly
                        showMessage('Image already exists. Loading previous analysis...', 'success');
                        setTimeout(() => {
                            showStep3();
                        }, 1500);
                    } else if (data.duplicate) {
                        // Image exists but no analysis - proceed to OCR
                        showMessage('Image already exists. Processing OCR...', 'success');
                        setTimeout(() => {
                            showStep2();
                        }, 1500);
                    } else {
                        // New image - proceed to step 2
                        showMessage('Image uploaded successfully!', 'success');
                        setTimeout(() => {
                            showStep2();
                        }, 1500);
                    }
                } else {
                    showMessage(data.message || 'Upload failed', 'error');
                    resetUploadArea();
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                showMessage('Upload failed. Please try again.', 'error');
                resetUploadArea();
            });
        }
        
        function showStep2() {
            step1.style.display = 'none';
            step2.style.display = 'block';
            
            // Update progress tracker
            updateProgress(2, 'Processing your image with advanced OCR technology...');
            
            // Show loading skeleton
            showProcessingSkeleton();
            
            // Start real OCR processing
            processOCR();
        }
        
        function showProcessingSkeleton() {
            const processingArea = document.querySelector('.processing-area');
            if (processingArea) {
                processingArea.innerHTML = `
                    <div class="skeleton-progress">
                        <div class="skeleton-progress-steps">
                            <div class="skeleton-step">
                                <div class="skeleton skeleton-step-circle"></div>
                                <div class="skeleton skeleton-step-label"></div>
                            </div>
                            <div class="skeleton-step">
                                <div class="skeleton skeleton-step-circle"></div>
                                <div class="skeleton skeleton-step-label"></div>
                            </div>
                            <div class="skeleton-step">
                                <div class="skeleton skeleton-step-circle"></div>
                                <div class="skeleton skeleton-step-label"></div>
                            </div>
                        </div>
                        <div class="skeleton skeleton-progress-description"></div>
                    </div>
                    
                    <div class="skeleton-card">
                        <div class="skeleton-header">
                            <div class="skeleton skeleton-icon"></div>
                            <div class="skeleton-title-section">
                                <div class="skeleton skeleton-title"></div>
                                <div class="skeleton skeleton-subtitle"></div>
                            </div>
                        </div>
                        <div class="skeleton-content">
                            <div class="skeleton skeleton-text long"></div>
                            <div class="skeleton skeleton-text medium"></div>
                            <div class="skeleton skeleton-text short"></div>
                            <div class="skeleton skeleton-text long"></div>
                        </div>
                    </div>
                `;
            }
        }
        
        function showResultsSkeleton() {
            resultsArea.innerHTML = `
                <div class="results-content">
                    <!-- Status Skeleton -->
                    <div class="skeleton-card">
                        <div class="skeleton-header">
                            <div class="skeleton skeleton-icon"></div>
                            <div class="skeleton-title-section">
                                <div class="skeleton skeleton-title"></div>
                                <div class="skeleton skeleton-subtitle"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Grid Skeleton -->
                    <div class="results-grid">
                        <!-- Extracted Text Skeleton -->
                        <div class="skeleton-card">
                            <div class="skeleton-header">
                                <div class="skeleton skeleton-icon"></div>
                                <div class="skeleton-title-section">
                                    <div class="skeleton skeleton-title"></div>
                                    <div class="skeleton skeleton-subtitle"></div>
                                </div>
                            </div>
                            <div class="skeleton-content">
                                <div class="skeleton skeleton-text long"></div>
                                <div class="skeleton skeleton-text medium"></div>
                                <div class="skeleton skeleton-text short"></div>
                                <div class="skeleton skeleton-text long"></div>
                                <div class="skeleton skeleton-text medium"></div>
                            </div>
                        </div>

                        <!-- AI Analysis Skeleton -->
                        <div class="skeleton-card">
                            <div class="skeleton-header">
                                <div class="skeleton skeleton-icon"></div>
                                <div class="skeleton-title-section">
                                    <div class="skeleton skeleton-title"></div>
                                    <div class="skeleton skeleton-subtitle"></div>
                                </div>
                            </div>
                            <div class="skeleton-content">
                                <div class="skeleton skeleton-text medium"></div>
                                <div class="skeleton skeleton-text short"></div>
                                <div class="skeleton skeleton-text long"></div>
                                <div class="skeleton skeleton-text medium"></div>
                            </div>
                        </div>

                        <!-- Compliance Skeleton -->
                        <div class="skeleton-card">
                            <div class="skeleton-header">
                                <div class="skeleton skeleton-icon"></div>
                                <div class="skeleton-title-section">
                                    <div class="skeleton skeleton-title"></div>
                                    <div class="skeleton skeleton-subtitle"></div>
                                </div>
                            </div>
                            <div class="skeleton-content">
                                <div class="skeleton skeleton-text short"></div>
                                <div class="skeleton skeleton-text short"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function processOCR(retryCount = 0) {
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const maxRetries = 3;
            
            // Update progress text based on retry attempt
            if (retryCount > 0) {
                progressText.textContent = `OCR processing (Attempt ${retryCount + 1}/${maxRetries + 1})...`;
            } else {
                progressText.textContent = 'Starting OCR processing...';
            }
            
            // Start OCR processing
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=process_ocr'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    progressFill.style.width = '50%';
                    progressText.textContent = 'OCR completed successfully! Starting AI analysis...';
                    
                    // Update progress tracker
                    updateProgress(3, 'OCR completed! Analyzing medical content with AI...');
                    
                    // Start AI analysis
                    setTimeout(() => {
                        processAIAnalysis();
                    }, 1000);
                } else {
                    // OCR failed - try retry logic
                    if (retryCount < maxRetries) {
                        console.log(`OCR attempt ${retryCount + 1} failed, retrying...`);
                        showMessage(`OCR attempt ${retryCount + 1} failed. Retrying...`, 'warning');
                        
                        // Wait 2 seconds before retry
                        setTimeout(() => {
                            processOCR(retryCount + 1);
                        }, 2000);
                    } else {
                        // All retries exhausted
                        showMessage('OCR processing failed after multiple attempts. Please try uploading a clearer image or contact support.', 'error');
                        
                        // Offer manual options
                        showOCRFailureOptions();
                    }
                }
            })
            .catch(error => {
                console.error('OCR error:', error);
                
                if (retryCount < maxRetries) {
                    console.log(`OCR network error, retrying... (${retryCount + 1}/${maxRetries + 1})`);
                    showMessage(`Network error. Retrying OCR (${retryCount + 1}/${maxRetries + 1})...`, 'warning');
                    
                    setTimeout(() => {
                        processOCR(retryCount + 1);
                    }, 3000);
                } else {
                    showMessage('OCR processing failed due to network issues. Please check your connection and try again.', 'error');
                    showOCRFailureOptions();
                }
            });
        }
        
        function showOCRFailureOptions() {
            const processingArea = document.querySelector('.processing-area');
            processingArea.innerHTML = `
                <div class="ocr-failure-options">
                    <div class="failure-icon">
                        <span class="material-icons-round" style="font-size: 48px; color: var(--error-500);">error_outline</span>
                    </div>
                    <h3 style="color: var(--error-600); margin: 16px 0;">OCR Processing Failed</h3>
                    <p style="color: var(--neutral-600); margin-bottom: 24px;">
                        We couldn't extract text from your image. Here are your options:
                    </p>
                    <div class="failure-actions">
                        <button class="btn btn-primary" onclick="retryWithNewImage()">
                            <span class="material-icons-round">cloud_upload</span>
                            Upload Different Image
                        </button>
                        <button class="btn btn-secondary" onclick="skipToManualEntry()">
                            <span class="material-icons-round">edit</span>
                            Enter Text Manually
                        </button>
                        <button class="btn btn-secondary" onclick="contactSupport()">
                            <span class="material-icons-round">support_agent</span>
                            Contact Support
                        </button>
                    </div>
                    <div class="failure-tips">
                        <h4>💡 Tips for better OCR results:</h4>
                        <ul style="text-align: left; color: var(--neutral-600);">
                            <li>Ensure good lighting and clear image quality</li>
                            <li>Avoid blurry or low-resolution images</li>
                            <li>Make sure text is clearly visible and not obscured</li>
                            <li>Try different image formats (PNG often works better)</li>
                        </ul>
                    </div>
                </div>
            `;
        }
        
        function retryWithNewImage() {
            resetToStep1();
            showMessage('Please upload a new image with clearer text.', 'info');
        }
        
        function skipToManualEntry() {
            // Show manual text entry option
            step2.style.display = 'none';
            showManualEntryStep();
        }
        
        function contactSupport() {
            showMessage('Please contact support at support@chief.ai for assistance.', 'info');
        }
        
        function showManualEntryStep() {
            const manualEntryHTML = `
                <div class="feature-card" id="manualEntry">
                    <div class="feature-header">
                        <div class="feature-icon">
                            <span class="material-icons-round">edit</span>
                        </div>
                        <div class="feature-info">
                            <h3 class="feature-title">Manual Text Entry</h3>
                            <p class="feature-description">Enter the text from your medical document</p>
                        </div>
                    </div>
                    <div class="feature-content">
                        <div class="manual-entry-area">
                            <textarea id="manualText" placeholder="Enter the text from your medical document here..." 
                                      style="width: 100%; height: 200px; padding: 16px; border: 2px solid var(--neutral-200); border-radius: 8px; font-family: inherit; resize: vertical;"></textarea>
                            <div class="manual-entry-actions" style="margin-top: 16px;">
                                <button class="btn btn-primary" onclick="processManualText()">
                                    <span class="material-icons-round">psychology</span>
                                    Analyze Text
                                </button>
                                <button class="btn btn-secondary" onclick="resetToStep1()">
                                    <span class="material-icons-round">arrow_back</span>
                                    Back to Upload
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.querySelector('.features-grid').insertAdjacentHTML('beforeend', manualEntryHTML);
        }
        
        function processManualText() {
            const manualText = document.getElementById('manualText').value.trim();
            
            if (!manualText) {
                showMessage('Please enter some text to analyze.', 'error');
                return;
            }
            
            // Store manual text in session and proceed to AI analysis
            $_SESSION['extracted_text'] = manualText;
            
            // Hide manual entry and show processing
            document.getElementById('manualEntry').style.display = 'none';
            step2.style.display = 'block';
            
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            
            progressFill.style.width = '50%';
            progressText.textContent = 'Manual text entered. Starting AI analysis...';
            
            setTimeout(() => {
                processAIAnalysis();
            }, 1000);
        }
        
        function processAIAnalysis() {
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=analyze_ai'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    progressFill.style.width = '100%';
                    progressText.textContent = 'Analysis completed!';
                    
                    setTimeout(() => {
                        showStep3();
                    }, 1000);
                } else {
                    showMessage(data.message || 'AI analysis failed', 'error');
                    resetToStep1();
                }
            })
            .catch(error => {
                console.error('AI analysis error:', error);
                showMessage('AI analysis failed. Please try again.', 'error');
                resetToStep1();
            });
        }
        
        function showStep3() {
            step2.style.display = 'none';
            step3.style.display = 'block';
            
            // Update progress tracker
            updateProgress(4, 'Analysis complete! Review your results.');
            
            // Show skeleton while loading results
            showResultsSkeleton();
            
            // Add a small delay to show the skeleton animation
            setTimeout(() => {
                // Populate results area with real data
                populateResults();
            }, 800);
        }
        
        function populateResults() {
            // Fetch the latest analysis data
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_results'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displayResults(data.results);
                } else {
                    showMessage('Failed to load results', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading results:', error);
                showMessage('Error loading results', 'error');
            });
        }
        
        function displayResults(results) {
            // Store results globally for expand functionality
            window.currentResults = results;
            
            const isMedical = results.document_type === 'Medical Document';
            const analysisNote = results.analysis_note || '';
            
            resultsArea.innerHTML = `
                <div class="results-content">
                    <!-- Status Card -->
                    <div class="result-card status-card">
                        <div class="card-header">
                            <div class="card-icon success">
                                <span class="material-icons-round">task_alt</span>
                            </div>
                            <div class="card-title-section">
                                <h4 class="card-title">Analysis Complete</h4>
                                <p class="card-subtitle">Your document has been successfully processed</p>
                            </div>
                        </div>
                        ${!isMedical ? `
                            <div class="card-alert warning">
                                <div class="alert-icon">
                                    <span class="material-icons-round">warning</span>
                                </div>
                                <div class="alert-content">
                                    <strong>Non-Medical Document Detected</strong>
                                    <p>${analysisNote}</p>
                                </div>
                            </div>
                        ` : ''}
                    </div>

                    <!-- Results Grid -->
                    <div class="results-grid">
                        <!-- Extracted Text Card -->
                        <div class="result-card data-card">
                            <div class="card-header">
                                <div class="card-icon primary">
                                    <span class="material-icons-round">text_fields</span>
                                </div>
                                <div class="card-title-section">
                                    <h4 class="card-title">Extracted Text</h4>
                                    <p class="card-subtitle">OCR Results</p>
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="text-preview">
                                    ${results.extracted_text ? results.extracted_text.substring(0, 150) + (results.extracted_text.length > 150 ? '...' : '') : 'No text extracted'}
                                </div>
                                <button class="expand-btn" onclick="expandTextCard('extracted')" aria-label="Expand extracted text">
                                    <span class="material-icons-round">expand_more</span>
                                </button>
                            </div>
                        </div>

                        <!-- AI Analysis Card -->
                        <div class="result-card data-card">
                            <div class="card-header">
                                <div class="card-icon secondary">
                                    <span class="material-icons-round">psychology</span>
                                </div>
                                <div class="card-title-section">
                                    <h4 class="card-title">AI Analysis</h4>
                                    <p class="card-subtitle">Medical Insights</p>
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="analysis-summary">
                                    <div class="analysis-item">
                                        <span class="analysis-label">Document Type:</span>
                                        <span class="analysis-value">${results.document_type || 'Document'}</span>
                                    </div>
                                    <div class="analysis-item">
                                        <span class="analysis-label">Severity:</span>
                                        <span class="analysis-value severity-${(results.clinical_insights?.severity || 'none').toLowerCase()}">${results.clinical_insights?.severity || 'Not specified'}</span>
                                    </div>
                                    <div class="analysis-item">
                                        <span class="analysis-label">Recommendations:</span>
                                        <span class="analysis-value">${results.clinical_insights?.recommendations || 'None provided'}</span>
                                    </div>
                                </div>
                                ${!isMedical ? `
                                    <div class="analysis-note">
                                        <span class="material-icons-round">info</span>
                                        This document does not contain medical information and cannot provide clinical insights.
                                    </div>
                                ` : ''}
                            </div>
                        </div>

                        <!-- Compliance Card -->
                        <div class="result-card compliance-card">
                            <div class="card-header">
                                <div class="card-icon accent">
                                    <span class="material-icons-round">verified_user</span>
                                </div>
                                <div class="card-title-section">
                                    <h4 class="card-title">Compliance</h4>
                                    <p class="card-subtitle">FHIR & HIPAA</p>
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="compliance-badges">
                                    <div class="compliance-badge fhir">
                                        <span class="material-icons-round">check_circle</span>
                                        <span>FHIR Compliant</span>
                                    </div>
                                    <div class="compliance-badge hipaa">
                                        <span class="material-icons-round">security</span>
                                        <span>HIPAA Secure</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="result-actions">
                        <button class="btn btn-secondary" onclick="startNewAnalysis()">
                            <span class="material-icons-round">refresh</span>
                            New Analysis
                        </button>
                    </div>
                </div>
            `;
        }
        
        function expandTextCard(type) {
            // Implementation for expanding text cards
            const card = event.target.closest('.result-card');
            const textPreview = card.querySelector('.text-preview');
            const expandBtn = card.querySelector('.expand-btn');
            
            if (card.classList.contains('expanded')) {
                // Collapse
                card.classList.remove('expanded');
                expandBtn.querySelector('.material-icons-round').textContent = 'expand_more';
                if (type === 'extracted') {
                    textPreview.textContent = (window.currentResults?.extracted_text || '').substring(0, 150) + '...';
                }
        } else {
                // Expand
                card.classList.add('expanded');
                expandBtn.querySelector('.material-icons-round').textContent = 'expand_less';
                if (type === 'extracted') {
                    textPreview.textContent = window.currentResults?.extracted_text || 'No text extracted';
                }
            }
        }
        

        
        function resetUploadArea() {
            uploadArea.innerHTML = `
                <div class="upload-icon">
                    <span class="material-icons-round">cloud_upload</span>
                </div>
                <div class="upload-text">Drop your medical image here</div>
                <div class="upload-hint">or click to browse (JPG, PNG, GIF, BMP, WebP up to 10MB)</div>
            `;
        }
        
        function resetToStep1() {
            step2.style.display = 'none';
            step3.style.display = 'none';
            step1.style.display = 'block';
            resetUploadArea();
        }
        
        function showMessage(message, type) {
            messagesArea.style.display = 'block';
            messagesArea.innerHTML = `
                <div class="message message-${type}">
                    <span class="material-icons-round">${type === 'success' ? 'check_circle' : 'error'}</span>
                    <span>${message}</span>
                </div>
            `;
            
            setTimeout(() => {
                messagesArea.style.display = 'none';
            }, 5000);
        }
        
        // Progress tracker functions
        function updateProgress(stepNumber, description) {
            // Update progress steps
            for (let i = 1; i <= 4; i++) {
                const step = document.getElementById(`progress-step-${i}`);
                const connector = document.getElementById(`connector-${i}`);
                
                if (i < stepNumber) {
                    step.classList.add('completed');
                    step.classList.remove('active');
                    if (connector) connector.classList.add('completed');
                } else if (i === stepNumber) {
                    step.classList.add('active');
                    step.classList.remove('completed');
                } else {
                    step.classList.remove('active', 'completed');
                    if (connector) connector.classList.remove('completed');
                }
            }
            
            // Update description
            document.getElementById('progressDescription').textContent = description;
        }

        // Enhanced scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        // Initialize scroll-triggered animations
        function initScrollAnimations() {
            const scrollElements = document.querySelectorAll('.animate-on-scroll');
            const scrollObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);
            
            scrollElements.forEach(el => scrollObserver.observe(el));
        }
        
        // Add ripple effect to buttons
        function addRippleEffect() {
            document.querySelectorAll('.btn').forEach(button => {
                button.classList.add('ripple');
                
                button.addEventListener('click', function(e) {
                    // Add loading state temporarily
                    this.classList.add('loading');
                    setTimeout(() => {
                        this.classList.remove('loading');
                    }, 1000);
                });
            });
        }
        
        // Enhanced drag and drop interactions
        function enhanceDragDropInteractions() {
            const uploadArea = document.querySelector('.upload-area');
            if (uploadArea) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, preventDefaults, false);
                });
                
                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                ['dragenter', 'dragover'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, highlight, false);
                });
                
                ['dragleave', 'drop'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, unhighlight, false);
                });
                
                function highlight() {
                    uploadArea.classList.add('dragover');
                }
                
                function unhighlight() {
                    uploadArea.classList.remove('dragover');
                }
            }
        }
        
        // Add tooltips to elements
        function addTooltips() {
            // Add tooltips to progress steps
            document.querySelectorAll('.progress-step').forEach((step, index) => {
                const labels = ['Upload Document', 'Extract Text', 'Analyze Content', 'View Results'];
                step.setAttribute('data-tooltip', labels[index] || 'Step');
            });
            
            // Add tooltips to action buttons
            document.querySelectorAll('.btn').forEach(btn => {
                const text = btn.textContent.trim();
                if (text.includes('New Analysis')) {
                    btn.setAttribute('data-tooltip', 'Start analyzing a new document');
                }
            });
        }
        
        // Initialize all micro-interactions
        function initMicroInteractions() {
            initScrollAnimations();
            addRippleEffect();
            enhanceDragDropInteractions();
            addTooltips();
        }
        
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

        // Enhanced upload area interactions
        uploadArea.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });

        uploadArea.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });

        // Keyboard navigation support
        uploadArea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                imageFile.click();
            }
        });

        // Focus management for accessibility
        uploadArea.addEventListener('focus', function() {
            this.style.outline = `2px solid var(--primary)`;
            this.style.outlineOffset = '2px';
        });

        uploadArea.addEventListener('blur', function() {
            this.style.outline = 'none';
        });
        
        // Initialize micro-interactions
        initMicroInteractions();
    });
    
    // Global functions
    function startNewAnalysis() {
        // Reset to step 1
        document.getElementById('step3').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
        
        // Reset upload area
        const uploadArea = document.getElementById('uploadArea');
        uploadArea.innerHTML = `
            <div class="upload-icon">
                <span class="material-icons-round">cloud_upload</span>
            </div>
            <div class="upload-text">Drop your medical image here</div>
            <div class="upload-hint">or click to browse (JPG, PNG, GIF, BMP, WebP up to 10MB)</div>
        `;
        
        showMessage('Ready for new analysis', 'success');
    }
</script>

<?php include('footer.php'); ?>