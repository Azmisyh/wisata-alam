<?php
require_once 'config.php';

// Get search query
$query = isset($_GET['q']) ? clean_input($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

if (empty($query)) {
    redirect('index.php');
}

// Search destinations
$search_sql = "SELECT * FROM destinations WHERE name LIKE ? OR description LIKE ? OR location LIKE ? OR province LIKE ? ORDER BY rating_avg DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($search_sql);
$search_term = "%$query%";
$stmt->execute([$search_term, $search_term, $search_term, $search_term]);
$destinations = $stmt->fetchAll();

// Get total results for pagination
$count_sql = "SELECT COUNT(*) as total FROM destinations WHERE name LIKE ? OR description LIKE ? OR location LIKE ? OR province LIKE ?";
$stmt = $conn->prepare($count_sql);
$stmt->execute([$search_term, $search_term, $search_term, $search_term]);
$total_results = $stmt->fetch()['total'];
$total_pages = ceil($total_results / $limit);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian: "<?php echo htmlspecialchars($query); ?>" - <?php echo APP_NAME; ?></title>
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

    <!-- Search Results Header -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold">Hasil Pencarian</h1>
                    <p class="lead">
                        <?php if (count($destinations) > 0): ?>
                            Menemukan <strong><?php echo $total_results; ?></strong> destinasi untuk "<strong><?php echo htmlspecialchars($query); ?></strong>"
                        <?php else: ?>
                            Tidak ada destinasi ditemukan untuk "<strong><?php echo htmlspecialchars($query); ?></strong>"
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <form action="search.php" method="GET" class="d-flex">
                        <input class="form-control me-2" type="search" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Cari destinasi..." required>
                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Results -->
    <section class="py-4">
        <div class="container">
            <?php if (count($destinations) > 0): ?>
                <div class="row">
                    <?php foreach ($destinations as $destination): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 destination-card">
                                <img src="<?php echo $destination['image_url']; ?>" class="card-img-top" alt="<?php echo $destination['name']; ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $destination['name']; ?></h5>
                                    <p class="card-text text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo $destination['location']; ?>
                                    </p>
                                    <p class="card-text"><?php echo substr($destination['description'], 0, 100) . '...'; ?></p>
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

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>Tidak ada destinasi ditemukan</h4>
                    <p class="text-muted">Coba kata kunci yang berbeda atau <a href="destinations.php">lihat semua destinasi</a></p>
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-success me-2">
                            <i class="fas fa-home me-2"></i>Kembali ke Beranda
                        </a>
                        <a href="destinations.php" class="btn btn-outline-success">
                            <i class="fas fa-map-marked-alt me-2"></i>Lihat Semua Destinasi
                        </a>
                    </div>
                </div>
            <?php endif; ?>
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
