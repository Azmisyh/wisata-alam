<?php
require_once '../config.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    // CSRF token validation
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Token CSRF tidak valid!';
    } else {
        switch ($action) {
            case 'add':
                $name = clean_input($_POST['name']);
                $description = clean_input($_POST['description']);
                $location = clean_input($_POST['location']);
                $province = clean_input($_POST['province']);
                $category = $_POST['category'];
                $image_url = clean_input($_POST['image_url']);
                
                try {
                    $stmt = $conn->prepare("INSERT INTO destinations (name, description, location, province, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $location, $province, $category, $image_url]);
                    $success = 'Destinasi berhasil ditambahkan!';
                } catch(PDOException $e) {
                    $error = 'Terjadi kesalahan: ' . $e->getMessage();
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $name = clean_input($_POST['name']);
                $description = clean_input($_POST['description']);
                $location = clean_input($_POST['location']);
                $province = clean_input($_POST['province']);
                $category = $_POST['category'];
                $image_url = clean_input($_POST['image_url']);
                
                try {
                    $stmt = $conn->prepare("UPDATE destinations SET name = ?, description = ?, location = ?, province = ?, category = ?, image_url = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$name, $description, $location, $province, $category, $image_url, $id]);
                    $success = 'Destinasi berhasil diperbarui!';
                } catch(PDOException $e) {
                    $error = 'Terjadi kesalahan: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                try {
                    $stmt = $conn->prepare("DELETE FROM destinations WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = 'Destinasi berhasil dihapus!';
                } catch(PDOException $e) {
                    $error = 'Terjadi kesalahan: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get all destinations
$stmt = $conn->prepare("SELECT * FROM destinations ORDER BY created_at DESC");
$stmt->execute();
$destinations = $stmt->fetchAll();

// Get destination for editing
$edit_destination = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
    $stmt->execute([$id]);
    $edit_destination = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Destinasi - <?php echo APP_NAME; ?></title>
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
                        <a class="nav-link active" href="manage_destinations.php">Kelola Destinasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php">Kelola Pengguna</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Laporan</a>
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
            <h2><i class="fas fa-map-marked-alt text-success me-2"></i>Kelola Destinasi</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-2"></i>Tambah Destinasi
            </button>
        </div>

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

        <!-- Destinations Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Lokasi</th>
                                <th>Kategori</th>
                                <th>Rating</th>
                                <th>Ulasan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($destinations as $destination): ?>
                                <tr>
                                    <td><?php echo $destination['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $destination['image_url']; ?>" alt="<?php echo $destination['name']; ?>" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <div>
                                                <div class="fw-bold"><?php echo $destination['name']; ?></div>
                                                <small class="text-muted"><?php echo substr($destination['description'], 0, 50) . '...'; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $destination['location']; ?></td>
                                    <td><span class="badge bg-success"><?php echo ucfirst($destination['category']); ?></span></td>
                                    <td>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $destination['rating_avg'] ? 'text-warning' : 'text-secondary'; ?> small"></i>
                                            <?php endfor; ?>
                                            <small><?php echo number_format($destination['rating_avg'], 1); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo $destination['review_count']; ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="../destination.php?id=<?php echo $destination['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-warning" onclick="editDestination(<?php echo $destination['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteDestination(<?php echo $destination['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Destinasi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Destinasi</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Lokasi</label>
                                    <input type="text" class="form-control" id="location" name="location" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="province" class="form-label">Provinsi</label>
                                    <input type="text" class="form-control" id="province" name="province" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Kategori</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="alam">Alam</option>
                                        <option value="budaya">Budaya</option>
                                        <option value="sejarah">Sejarah</option>
                                        <option value="rekreasi">Rekreasi</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="image_url" class="form-label">URL Gambar</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Tambah Destinasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Destinasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editId">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editName" class="form-label">Nama Destinasi</label>
                                    <input type="text" class="form-control" id="editName" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editLocation" class="form-label">Lokasi</label>
                                    <input type="text" class="form-control" id="editLocation" name="location" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editProvince" class="form-label">Provinsi</label>
                                    <input type="text" class="form-control" id="editProvince" name="province" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editCategory" class="form-label">Kategori</label>
                                    <select class="form-select" id="editCategory" name="category" required>
                                        <option value="alam">Alam</option>
                                        <option value="budaya">Budaya</option>
                                        <option value="sejarah">Sejarah</option>
                                        <option value="rekreasi">Rekreasi</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editImageUrl" class="form-label">URL Gambar</label>
                            <input type="url" class="form-control" id="editImageUrl" name="image_url" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Update Destinasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Destinasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus destinasi ini? Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
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
    <script>
        function editDestination(id) {
            // Fetch destination data and populate form
            fetch(`get_destination.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editId').value = data.id;
                    document.getElementById('editName').value = data.name;
                    document.getElementById('editLocation').value = data.location;
                    document.getElementById('editProvince').value = data.province;
                    document.getElementById('editCategory').value = data.category;
                    document.getElementById('editImageUrl').value = data.image_url;
                    document.getElementById('editDescription').value = data.description;
                    
                    new bootstrap.Modal(document.getElementById('editModal')).show();
                });
        }

        function deleteDestination(id) {
            document.getElementById('deleteId').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
