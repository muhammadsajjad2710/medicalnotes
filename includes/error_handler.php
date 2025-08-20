<?php
/**
 * Comprehensive Error Handling and Logging System for MedicalNotes
 * Production-grade error management with HIPAA compliance considerations
 */

// Prevent direct access
if (!defined('MEDICALNOTES_ROOT')) {
    define('MEDICALNOTES_ROOT', dirname(__DIR__));
}

// Define environment if not already defined
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development'); // Change to 'production' for live deployment
}

/**
 * Custom Error Handler Class
 */
class MedicalNotesErrorHandler {
    private $logFile;
    private $errorLogFile;
    private $auditLogFile;
    private $maxLogSize = 10 * 1024 * 1024; // 10MB
    private $maxLogFiles = 5;
    
    public function __construct() {
        $this->logFile = MEDICALNOTES_ROOT . '/logs/error.log';
        $this->errorLogFile = MEDICALNOTES_ROOT . '/logs/php_errors.log';
        $this->auditLogFile = MEDICALNOTES_ROOT . '/logs/audit.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Set up error handling
        $this->setupErrorHandling();
    }
    
    /**
     * Set up error handling configuration
     */
    private function setupErrorHandling() {
        // Set error reporting level
        error_reporting(E_ALL);
        
        // Set custom error handler
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
        
        // Set error log file
        ini_set('log_errors', 1);
        ini_set('error_log', $this->errorLogFile);
        
        // Set display errors based on environment
        $isProduction = defined('ENVIRONMENT') && ENVIRONMENT === 'production';
        ini_set('display_errors', $isProduction ? 0 : 1);
    }
    
    /**
     * Handle PHP errors
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        $errorType = $this->getErrorType($errno);
        $message = "[$errorType] $errstr in $errfile on line $errline";
        
        // Log the error
        $this->logError($message, [
            'type' => $errorType,
            'file' => $errfile,
            'line' => $errline,
            'user_id' => $_SESSION['member_id'] ?? 'guest',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
        ]);
        
        // Don't display errors in production
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            return true;
        }
        
        // Display error for development
        if (ini_get('display_errors')) {
            printf("<div style='background: #fee; border: 1px solid #fcc; padding: 10px; margin: 10px; border-radius: 4px;'>");
            printf("<strong>Error:</strong> %s<br>", htmlspecialchars($errstr));
            printf("<strong>File:</strong> %s<br>", htmlspecialchars($errfile));
            printf("<strong>Line:</strong> %d", $errline);
            printf("</div>");
        }
        
        return true;
    }
    
    /**
     * Handle exceptions
     */
    public function handleException($exception) {
        $message = sprintf(
            "[EXCEPTION] %s: %s in %s on line %d\nStack trace:\n%s",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        $this->logError($message, [
            'type' => 'EXCEPTION',
            'class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'user_id' => $_SESSION['member_id'] ?? 'guest',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
        ]);
        
        // Display user-friendly error in production
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            $this->displayUserError();
        } else {
            // Display detailed error for development
            echo "<h1>Exception</h1>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
            echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
            echo "<h2>Stack Trace:</h2>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        }
    }
    
    /**
     * Handle fatal errors
     */
    public function handleFatalError() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $message = sprintf(
                "[FATAL ERROR] %s in %s on line %d",
                $error['message'],
                $error['file'],
                $error['line']
            );
            
            $this->logError($message, [
                'type' => 'FATAL_ERROR',
                'file' => $error['file'],
                'line' => $error['line'],
                'user_id' => $_SESSION['member_id'] ?? 'guest',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
            ]);
            
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
                $this->displayUserError();
            }
        }
    }
    
    /**
     * Log error with context
     */
    private function logError($message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = sprintf(
            "[%s] %s | Context: %s\n",
            $timestamp,
            $message,
            json_encode($context, JSON_UNESCAPED_SLASHES)
        );
        
        // Write to error log
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Rotate logs if needed
        $this->rotateLogs($this->logFile);
        
        // Also log to system error log
        error_log($message);
    }
    
    /**
     * Log audit events
     */
    public function logAudit($event, $details = []) {
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['member_id'] ?? 'guest';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $url = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
        
        $auditEntry = sprintf(
            "[%s] AUDIT | Event: %s | User: %s | IP: %s | URL: %s | Method: %s | Details: %s\n",
            $timestamp,
            $event,
            $userId,
            $ip,
            $url,
            $method,
            json_encode($details, JSON_UNESCAPED_SLASHES)
        );
        
        file_put_contents($this->auditLogFile, $auditEntry, FILE_APPEND | LOCK_EX);
        $this->rotateLogs($this->auditLogFile);
    }
    
    /**
     * Log security events
     */
    public function logSecurity($event, $details = []) {
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['member_id'] ?? 'guest';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $securityEntry = sprintf(
            "[%s] SECURITY | Event: %s | User: %s | IP: %s | Details: %s\n",
            $timestamp,
            $event,
            $userId,
            $ip,
            json_encode($details, JSON_UNESCAPED_SLASHES)
        );
        
        // Log to security log
        $securityLogFile = MEDICALNOTES_ROOT . '/logs/security.log';
        file_put_contents($securityLogFile, $securityEntry, FILE_APPEND | LOCK_EX);
        $this->rotateLogs($securityLogFile);
        
        // Also log to main error log for visibility
        $this->logError("SECURITY EVENT: $event", $details);
    }
    
    /**
     * Log API calls
     */
    public function logAPI($endpoint, $method, $status, $responseTime, $details = []) {
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['member_id'] ?? 'guest';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $apiEntry = sprintf(
            "[%s] API | %s %s | Status: %s | ResponseTime: %sms | User: %s | IP: %s | Details: %s\n",
            $timestamp,
            $method,
            $endpoint,
            $status,
            $responseTime,
            $userId,
            $ip,
            json_encode($details, JSON_UNESCAPED_SLASHES)
        );
        
        $apiLogFile = MEDICALNOTES_ROOT . '/logs/api.log';
        file_put_contents($apiLogFile, $apiEntry, FILE_APPEND | LOCK_EX);
        $this->rotateLogs($apiLogFile);
    }
    
    /**
     * Rotate log files
     */
    private function rotateLogs($logFile) {
        if (!file_exists($logFile)) {
            return;
        }
        
        $fileSize = filesize($logFile);
        if ($fileSize < $this->maxLogSize) {
            return;
        }
        
        // Rotate existing log files
        for ($i = $this->maxLogFiles - 1; $i >= 1; $i--) {
            $oldFile = $logFile . '.' . $i;
            $newFile = $logFile . '.' . ($i + 1);
            
            if (file_exists($oldFile)) {
                if ($i === $this->maxLogFiles - 1) {
                    unlink($oldFile);
                } else {
                    rename($oldFile, $newFile);
                }
            }
        }
        
        // Rename current log file
        rename($logFile, $logFile . '.1');
        
        // Create new log file
        touch($logFile);
        chmod($logFile, 0644);
    }
    
    /**
     * Get error type string
     */
    private function getErrorType($errno) {
        switch ($errno) {
            case E_ERROR: return 'E_ERROR';
            case E_WARNING: return 'E_WARNING';
            case E_PARSE: return 'E_PARSE';
            case E_NOTICE: return 'E_NOTICE';
            case E_CORE_ERROR: return 'E_CORE_ERROR';
            case E_CORE_WARNING: return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: return 'E_COMPILE_WARNING';
            case E_USER_ERROR: return 'E_USER_ERROR';
            case E_USER_WARNING: return 'E_USER_WARNING';
            case E_USER_NOTICE: return 'E_USER_NOTICE';
            case E_STRICT: return 'E_STRICT';
            case E_RECOVERABLE_ERROR: return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: return 'E_DEPRECATED';
            case E_USER_DEPRECATED: return 'E_USER_DEPRECATED';
            default: return 'UNKNOWN';
        }
    }
    
    /**
     * Display user-friendly error in production
     */
    private function displayUserError() {
        http_response_code(500);
        
        if (headers_sent()) {
            return;
        }
        
        // Simple error page
        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>System Error - Chief.AI MedicalNotes</title>';
        echo '<style>';
        echo 'body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }';
        echo '.error-container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }';
        echo '.error-icon { font-size: 48px; color: #e74c3c; margin-bottom: 20px; }';
        echo '.error-title { color: #2c3e50; margin-bottom: 20px; }';
        echo '.error-message { color: #7f8c8d; line-height: 1.6; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="error-container">';
        echo '<div class="error-icon">⚠️</div>';
        echo '<h1 class="error-title">System Temporarily Unavailable</h1>';
        echo '<p class="error-message">We\'re experiencing technical difficulties. Please try again in a few minutes. If the problem persists, contact support.</p>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Clean up sensitive data from logs
     */
    public function sanitizeForLogs($data) {
        $sensitiveFields = ['password', 'password_hash', 'token', 'api_key', 'secret'];
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (in_array(strtolower($key), $sensitiveFields)) {
                    $data[$key] = '[REDACTED]';
                } else {
                    $data[$key] = $this->sanitizeForLogs($value);
                }
            }
        }
        
        return $data;
    }
}

/**
 * Initialize error handler
 */
$errorHandler = new MedicalNotesErrorHandler();

/**
 * Helper functions for logging
 */
function logAudit($event, $details = []) {
    global $errorHandler;
    if ($errorHandler) {
        $errorHandler->logAudit($event, $details);
    }
}

function logSecurity($event, $details = []) {
    global $errorHandler;
    if ($errorHandler) {
        $errorHandler->logSecurity($event, $details);
    }
}

function logAPI($endpoint, $method, $status, $responseTime, $details = []) {
    global $errorHandler;
    if ($errorHandler) {
        $errorHandler->logAPI($endpoint, $method, $status, $responseTime, $details);
    }
}

function sanitizeForLogs($data) {
    global $errorHandler;
    if ($errorHandler) {
        return $errorHandler->sanitizeForLogs($data);
    }
    return $data;
}

// Log system startup
logAudit('SYSTEM_STARTUP', [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time')
]);
?>
