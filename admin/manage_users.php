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
                $username = clean_input($_POST['username']);
                $email = clean_input($_POST['email']);
                $full_name = clean_input($_POST['full_name']);
                $password = $_POST['password'];
                $role = $_POST['role'];
                
                // Validation
                if (strlen($password) < 6) {
                    $error = 'Password minimal 6 karakter!';
                } else {
                    try {
                        // Check if username or email already exists
                        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                        $stmt->execute([$username, $email]);
                        
                        if ($stmt->fetch()) {
                            $error = 'Username atau email sudah digunakan!';
                        } else {
                            // Insert new user
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, password, role) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$username, $email, $full_name, $hashed_password, $role]);
                            $success = 'Pengguna berhasil ditambahkan!';
                        }
                    } catch(PDOException $e) {
                        $error = 'Terjadi kesalahan: ' . $e->getMessage();
                    }
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $username = clean_input($_POST['username']);
                $email = clean_input($_POST['email']);
                $full_name = clean_input($_POST['full_name']);
                $role = $_POST['role'];
                $password = $_POST['password'];
                
                try {
                    // Check if username or email already exists (excluding current user)
                    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                    $stmt->execute([$username, $email, $id]);
                    
                    if ($stmt->fetch()) {
                        $error = 'Username atau email sudah digunakan!';
                    } else {
                        // Update user
                        if (!empty($password)) {
                            // Update with new password
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, role = ?, password = ?, updated_at = NOW() WHERE id = ?");
                            $stmt->execute([$username, $email, $full_name, $role, $hashed_password, $id]);
                        } else {
                            // Update without changing password
                            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, role = ?, updated_at = NOW() WHERE id = ?");
                            $stmt->execute([$username, $email, $full_name, $role, $id]);
                        }
                        $success = 'Pengguna berhasil diperbarui!';
                    }
                } catch(PDOException $e) {
                    $error = 'Terjadi kesalahan: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                // Prevent deleting self
                if ($id == $_SESSION['user_id']) {
                    $error = 'Tidak dapat menghapus akun sendiri!';
                } else {
                    try {
                        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$id]);
                        $success = 'Pengguna berhasil dihapus!';
                    } catch(PDOException $e) {
                        $error = 'Terjadi kesalahan: ' . $e->getMessage();
                    }
                }
                break;
                
            case 'toggle_status':
                $id = (int)$_POST['id'];
                
                // Prevent toggling self
                if ($id == $_SESSION['user_id']) {
                    $error = 'Tidak dapat mengubah status akun sendiri!';
                } else {
                    try {
                        // For simplicity, we'll toggle between 'user' and 'banned'
                        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
                        $stmt->execute([$id]);
                        $user = $stmt->fetch();
                        
                        if ($user) {
                            $new_role = $user['role'] === 'banned' ? 'user' : 'banned';
                            $stmt = $conn->prepare("UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?");
                            $stmt->execute([$new_role, $id]);
                            $success = 'Status pengguna berhasil diperbarui!';
                        }
                    } catch(PDOException $e) {
                        $error = 'Terjadi kesalahan: ' . $e->getMessage();
                    }
                }
                break;
        }
    }
}

// Get all users
$stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll();

// Get user statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$total_users = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
$stmt->execute();
$admin_count = $stmt->fetch()['admin_count'];

$stmt = $conn->prepare("SELECT COUNT(*) as user_count FROM users WHERE role = 'user'");
$stmt->execute();
$user_count = $stmt->fetch()['user_count'];

$stmt = $conn->prepare("SELECT COUNT(*) as banned_count FROM users WHERE role = 'banned'");
$stmt->execute();
$banned_count = $stmt->fetch()['banned_count'];

// Get user for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $edit_user = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - <?php echo APP_NAME; ?></title>
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
                        <a class="nav-link active" href="manage_users.php">Kelola Pengguna</a>
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
            <h2><i class="fas fa-users text-success me-2"></i>Kelola Pengguna</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-user-plus me-2"></i>Tambah Pengguna
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h4><?php echo $total_users; ?></h4>
                        <p>Total Pengguna</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4><?php echo $admin_count; ?></h4>
                        <p>Admin</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h4><?php echo $user_count; ?></h4>
                        <p>User Biasa</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h4><?php echo $banned_count; ?></h4>
                        <p>Dibanned</p>
                    </div>
                </div>
            </div>
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

        <!-- Users Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo $user['username']; ?></div>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                    <small class="text-muted">(Anda)</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $user['full_name']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td>
                                        <?php
                                        $badge_class = match($user['role']) {
                                            'admin' => 'bg-danger',
                                            'user' => 'bg-primary',
                                            'banned' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-warning" onclick="editUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button class="btn btn-sm btn-<?php echo $user['role'] === 'banned' ? 'success' : 'secondary'; ?>" onclick="toggleStatus(<?php echo $user['id']; ?>)" title="<?php echo $user['role'] === 'banned' ? 'Unban User' : 'Ban User'; ?>">
                                                    <i class="fas fa-<?php echo $user['role'] === 'banned' ? 'check' : 'ban'; ?>"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required minlength="3">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            <small class="text-muted">Minimal 6 karakter</small>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Tambah Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editFullName" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="editFullName" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRole" class="form-label">Role</label>
                            <select class="form-select" id="editRole" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                                <option value="banned">Banned</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Password (kosongkan jika tidak diubah)</label>
                            <input type="password" class="form-control" id="editPassword" name="password">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Update Pengguna</button>
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
                    <h5 class="modal-title">Hapus Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus pengguna ini? Semua ulasan yang terkait juga akan dihapus.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toggle Status Modal -->
    <div class="modal fade" id="toggleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Status Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="id" id="toggleId">
                    <div class="modal-body">
                        <p id="toggleMessage">Apakah Anda yakin ingin mengubah status pengguna ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Ya, Ubah Status</button>
                    </div>
                </form>
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
    <script>
        function editUser(id) {
            // Fetch user data and populate form
            fetch(`get_user.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editId').value = data.id;
                    document.getElementById('editUsername').value = data.username;
                    document.getElementById('editEmail').value = data.email;
                    document.getElementById('editFullName').value = data.full_name;
                    document.getElementById('editRole').value = data.role;
                    
                    new bootstrap.Modal(document.getElementById('editModal')).show();
                });
        }

        function deleteUser(id) {
            document.getElementById('deleteId').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function toggleStatus(id) {
            document.getElementById('toggleId').value = id;
            new bootstrap.Modal(document.getElementById('toggleModal')).show();
        }
    </script>
</body>
</html>
