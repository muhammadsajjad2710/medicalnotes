<?php

session_start();
include("database.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT member_id, password, name FROM members WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['member_id'] = $row['member_id'];
            $_SESSION['name'] = $row['name'];
            header("Location: index.php");
            exit;
        } else {
            showError("Invalid credentials. Please check your username and password.");
        }
    } else {
        showError("User not found. Please check your username or register for a new account.");
    }

    $stmt->close();
    $conn->close();
} else {
    showError("Invalid request. Please submit the login form properly.");
}

function showError($message) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Login Error - Medical AI</title>
        <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap' rel='stylesheet'>
        <style>
            :root{--danger:#ef4444;--text:#1f2937;--bg:#f9fafb;--card:#ffffff;--border:#e5e7eb}
            *{box-sizing:border-box}
            body{font-family:Inter,system-ui,sans-serif;background:var(--bg);margin:0;color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
            .error-card{background:var(--card);border:1px solid var(--border);border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,0.08);max-width:480px;width:100%;padding:2rem;text-align:center}
            .error-icon{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#ef4444,#dc2626);margin:0 auto 1.5rem;display:flex;align-items:center;justify-content:center;color:#fff;font-size:2rem}
            h1{margin:0 0 1rem;font-size:1.5rem;font-weight:600}
            p{margin:0 0 1.5rem;color:#6b7280;line-height:1.6}
            .btn{display:inline-block;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:0.75rem 1.5rem;border-radius:10px;text-decoration:none;font-weight:600;transition:all 0.2s ease}
            .btn:hover{filter:brightness(1.05);transform:translateY(-1px)}
        </style>
    </head>
    <body>
        <div class='error-card' role='alert' aria-live='assertive'>
            <div class='error-icon' aria-hidden='true'>⚠</div>
            <h1>Login Failed</h1>
            <p>" . htmlspecialchars($message) . "</p>
            <a href='login.php' class='btn' aria-label='Back to login'>← Back to Login</a>
        </div>
    </body>
    </html>";
}
?>

