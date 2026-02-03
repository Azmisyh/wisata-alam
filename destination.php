<?php
require_once 'config.php';

// Get destination ID
$destination_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($destination_id === 0) {
    redirect('destinations.php');
}

// Get destination details
$stmt = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->execute([$destination_id]);
$destination = $stmt->fetch();

if (!$destination) {
    redirect('destinations.php');
}

// Get reviews for this destination
$stmt = $conn->prepare("SELECT r.*, u.username, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.destination_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$destination_id]);
$reviews = $stmt->fetchAll();

// Check if user has already reviewed this destination
$user_has_reviewed = false;
if (is_logged_in()) {
    $stmt = $conn->prepare("SELECT id FROM reviews WHERE destination_id = ? AND user_id = ?");
    $stmt->execute([$destination_id, $_SESSION['user_id']]);
    $user_has_reviewed = $stmt->fetch() !== false;
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in() && !$user_has_reviewed) {
    $rating = (int)$_POST['rating'];
    $comment = clean_input($_POST['comment']);
    
    // CSRF token validation
    if (verify_csrf_token($_POST['csrf_token']) && $rating >= 1 && $rating <= 5) {
        try {
            // Insert review
            $stmt = $conn->prepare("INSERT INTO reviews (destination_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$destination_id, $_SESSION['user_id'], $rating, $comment]);
            
            // Update destination rating
            $stmt = $conn->prepare("UPDATE destinations SET rating_avg = (SELECT AVG(rating) FROM reviews WHERE destination_id = ?), review_count = (SELECT COUNT(*) FROM reviews WHERE destination_id = ?) WHERE id = ?");
            $stmt->execute([$destination_id, $destination_id, $destination_id]);
            
            // Refresh page to show new review
            header("Refresh:0");
            exit();
        } catch(PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $destination['name']; ?> - <?php echo APP_NAME; ?></title>
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

    <!-- Destination Detail -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-md-8">
                    <!-- Destination Image -->
                    <div class="mb-4">
                        <img src="<?php echo $destination['image_url']; ?>" class="img-fluid rounded" alt="<?php echo $destination['name']; ?>">
                    </div>
                    
                    <!-- Destination Info -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h1 class="card-title"><?php echo $destination['name']; ?></h1>
                            <div class="mb-3">
                                <span class="badge bg-success me-2"><?php echo ucfirst($destination['category']); ?></span>
                                <span class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo $destination['location']; ?>, <?php echo $destination['province']; ?>
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $destination['rating_avg'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="ms-2"><?php echo number_format($destination['rating_avg'], 1); ?> (<?php echo $destination['review_count']; ?> ulasan)</span>
                                </div>
                            </div>
                            
                            <div class="card-text">
                                <h5>Deskripsi</h5>
                                <p><?php echo nl2br($destination['description']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reviews Section -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-comments me-2"></i>Ulasan Pengunjung (<?php echo count($reviews); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (is_logged_in() && !$user_has_reviewed): ?>
                                <!-- Review Form -->
                                <div class="mb-4">
                                    <h6>Tulis Ulasan Anda</h6>
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <div class="mb-3">
                                            <label for="rating" class="form-label">Rating</label>
                                            <div class="rating-input">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                                    <label for="star<?php echo $i; ?>" class="star-label">
                                                        <i class="fas fa-star"></i>
                                                    </label>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="comment" class="form-label">Ulasan</label>
                                            <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-paper-plane me-2"></i>Kirim Ulasan
                                        </button>
                                    </form>
                                </div>
                                <hr>
                            <?php elseif (is_logged_in() && $user_has_reviewed): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>Anda sudah memberikan ulasan untuk destinasi ini.
                                </div>
                                <hr>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i><a href="login.php">Masuk</a> untuk memberikan ulasan.
                                </div>
                                <hr>
                            <?php endif; ?>
                            
                            <!-- Reviews List -->
                            <?php if (count($reviews) > 0): ?>
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-item mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <div>
                                                <h6 class="mb-0"><?php echo $review['full_name']; ?></h6>
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
                            <?php else: ?>
                                <p class="text-muted text-center">Belum ada ulasan untuk destinasi ini.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Quick Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6><i class="fas fa-info-circle me-2"></i>Informasi Cepat</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-map-marker-alt text-success me-2"></i>
                                    <strong>Lokasi:</strong> <?php echo $destination['location']; ?>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-globe text-success me-2"></i>
                                    <strong>Provinsi:</strong> <?php echo $destination['province']; ?>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-tag text-success me-2"></i>
                                    <strong>Kategori:</strong> <?php echo ucfirst($destination['category']); ?>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-star text-success me-2"></i>
                                    <strong>Rating:</strong> <?php echo number_format($destination['rating_avg'], 1); ?>/5.0
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-comments text-success me-2"></i>
                                    <strong>Ulasan:</strong> <?php echo $destination['review_count']; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Share -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6><i class="fas fa-share-alt me-2"></i>Bagikan</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary">
                                    <i class="fab fa-facebook me-2"></i>Facebook
                                </button>
                                <button class="btn btn-info">
                                    <i class="fab fa-twitter me-2"></i>Twitter
                                </button>
                                <button class="btn btn-success">
                                    <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; Copyright by NPM_NAMA_KELAS_UASWEB1</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Rating input functionality
        document.querySelectorAll('.rating-input input').forEach(input => {
            input.addEventListener('change', function() {
                document.querySelectorAll('.rating-input label').forEach(label => {
                    label.classList.remove('active');
                });
                
                const rating = parseInt(this.value);
                for (let i = 1; i <= rating; i++) {
                    document.querySelector(`label[for="star${i}"]`).classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
