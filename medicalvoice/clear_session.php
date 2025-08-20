<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Capture current paths before clearing (for cleanup)
$prevUploaded = $_SESSION['uploaded_file'] ?? null;
$prevLog = $_SESSION['log_file'] ?? null;
$prevJson = $_SESSION['json_file'] ?? null;

// Clear all medical voice session data
unset($_SESSION['uploaded_file']);
unset($_SESSION['log_file']);
unset($_SESSION['json_file']);
unset($_SESSION['processing_complete']);

// Also clean up any uploaded files (optional - for complete cleanup)
if (isset($_GET['cleanup_files']) && $_GET['cleanup_files'] === '1') {
    // Remove any lingering lock file from previous run to allow fresh processing
    if ($prevLog) {
        $lock = $prevLog . '.lock';
        if (file_exists($lock)) {
            @unlink($lock);
        }
    }
}

// Redirect back to index with clear_session parameter for fresh session
// Optional anchor param to jump to a section (e.g., #upload)
$anchor = isset($_GET['anchor']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['anchor']) : '';
// Redirect WITHOUT the clear_session flag so the next page load doesn't clear again
$location = 'index.php' . ($anchor ? '#' . $anchor : '');
header('Location: ' . $location);
exit();
?>
