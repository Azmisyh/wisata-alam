<?php
require_once '../config.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Get statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$total_users = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM destinations");
$stmt->execute();
$total_destinations = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM reviews");
$stmt->execute();
$total_reviews = $stmt->fetch()['total'];

// Get destination statistics by category
$stmt = $conn->prepare("SELECT category, COUNT(*) as count FROM destinations GROUP BY category");
$stmt->execute();
$category_stats = $stmt->fetchAll();

// Get top rated destinations
$stmt = $conn->prepare("SELECT * FROM destinations ORDER BY rating_avg DESC LIMIT 10");
$stmt->execute();
$top_destinations = $stmt->fetchAll();

// Get most active users
$stmt = $conn->prepare("SELECT u.username, COUNT(r.id) as review_count FROM users u LEFT JOIN reviews r ON u.id = r.user_id GROUP BY u.id ORDER BY review_count DESC LIMIT 10");
$stmt->execute();
$active_users = $stmt->fetchAll();

// Get recent reviews
$stmt = $conn->prepare("SELECT r.*, u.username, d.name as destination_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN destinations d ON r.destination_id = d.id ORDER BY r.created_at DESC LIMIT 20");
$stmt->execute();
$recent_reviews = $stmt->fetchAll();

// Handle export requests
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    
    if ($format === 'pdf') {
        // PDF Export using TCPDF (simplified version)
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="wisata_alam_report.pdf"');
        
        // Simple PDF content (you would normally use a PDF library here)
        echo '<h1>Laporan Wisata Alam Indonesia</h1>';
        echo '<h2>Statistik Umum</h2>';
        echo '<p>Total Pengguna: ' . $total_users . '</p>';
        echo '<p>Total Destinasi: ' . $total_destinations . '</p>';
        echo '<p>Total Ulasan: ' . $total_reviews . '</p>';
        
        exit();
    } elseif ($format === 'excel') {
        // Excel Export
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="wisata_alam_report.xls"');
        
        echo '<table border="1">';
        echo '<tr><th colspan="2">Laporan Wisata Alam Indonesia</th></tr>';
        echo '<tr><th>Total Pengguna</th><td>' . $total_users . '</td></tr>';
        echo '<tr><th>Total Destinasi</th><td>' . $total_destinations . '</td></tr>';
        echo '<tr><th>Total Ulasan</th><td>' . $total_reviews . '</td></tr>';
        echo '</table>';
        
        echo '<table border="1">';
        echo '<tr><th colspan="5">Destinasi Terpopuler</th></tr>';
        echo '<tr><th>Nama</th><th>Lokasi</th><th>Kategori</th><th>Rating</th><th>Jumlah Ulasan</th></tr>';
        
        foreach ($top_destinations as $dest) {
            echo '<tr>';
            echo '<td>' . $dest['name'] . '</td>';
            echo '<td>' . $dest['location'] . '</td>';
            echo '<td>' . $dest['category'] . '</td>';
            echo '<td>' . $dest['rating_avg'] . '</td>';
            echo '<td>' . $dest['review_count'] . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-umbrella-beach me-2"></i><?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_destinations.php">Kelola Destinasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php">Kelola Pengguna</a></li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reports.php">Laporan</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../profile.php">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-bar text-success me-2"></i>Laporan Sistem</h2>
            <div class="btn-group">
                <a href="?export=excel" class="btn btn-success">
                    <i class="fas fa-file-excel me-2"></i>Export Excel
                </a>
                <a href="?export=pdf" class="btn btn-danger">
                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
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
                                <h4><?php echo $total_reviews > 0 ? round($total_reviews / $total_users, 1) : 0; ?></h4>
                                <p>Rata-rata Ulasan/Pengguna</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Statistics -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie me-2"></i>Statistik Kategori Destinasi</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Jumlah</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($category_stats as $stat): ?>
                                    <tr>
                                        <td><?php echo ucfirst($stat['category']); ?></td>
                                        <td><?php echo $stat['count']; ?></td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo ($stat['count'] / $total_destinations) * 100; ?>%">
                                                    <?php echo round(($stat['count'] / $total_destinations) * 100, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-trophy me-2"></i>Destinasi Terpopuler</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Rating</th>
                                        <th>Ulasan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_destinations as $dest): ?>
                                        <tr>
                                            <td><?php echo $dest['name']; ?></td>
                                            <td>
                                                <div class="rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $dest['rating_avg'] ? 'text-warning' : 'text-secondary'; ?> small"></i>
                                                    <?php endfor; ?>
                                                    <small><?php echo number_format($dest['rating_avg'], 1); ?></small>
                                                </div>
                                            </td>
                                            <td><?php echo $dest['review_count']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-check me-2"></i>Pengguna Paling Aktif</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Jumlah Ulasan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['username']; ?></td>
                                            <td><?php echo $user['review_count']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-clock me-2"></i>Ulasan Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Destinasi</th>
                                        <th>Pengguna</th>
                                        <th>Rating</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($recent_reviews, 0, 10) as $review): ?>
                                        <tr>
                                            <td><?php echo $review['destination_name']; ?></td>
                                            <td><?php echo $review['username']; ?></td>
                                            <td>
                                                <div class="rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-secondary'; ?> small"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; Copyright by 23552011068_Azmi Syahri Ramadhan_TIF 23 CNS A_UASWEB1</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
