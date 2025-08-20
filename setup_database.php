<?php
// ===== MEDICALNOTES DATABASE SETUP SCRIPT =====
// Run this script once to set up the database tables

// Include configuration
require_once 'config.php';

echo "<h1>MedicalNotes Database Setup</h1>";
echo "<p>Setting up database tables...</p>";

try {
    $pdo = getDatabaseConnection();
    
    if (!$pdo) {
        echo "<p style='color: red;'>❌ Database connection failed. Please check your configuration.</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Create members table
    $sql = "CREATE TABLE IF NOT EXISTS members (
        member_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        credits INT DEFAULT 10,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Members table created successfully!</p>";
    
    // Create credits_transactions table
    $sql = "CREATE TABLE IF NOT EXISTS credits_transactions (
        transaction_id INT AUTO_INCREMENT PRIMARY KEY,
        member_id INT NOT NULL,
        amount INT NOT NULL,
        type ENUM('purchase', 'usage', 'refund', 'bonus') NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES members(member_id)
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Credits transactions table created successfully!</p>";
    
    // Create audio_files table for MedicalVoice
    $sql = "CREATE TABLE IF NOT EXISTS audio_files (
        file_id INT AUTO_INCREMENT PRIMARY KEY,
        member_id INT NOT NULL,
        filename VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size INT NOT NULL,
        status ENUM('uploaded', 'processing', 'completed', 'failed') DEFAULT 'uploaded',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES members(member_id)
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Audio files table created successfully!</p>";
    
    // Create document_files table for MedicalVision
    $sql = "CREATE TABLE IF NOT EXISTS document_files (
        file_id INT AUTO_INCREMENT PRIMARY KEY,
        member_id INT NOT NULL,
        filename VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size INT NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        status ENUM('uploaded', 'processing', 'completed', 'failed') DEFAULT 'uploaded',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (member_id) REFERENCES members(member_id)
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Document files table created successfully!</p>";
    
    // Insert default admin user (optional)
    $adminUsername = 'admin';
    $adminEmail = 'admin@medicalnotes.com';
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT member_id FROM members WHERE username = ?");
    $stmt->execute([$adminUsername]);
    
    if ($stmt->rowCount() == 0) {
        $sql = "INSERT INTO members (username, email, password_hash, credits) VALUES (?, ?, ?, 100)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$adminUsername, $adminEmail, $adminPassword]);
        echo "<p style='color: green;'>✅ Default admin user created (username: admin, password: admin123)</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Admin user already exists</p>";
    }
    
    echo "<h2>🎉 Database setup completed successfully!</h2>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>Register new users</li>";
    echo "<li>Use MedicalVoice for audio transcription</li>";
    echo "<li>Use MedicalVision for document analysis</li>";
    echo "<li>Manage user credits</li>";
    echo "</ul>";
    echo "<p><a href='index.php'>← Return to MedicalNotes Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error during setup: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database configuration and try again.</p>";
}
?>
