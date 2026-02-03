<?php
require_once 'config.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    // CSRF token validation
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Token CSRF tidak valid!';
    } else {
        // Query user
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_full_name'] = $user['full_name'];
            
            // Update last activity
            $stmt = $conn->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect('dashboard.php');
            } else {
                redirect('index.php');
            }
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-umbrella-beach me-2"></i><?php echo APP_NAME; ?>
            </a>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-sign-in-alt text-success me-2"></i>Masuk
                        </h3>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username atau Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Ingat saya</label>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk
                            </button>
                            
                            <div class="text-center">
                                <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5 fixed-bottom">
        <div class="container text-center">
            <p>&copy; Copyright by 23552011068_Azmi Syahri Ramadhan_TIF 23 CNS A_UASWEB1</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
