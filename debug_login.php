<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    echo "<h3>Debug Login Process</h3>";
    echo "<p>Username: $username</p>";
    echo "<p>Password: $password</p>";
    
    // Query user
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p>✅ User found in database</p>";
        echo "<p>User ID: {$user['id']}</p>";
        echo "<p>Username: {$user['username']}</p>";
        echo "<p>Role: {$user['role']}</p>";
        
        if (password_verify($password, $user['password'])) {
            echo "<p>✅ Password verification SUCCESS</p>";
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_full_name'] = $user['full_name'];
            
            echo "<p>✅ Session created</p>";
            echo "<p>Session ID: " . session_id() . "</p>";
            
            if (is_admin()) {
                echo "<p>✅ User is admin</p>";
                echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
            } else {
                echo "<p>❌ User is not admin</p>";
            }
            
        } else {
            echo "<p>❌ Password verification FAILED</p>";
            echo "<p>Expected password: admin123</p>";
            echo "<p>Stored hash: {$user['password']}</p>";
        }
    } else {
        echo "<p>❌ User not found</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Debug Login Form</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="admin" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" value="admin123" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Test Login</button>
                        </form>
                        
                        <hr>
                        <h5>Quick Links:</h5>
                        <p><a href="test_admin.php">Test Admin Setup</a></p>
                        <p><a href="login.php">Normal Login Page</a></p>
                        <p><a href="dashboard.php">Dashboard</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
