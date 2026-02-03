<?php
require_once 'config.php';

// Get filter parameters
$category = isset($_GET['category']) ? clean_input($_GET['category']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = [];
$params = [];

if ($category) {
    $where_conditions[] = "category = ?";
    $params[] = $category;
}

if ($search) {
    $where_conditions[] = "(name LIKE ? OR description LIKE ? OR location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total destinations
$count_sql = "SELECT COUNT(*) as total FROM destinations $where_clause";
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_destinations = $stmt->fetch()['total'];
$total_pages = ceil($total_destinations / $limit);

// Get destinations with pagination
$sql = "SELECT * FROM destinations $where_clause ORDER BY rating_avg DESC, created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$destinations = $stmt->fetchAll();

// Get categories for filter
$stmt = $conn->prepare("SELECT DISTINCT category FROM destinations ORDER BY category");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinasi Wisata - <?php echo APP_NAME; ?></title>
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
                        <a class="nav-link active" href="destinations.php">Destinasi</a>
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

    <!-- Destinations Header -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold">Destinasi Wisata Indonesia</h1>
                    <p class="lead">Temukan dan jelajahi keindahan destinasi wisata di seluruh Indonesia</p>
                </div>
                <div class="col-md-4">
                    <form method="GET" class="d-flex">
                        <input class="form-control me-2" type="search" name="search" placeholder="Cari destinasi..." value="<?php echo $search; ?>">
                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters and Destinations -->
    <section class="py-4">
        <div class="container">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Filter Destinasi</h6>
                            <form method="GET">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="category" class="form-label">Kategori</label>
                                        <select class="form-select" id="category" name="category">
                                            <option value="">Semua Kategori</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['category']; ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($cat['category']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="search" class="form-label">Pencarian</label>
                                        <input type="text" class="form-control" id="search" name="search" value="<?php echo $search; ?>" placeholder="Cari nama, lokasi, atau deskripsi...">
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-filter me-2"></i>Terapkan Filter
                                        </button>
                                        <a href="destinations.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Count -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <p class="text-muted">Menampilkan <?php echo count($destinations); ?> dari <?php echo $total_destinations; ?> destinasi</p>
                </div>
            </div>

            <!-- Destinations Grid -->
            <div class="row">
                <?php if (count($destinations) > 0): ?>
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
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h4>Tidak ada destinasi ditemukan</h4>
                            <p class="text-muted">Coba ubah filter atau kata kunci pencarian Anda</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&category=<?php echo $category; ?>&search=<?php echo $search; ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $category; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&category=<?php echo $category; ?>&search=<?php echo $search; ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
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
</body>
</html>
