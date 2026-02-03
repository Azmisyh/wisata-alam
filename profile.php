<?php
require_once 'config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    // CSRF token validation
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Token CSRF tidak valid!';
    } else {
        switch ($action) {
            case 'update_profile':
                $full_name = clean_input($_POST['full_name']);
                $email = clean_input($_POST['email']);
                
                try {
                    // Check if email already exists (excluding current user)
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $user_id]);
                    
                    if ($stmt->fetch()) {
                        $error = 'Email sudah digunakan oleh pengguna lain!';
                    } else {
                        // Update profile
                        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$full_name, $email, $user_id]);
                        
                        // Update session
                        $_SESSION['user_full_name'] = $full_name;
                        $_SESSION['user_email'] = $email;
                        
                        $success = 'Profil berhasil diperbarui!';
                    }
                } catch(PDOException $e) {
                    $error = 'Terjadi kesalahan: ' . $e->getMessage();
                }
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                // Verify current password
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                if (!password_verify($current_password, $user['password'])) {
                    $error = 'Password saat ini salah!';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'Password baru dan konfirmasi tidak cocok!';
                } elseif (strlen($new_password) < 6) {
                    $error = 'Password baru minimal 6 karakter!';
                } else {
                    try {
                        // Update password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$hashed_password, $user_id]);
                        
                        $success = 'Password berhasil diubah!';
                    } catch(PDOException $e) {
                        $error = 'Terjadi kesalahan: ' . $e->getMessage();
                    }
                }
                break;
        }
    }
}

// Get current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();

// Get user statistics
if ($user_role === 'admin') {
    // Admin statistics
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $total_users = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM destinations");
    $stmt->execute();
    $total_destinations = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reviews");
    $stmt->execute();
    $total_reviews = $stmt->fetch()['total'];
} else {
    // User statistics
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reviews WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_reviews = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_avg_rating = $stmt->fetch()['avg_rating'] ?: 0;
    
    // Get user's recent reviews
    $stmt = $conn->prepare("SELECT r.*, d.name as destination_name FROM reviews r JOIN destinations d ON r.destination_id = d.id WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_reviews = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-umbrella-beach me-2"></i><?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="destinations.php">Destinasi</a>
                    </li>
                    <?php if ($user_role === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog me-1"></i>Admin Panel
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="admin/manage_destinations.php">Kelola Destinasi</a></li>
                                <li><a class="dropdown-item" href="admin/manage_users.php">Kelola Pengguna</a></li>
                                <li><a class="dropdown-item" href="admin/reports.php">Laporan</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="profile.php">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Content -->
    <div class="container mt-4">
        <div class="row">
            <!-- Profile Card -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="avatar-circle bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px; font-size: 3rem;">
                            <?php echo strtoupper(substr($current_user['username'], 0, 1)); ?>
                        </div>
                        <h4><?php echo $current_user['full_name']; ?></h4>
                        <p class="text-muted">@<?php echo $current_user['username']; ?></p>
                        <span class="badge bg-<?php echo $user_role === 'admin' ? 'danger' : 'primary'; ?>">
                            <?php echo ucfirst($user_role); ?>
                        </span>
                        
                        <hr>
                        
                        <div class="text-start">
                            <p><i class="fas fa-envelope me-2"></i><?php echo $current_user['email']; ?></p>
                            <p><i class="fas fa-calendar me-2"></i>Bergabung: <?php echo date('d M Y', strtotime($current_user['created_at'])); ?></p>
                        </div>
                        
                        <?php if ($user_role === 'admin'): ?>
                            <hr>
                            <h6>Statistik Admin</h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <h5><?php echo $total_users; ?></h5>
                                    <small>Pengguna</small>
                                </div>
                                <div class="col-4">
                                    <h5><?php echo $total_destinations; ?></h5>
                                    <small>Destinasi</small>
                                </div>
                                <div class="col-4">
                                    <h5><?php echo $total_reviews; ?></h5>
                                    <small>Ulasan</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <hr>
                            <h6>Statistik User</h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5><?php echo $user_reviews; ?></h5>
                                    <small>Ulasan</small>
                                </div>
                                <div class="col-6">
                                    <h5><?php echo number_format($user_avg_rating, 1); ?></h5>
                                    <small>Rating Rata-rata</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Edit Profile -->
            <div class="col-md-8">
                <!-- Success/Error Messages -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Edit Profile Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-user-edit me-2"></i>Edit Profil</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $current_user['full_name']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $current_user['email']; ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" value="<?php echo $current_user['username']; ?>" readonly>
                                        <small class="text-muted">Username tidak dapat diubah</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" value="<?php echo ucfirst($current_user['role']); ?>" readonly>
                                        <small class="text-muted">Role ditentukan oleh admin</small>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Change Password Form -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-lock me-2"></i>Ubah Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="action" value="change_password">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Password Saat Ini</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Password Baru</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                        <small class="text-muted">Minimal 6 karakter</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key me-2"></i>Ubah Password
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Recent Reviews (for users) -->
                <?php if ($user_role === 'user' && isset($recent_reviews) && count($recent_reviews) > 0): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5><i class="fas fa-comments me-2"></i>Ulasan Terbaru Anda</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($recent_reviews as $review): ?>
                                <div class="review-item mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <h6 class="mb-0"><?php echo $review['destination_name']; ?></h6>
                                            <small class="text-muted"><?php echo format_date($review['created_at']); ?></small>
                                        </div>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-secondary'; ?> small"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br($review['comment']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; Copyright by NPM_NAMA_KELAS_UASWEB1</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
