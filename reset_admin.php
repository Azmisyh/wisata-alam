<?php
require_once 'config.php';

echo "<h2>Reset Admin Password</h2>";

// Delete existing admin
$stmt = $conn->prepare("DELETE FROM users WHERE username = 'admin'");
$stmt->execute();
echo "<p>✅ Old admin user deleted</p>";

// Create new admin with fresh password
$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$email = 'admin@wisataalam.com';
$full_name = 'Administrator';
$role = 'admin';

$stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$username, $hashed_password, $email, $full_name, $role]);

echo "<p>✅ New admin user created</p>";
echo "<p>Username: admin</p>";
echo "<p>Password: admin123</p>";
echo "<p>Email: admin@wisataalam.com</p>";
echo "<p>Role: admin</p>";

// Test the new password
$stmt = $conn->prepare("SELECT * FROM users WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->fetch();

if (password_verify($password, $admin['password'])) {
    echo "<p>✅ Password verification test PASSED</p>";
} else {
    echo "<p>❌ Password verification test FAILED</p>";
}

echo "<hr>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
echo "<p>Use: username = 'admin', password = 'admin123'</p>";
?>
