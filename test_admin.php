<?php
require_once 'config.php';

// Test admin login
echo "<h2>Testing Admin Login</h2>";

// Check if admin user exists
$stmt = $conn->prepare("SELECT * FROM users WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->fetch();

if ($admin) {
    echo "<p>✅ Admin user found in database</p>";
    echo "<p>ID: {$admin['id']}</p>";
    echo "<p>Username: {$admin['username']}</p>";
    echo "<p>Email: {$admin['email']}</p>";
    echo "<p>Role: {$admin['role']}</p>";
    echo "<p>Password Hash: {$admin['password']}</p>";
    
    // Test password verification
    $password = 'admin123';
    if (password_verify($password, $admin['password'])) {
        echo "<p>✅ Password verification SUCCESS</p>";
    } else {
        echo "<p>❌ Password verification FAILED</p>";
        
        // Create new hash for admin123
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        echo "<p>New hash for 'admin123': {$new_hash}</p>";
        
        // Update admin password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->execute([$new_hash]);
        echo "<p>✅ Admin password updated in database</p>";
    }
} else {
    echo "<p>❌ Admin user NOT found in database</p>";
    
    // Create admin user
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', $hashed_password, 'admin@wisataalam.com', 'Administrator', 'admin']);
    echo "<p>✅ Admin user created with password 'admin123'</p>";
}

// Test session
echo "<h2>Testing Session</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";

if (is_logged_in()) {
    echo "<p>✅ User is logged in</p>";
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Username: " . $_SESSION['username'] . "</p>";
    echo "<p>Role: " . $_SESSION['user_role'] . "</p>";
} else {
    echo "<p>❌ User is not logged in</p>";
}

echo "<h2>Database Connection</h2>";
echo "<p>✅ Database connection successful</p>";

echo "<h2>Test Login Link</h2>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
echo "<p>Use: username = 'admin', password = 'admin123'</p>";
?>
