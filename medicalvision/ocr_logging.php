<?php
/**
 * OCR Error Logging Configuration
 * Enhanced logging for MedicalVision OCR processing
 */

// Configure error logging for OCR
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/ocr_error.log');

// OCR-specific logging functions
function logOCRInfo($message, $data = []) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] INFO: {$message}";
    
    if (!empty($data)) {
        $logEntry .= " | Data: " . json_encode($data);
    }
    
    error_log($logEntry);
}

function logOCRError($message, $error = null, $data = []) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] ERROR: {$message}";
    
    if ($error) {
        $logEntry .= " | Error: " . $error->getMessage();
        $logEntry .= " | File: " . $error->getFile() . ":" . $error->getLine();
    }
    
    if (!empty($data)) {
        $logEntry .= " | Data: " . json_encode($data);
    }
    
    error_log($logEntry);
}

function logOCRSuccess($message, $data = []) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] SUCCESS: {$message}";
    
    if (!empty($data)) {
        $logEntry .= " | Data: " . json_encode($data);
    }
    
    error_log($logEntry);
}

// Health check function for OCR service
function checkOCRHealth() {
    $health = [
        'python_available' => false,
        'script_exists' => false,
        'dependencies_ok' => false,
        'permissions_ok' => false
    ];
    
    // Check if Python is available
    $pythonOutput = shell_exec('py --version 2>&1');
    $health['python_available'] = !empty($pythonOutput) && strpos($pythonOutput, 'Python') !== false;
    
    // Check if OCR script exists
    $scriptPath = __DIR__ . '/handwritten_to_text.py';
    $health['script_exists'] = file_exists($scriptPath);
    
    // Check if dependencies are available
    $testCommand = 'py -c "import google.generativeai, PIL; print(\'OK\')" 2>&1';
    $depOutput = shell_exec($testCommand);
    $health['dependencies_ok'] = strpos($depOutput, 'OK') !== false;
    
    // Check file permissions
    $health['permissions_ok'] = is_readable($scriptPath);
    
    return $health;
}
?>
