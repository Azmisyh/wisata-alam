<?php
require_once 'config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

// Get dashboard statistics based on role
if ($user_role === 'admin') {
    // Admin statistics
    $stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
    $stmt->execute();
    $total_users = $stmt->fetch()['total_users'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total_destinations FROM destinations");
    $stmt->execute();
    $total_destinations = $stmt->fetch()['total_destinations'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total_reviews FROM reviews");
    $stmt->execute();
    $total_reviews = $stmt->fetch()['total_reviews'];
    
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews");
    $stmt->execute();
    $avg_rating = $stmt->fetch()['avg_rating'] ?: 0;
    
    // Get latest destinations
    $stmt = $conn->prepare("SELECT * FROM destinations ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $latest_destinations = $stmt->fetchAll();
    
    // Get latest reviews
    $stmt = $conn->prepare("SELECT r.*, u.username, d.name as destination_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN destinations d ON r.destination_id = d.id ORDER BY r.created_at DESC LIMIT 5");
    $stmt->execute();
    $latest_reviews = $stmt->fetchAll();
    
} else {
    // User statistics
    $stmt = $conn->prepare("SELECT COUNT(*) as user_reviews FROM reviews WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_reviews = $stmt->fetch()['user_reviews'];
    
    $stmt = $conn->prepare("SELECT AVG(rating) as user_avg_rating FROM reviews WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_avg_rating = $stmt->fetch()['user_avg_rating'] ?: 0;
    
    // Get user's latest reviews
    $stmt = $conn->prepare("SELECT r.*, d.name as destination_name FROM reviews r JOIN destinations d ON r.destination_id = d.id WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $user_latest_reviews = $stmt->fetchAll();
    
    // Get recommended destinations
    $stmt = $conn->prepare("SELECT * FROM destinations ORDER BY rating_avg DESC LIMIT 6");
    $stmt->execute();
    $recommended_destinations = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
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
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
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
                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                            <?php if ($user_role === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="admin/manage_destinations.php">Kelola Destinasi</a></li>
                                <li><a class="dropdown-item" href="admin/manage_users.php">Kelola Pengguna</a></li>
                                <li><a class="dropdown-item" href="admin/reports.php">Laporan</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container mt-4">
        <?php if ($user_role === 'admin'): ?>
            <!-- Admin Dashboard Header -->
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fas fa-shield-alt me-3 fa-2x"></i>
                <div>
                    <h4 class="alert-heading mb-1">Admin Dashboard</h4>
                    <p class="mb-0">Selamat datang di panel administrasi sistem</p>
                </div>
            </div>
        <?php else: ?>
            <!-- User Dashboard Header -->
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="fas fa-user me-3 fa-2x"></i>
                <div>
                    <h4 class="alert-heading mb-1">User Dashboard</h4>
                    <p class="mb-0">Selamat datang, <?php echo $_SESSION['user_full_name']; ?>!</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($user_role === 'admin'): ?>
            <!-- Admin Dashboard -->
            <!-- Quick Access Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Access Admin</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <a href="admin/manage_destinations.php" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-map-marked-alt me-2"></i>Kelola Destinasi
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="admin/manage_users.php" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-users me-2"></i>Kelola Pengguna
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="admin/reports.php" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-chart-bar me-2"></i>Laporan
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="admin/reports.php?export=excel" class="btn btn-success w-100">
                                        <i class="fas fa-file-excel me-2"></i>Export Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $total_users; ?></h4>
                                    <p>Total Pengguna</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $total_destinations; ?></h4>
                                    <p>Total Destinasi</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-map-marked-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $total_reviews; ?></h4>
                                    <p>Total Ulasan</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-comments fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo number_format($avg_rating, 1); ?></h4>
                                    <p>Rata-rata Rating</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-star fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-map-marked-alt me-2"></i>Destinasi Terbaru</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php foreach ($latest_destinations as $destination): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-1"><?php echo $destination['name']; ?></h6>
                                                <small class="text-muted"><?php echo $destination['location']; ?></small>
                                            </div>
                                            <div>
                                                <span class="badge bg-success"><?php echo ucfirst($destination['category']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-comments me-2"></i>Ulasan Terbaru</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php foreach ($latest_reviews as $review): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-1"><?php echo $review['destination_name']; ?></h6>
                                                <small class="text-muted">Oleh <?php echo $review['username']; ?></small>
                                            </div>
                                            <div>
                                                <div class="rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-secondary'; ?> small"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- User Dashboard -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $user_reviews; ?></h4>
                                    <p>Ulasan Anda</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-comments fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo number_format($user_avg_rating, 1); ?></h4>
                                    <p>Rata-rata Rating Anda</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-star fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-comments me-2"></i>Ulasan Terbaru Anda</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php foreach ($user_latest_reviews as $review): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-1"><?php echo $review['destination_name']; ?></h6>
                                                <small class="text-muted"><?php echo format_date($review['created_at']); ?></small>
                                            </div>
                                            <div>
                                                <div class="rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-secondary'; ?> small"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-star me-2"></i>Rekomendasi Destinasi</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php foreach ($recommended_destinations as $destination): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-1"><?php echo $destination['name']; ?></h6>
                                                <small class="text-muted"><?php echo $destination['location']; ?></small>
                                            </div>
                                            <div>
                                                <a href="destination.php?id=<?php echo $destination['id']; ?>" class="btn btn-sm btn-primary">Lihat</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; Copyright by NPM_NAMA_KELAS_UASWEB1</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
