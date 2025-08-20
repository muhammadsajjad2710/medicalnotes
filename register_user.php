<?php
include("database.php");

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['username', 'firstname', 'lastname', 'email', 'password', 'confirm_password'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        showError("Missing required fields: " . implode(', ', $missing_fields));
        exit;
    }
    
    // Collect and sanitize input
    $username = trim($_POST['username']);
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $name = $firstname . ' ' . $lastname; // Combine first and last name
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $doctorname = !empty($_POST['doctorname']) ? trim($_POST['doctorname']) : null; // Optional field
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null; // Optional field
    $company = !empty($_POST['company']) ? trim($_POST['company']) : null; // Optional field
    
    // Set login field to match username (required by database constraint)
    $login = $username;
    
    // Additional validation
    if (strlen($username) < 3) {
        showError("Username must be at least 3 characters long.");
        exit;
    }
    
    if (strlen($firstname) < 2) {
        showError("First name must be at least 2 characters long.");
        exit;
    }
    
    if (strlen($lastname) < 2) {
        showError("Last name must be at least 2 characters long.");
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        showError("Please enter a valid email address.");
        exit;
    }
    
    if (strlen($password) < 6) {
        showError("Password must be at least 6 characters long.");
        exit;
    }
    
    if ($password !== $confirm_password) {
        showError("Passwords do not match. Please try again.");
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Set default values
    $level = 1; // default user level
    $jointid = date("Y-m-d H:i:s");
    $stripeid = null;
    $credits = 10; // Default credits for new users

    // Check if username or email already exists
    $checkSql = "SELECT member_id FROM members WHERE username = ? OR email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        showError("Username or email already exists. Please choose different credentials or sign in instead.");
        exit;
    }
    $checkStmt->close();

    // Insert user into database
    $sql = "INSERT INTO members (username, login, password, name, firstname, lastname, email, doctorname, phone, company, level, jointid, stripeid, credits)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("sssssssssssiss", $username, $login, $hashed_password, $name, $firstname, $lastname, $email, $doctorname, $phone, $company, $level, $jointid, $stripeid, $credits);

    if ($stmt->execute()) {
        // Success - redirect to login with success message
        header("Location: login.php?success=registered&username=" . urlencode($username));
        exit;
    } else {
        showError("Registration failed: " . $stmt->error . ". Please try again.");
    }

    $stmt->close();
    $conn->close();
} else {
    showError("Invalid request. Please submit the registration form properly.");
}

function showError($message) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Registration Error - Chief.AI</title>
        <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap' rel='stylesheet'>
        <style>
            :root{--danger:#ef4444;--text:#1f2937;--bg:#f9fafb;--card:#ffffff;--border:#e5e7eb}
            *{box-sizing:border-box}
            body{font-family:Inter,system-ui,sans-serif;background:var(--bg);margin:0;color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
            .error-card{background:var(--card);border:1px solid var(--border);border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,0.08);max-width:480px;width:100%;padding:2rem;text-align:center}
            .error-icon{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#ef4444,#dc2626);margin:0 auto 1.5rem;display:flex;align-items:center;justify-content:center;color:#fff;font-size:2rem}
            h1{margin:0 0 1rem;font-size:1.5rem;font-weight:600}
            p{margin:0 0 1.5rem;color:#6b7280;line-height:1.6}
            .actions{display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap}
            .btn{display:inline-block;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:0.75rem 1.5rem;border-radius:10px;text-decoration:none;font-weight:600;transition:all 0.2s ease}
            .btn:hover{filter:brightness(1.05);transform:translateY(-1px)}
            .btn-outline{background:#fff;color:#667eea;border:2px solid #667eea}
        </style>
    </head>
    <body>
        <div class='error-card' role='alert' aria-live='assertive'>
            <div class='error-icon' aria-hidden='true'>⚠</div>
            <h1>Registration Failed</h1>
            <p>" . htmlspecialchars($message) . "</p>
            <div class='actions'>
                <a href='register.php' class='btn' aria-label='Back to registration'>← Try Again</a>
                <a href='login.php' class='btn-outline' aria-label='Go to login'>Sign In</a>
            </div>
        </div>
    </body>
    </html>";
}
?>

