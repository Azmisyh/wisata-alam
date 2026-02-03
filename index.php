<?php
require_once 'config.php';

// Get featured destinations
$stmt = $conn->prepare("SELECT d.*, COUNT(r.id) as review_count FROM destinations d LEFT JOIN reviews r ON d.id = r.destination_id GROUP BY d.id ORDER BY d.rating_avg DESC LIMIT 6");
$stmt->execute();
$featured_destinations = $stmt->fetchAll();

// Get latest reviews
$stmt = $conn->prepare("SELECT r.*, u.username, d.name as destination_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN destinations d ON r.destination_id = d.id ORDER BY r.created_at DESC LIMIT 5");
$stmt->execute();
$latest_reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Jelajahi Keindahan Indonesia</title>
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
                        <a class="nav-link active" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="destinations.php">Destinasi</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                                <?php if (is_admin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="dashboard.php">Dashboard Admin</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Keluar</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Masuk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center text-white">
            <h1 class="display-4 fw-bold mb-4">Jelajahi Keindahan Alam Indonesia</h1>
            <p class="lead mb-4">Temukan destinasi wisata terbaik dan bagikan pengalaman Anda</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <form action="search.php" method="GET" class="d-flex">
                        <input class="form-control form-control-lg me-2" type="search" name="q" placeholder="Cari destinasi wisata..." required>
                        <button class="btn btn-warning btn-lg" type="submit">
                            <i class="fas fa-search me-2"></i>Cari
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Destinations -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Destinasi Populer</h2>
            <div class="row">
                <?php foreach ($featured_destinations as $destination): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 destination-card">
                            <img src="<?php echo $destination['image_url']; ?>" class="card-img-top" alt="<?php echo $destination['name']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $destination['name']; ?></h5>
                                <p class="card-text text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo $destination['location']; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $destination['rating_avg'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                        <?php endfor; ?>
                                        <small class="ms-1">(<?php echo $destination['review_count']; ?>)</small>
                                    </div>
                                    <span class="badge bg-success"><?php echo ucfirst($destination['category']); ?></span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="destination.php?id=<?php echo $destination['id']; ?>" class="btn btn-primary w-100">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="destinations.php" class="btn btn-outline-success btn-lg">Lihat Semua Destinasi</a>
            </div>
        </div>
    </section>

    <!-- Latest Reviews -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4">Ulasan Terbaru</h2>
            <div class="row">
                <?php foreach ($latest_reviews as $review): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <h6 class="card-title mb-0"><?php echo $review['destination_name']; ?></h6>
                                    <div class="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-secondary'; ?> small"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="card-text"><?php echo substr($review['comment'], 0, 100) . '...'; ?></p>
                                <small class="text-muted">
                                    Oleh <?php echo $review['username']; ?> • <?php echo format_date($review['created_at']); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo APP_NAME; ?></h5>
                    <p>Platform rekomendasi destinasi wisata Indonesia</p>
                </div>
                <div class="col-md-6">
                    <h5>Kontak</h5>
                    <p><i class="fas fa-envelope me-2"></i>info@wisataalam.com</p>
                    <p><i class="fas fa-phone me-2"></i>+62 812-3456-7890</p>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center">
                <p>&copy; Copyright by NPM_NAMA_KELAS_UASWEB1</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
