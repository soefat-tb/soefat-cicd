<?php
// Aktifkan mode keamanan
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

include 'config/database.php';

// Improved security with prepared statements
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Session management
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Cek login dengan validasi tambahan
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || !in_array($_SESSION['username'], ['admin1', 'sudo'])) {
    header("Location: /login.php");
    exit();
}

// Handle deletion with prepared statements
if (isset($_GET['delete']) && isset($_GET['csrf_token']) && $_GET['csrf_token'] === $_SESSION['csrf_token']) {
    $id = intval($_GET['delete']);
    $stmt = $koneksi->prepare("DELETE FROM siswa WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']."?deleted=success");
    exit();
}

// Handle form submissions with prepared statements
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['operation'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }
    
    $operation = validateInput($_POST['operation']);
    $nis = validateInput($_POST['nis']);
    $nama = validateInput($_POST['nama']);
    $kelas = validateInput($_POST['kelas']);
    $nomor_telepon = validateInput($_POST['nomor_telepon']);
    $tingkatan = validateInput($_POST['tingkatan']);
    $email = validateInput($_POST['email']);
    $status = validateInput($_POST['status']); 

    $errors = [];
    
    // Validation
    if (empty($nis) || empty($nama) || empty($kelas) || empty($nomor_telepon) || empty($email)) {
        $errors[] = "Semua field harus diisi!";
    } 
    if (!preg_match("/^[0-9]{8,11}$/", $nis)) {
        $errors[] = "NIS harus terdiri dari 8 hingga 11 digit angka!";
    } 
    if (!preg_match("/^[A-Za-z\s]+$/", $nama)) {
        $errors[] = "Nama hanya boleh berisi huruf!";
    } 
    if (!preg_match("/^[0-9]{11,14}$/", $nomor_telepon)) {
        $errors[] = "Nomor telepon harus terdiri dari 11 hingga 14 digit!";
    } 
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid!";
    }
    if (!in_array($status, ['Aktif', 'Tidak Aktif', 'Purna'])) {
        $errors[] = "Status tidak valid!";
    }

    if (empty($errors)) {
        if ($operation === 'add') {
            $stmt = $koneksi->prepare("INSERT INTO siswa (nis, nama, kelas, nomor_telepon, tingkatan, email, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $nis, $nama, $kelas, $nomor_telepon, $tingkatan, $email, $status);
            $stmt->execute();
            $stmt->close();
            header("Location: ".$_SERVER['PHP_SELF']."?added=success");
            exit();
        } elseif ($operation === 'edit') {
            $id = intval($_POST['id']);
            $stmt = $koneksi->prepare("UPDATE siswa SET nis=?, nama=?, kelas=?, nomor_telepon=?, tingkatan=?, email=?, status=? WHERE id=?");
            $stmt->bind_param("sssssssi", $nis, $nama, $kelas, $nomor_telepon, $tingkatan, $email, $status, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: ".$_SERVER['PHP_SELF']."?updated=success");
            exit();
        }
    }
}

// Improved search with prepared statements
$search = '';
if (isset($_POST['search'])) {
    $search = validateInput($_POST['search']);
    $search_param = "%$search%";
    $stmt = $koneksi->prepare("SELECT * FROM siswa WHERE nama LIKE ? OR nis LIKE ? OR email LIKE ?");
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $koneksi->query("SELECT * FROM siswa ORDER BY nama ASC");
}

// Data retrieval for edit with prepared statements
$editData = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    $stmt = $koneksi->prepare("SELECT * FROM siswa WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get notification message type
$notification = '';
$notificationType = '';

if (isset($_GET['deleted']) && $_GET['deleted'] == 'success') {
    $notification = 'Data siswa berhasil dihapus.';
    $notificationType = 'success';
} elseif (isset($_GET['added']) && $_GET['added'] == 'success') {
    $notification = 'Data siswa berhasil ditambahkan.';
    $notificationType = 'success';
} elseif (isset($_GET['updated']) && $_GET['updated'] == 'success') {
    $notification = 'Data siswa berhasil diperbarui.';
    $notificationType = 'success';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Sistem Manajemen Data Siswa - Platform untuk mengelola data siswa secara efisien">
    <meta name="theme-color" content="#2c8030">
    <title>Data Siswa - Soefat</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c8030;
            --secondary-color: #4a7c59;
            --accent-color: #78c679;
            --background-light: #f8f9fa;
            --text-dark: #2c3e50;
            --card-shadow: 0 4px 12px rgba(44, 128, 48, 0.1);
            --card-default-bg: rgba(108, 117, 125, 0.1);
            --success-color: #28a745;
            --danger-color: #dc3545;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-light);
            color: var(--text-dark);
            min-height: 100vh;
            padding-bottom: 70px;
            overscroll-behavior-y: none;
            -webkit-tap-highlight-color: transparent;
            overflow-x: hidden;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.8rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            min-height: 50px;
        }

        .header .logo {
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .header .logo i {
            margin-right: 6px;
            font-size: 1.3rem;
        }

        .header .actions {
            display: flex;
            align-items: center;
        }

        .header .actions .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s;
        }

        .header .actions .user-avatar:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .header .actions .user-avatar i {
            font-size: 1rem;
        }

        /* Main Content */
        .main-content {
            padding: 70px 0.5rem 70px 0.5rem;
            max-width: 100%;
            margin: 0 auto;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card-body {
            padding: 1rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.5rem;
            font-size: 0.9rem;
            border: 1px solid var(--border-color);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44,128,48,0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Search Bar */
        .search-container {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-container .form-control {
            padding-left: 2.5rem;
            border-radius: 50px;
        }

        .search-container i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 0.5rem 0;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            border-top: 1px solid #e9ecef;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #6c757d;
            font-size: 0.6rem;
            font-weight: 500;
            transition: all 0.3s;
            padding: 0.3rem;
            border-radius: 8px;
            min-width: 45px;
        }

        .nav-item.active {
            color: var(--primary-color);
            background: rgba(44, 128, 48, 0.1);
        }

        .nav-item:hover {
            color: var(--primary-color);
            background: rgba(44, 128, 48, 0.05);
        }

        .nav-item i {
            font-size: 1.1rem;
            margin-bottom: 0.2rem;
            transition: transform 0.3s;
        }

        .nav-item.active i {
            transform: scale(1.1);
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            animation: fadeIn 0.3s;
            max-width: 90%;
        }

        .notification-success {
            background-color: var(--success-color);
            color: white;
        }

        .notification-error {
            background-color: var(--danger-color);
            color: white;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translate(-50%, -20px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }

        /* Badges */
        .badge-bantara {
            background-color: #FFD166;
            color: #333;
        }

        .badge-calon-bantara {
            background-color: #A3BFFA;
            color: #333;
        }

        .badge-laksana {
            background-color: #06D6A0;
            color: #333;
        }

        /* Modal */
        .modal-content {
            border-radius: 12px;
            border: none;
            max-height: 85vh;
            overflow-y: auto;
            margin: 0.5rem;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
            padding: 0.75rem 1rem;
        }

        .modal-body {
            padding: 1rem;
            font-size: 0.9rem;
        }

        /* View Modal Specific Styles */
        .view-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1rem;
            gap: 0.5rem;
        }

        .view-profile-icon {
            width: 60px;
            height: 60px;
            background-color: var(--background-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
        }

        .view-profile-icon i {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .view-profile h4 {
            font-size: 1.1rem;
            margin: 0;
            text-align: center;
            word-break: break-word;
        }

        .view-table {
            font-size: 0.85rem;
        }

        .view-table td:first-child {
            width: 40%;
            font-weight: 500;
            vertical-align: top;
        }

        .view-table td:last-child {
            word-break: break-word;
        }

        .modal-footer {
            padding: 0.75rem;
            justify-content: space-between;
        }

        /* Responsive Adjustments */
        @media (min-width: 768px) {
            .main-content {
                padding: 70px 1rem 70px 1rem;
                max-width: 900px;
            }

            .header .logo {
                font-size: 1.2rem;
            }

            .nav-item {
                font-size: 0.7rem;
            }

            .nav-item i {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 576px) {
            .modal-dialog {
                margin: 0.25rem;
                max-width: 100%;
            }

            .modal-content {
                max-height: 90vh;
            }

            .card-body {
                padding: 0.75rem;
            }

            .btn {
                font-size: 0.85rem;
                padding: 0.4rem 0.8rem;
            }

            .bottom-nav {
                padding: 0.25rem 0;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 2px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 3000;
            justify-content: center;
            align-items: center;
        }

        /* Ripple Effect */
        .ripple-container {
            position: relative;
            overflow: hidden;
        }

        .ripple {
            position: absolute;
            background: rgba(44, 128, 48, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple-animation 0.6s ease-out;
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to {
                transform: scale(2);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="logo">
            <i class="fas fa-shield-alt"></i>
            Soefat Admin
        </div>
        <div class="actions">
            <div class="user-avatar ripple-container">
                <i class="fas fa-user"></i>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <?php if (!empty($notification)): ?>
            <div class="notification notification-<?php echo $notificationType; ?>">
                <i class="fas fa-<?php echo $notificationType == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $notification; ?>
            </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <div class="search-container">
                <form method="POST">
                    <i class="fas fa-search"></i>
                    <input class="form-control" type="search" name="search" placeholder="Cari NIS/Nama/Email" value="<?php echo $search; ?>">
                </form>
            </div>

            <!-- Add Button -->
            <button class="btn btn-primary w-100 mb-3" id="addSiswaBtn">
                <i class="fas fa-plus-circle me-2"></i> Tambah Siswa Baru
            </button>

            <!-- Siswa List -->
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card" data-id="<?php echo $row['id']; ?>">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1"><?php echo $row['nama']; ?></h5>
                            <p class="mb-1 text-muted small">NIS: <?php echo $row['nis']; ?></p>
                            <p class="mb-1 text-muted small">Kelas: <?php echo $row['kelas']; ?></p>
                            <span class="badge <?php echo $row['tingkatan'] == 'Bantara' ? 'badge-bantara' : ($row['tingkatan'] == 'Calon Bantara' ? 'badge-calon-bantara' : 'badge-laksana'); ?>">
                                <?php echo $row['tingkatan']; ?>
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary view-btn" data-id="<?php echo $row['id']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-info edit-btn" data-id="<?php echo $row['id']; ?>">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $row['id']; ?>" data-csrf="<?php echo $_SESSION['csrf_token']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-search text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-2">Tidak ada data siswa</h5>
                    <p class="text-muted">Tambahkan data baru atau ubah kata kunci pencarian.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item ripple-container">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="data_siswa.php" class="nav-item active ripple-container">
            <i class="fas fa-users"></i>
            <span>Data Siswa</span>
        </a>
        <a href="../export-siswa.php" class="nav-item ripple-container">
            <i class="fas fa-file-excel"></i>
            <span>Export</span>
        </a>
        <a href="#" id="refreshData" class="nav-item ripple-container">
            <i class="fas fa-sync-alt"></i>
            <span>Refresh</span>
        </a>
    </nav>

    <!-- Modal Tambah/Edit Siswa -->
    <div class="modal fade" id="siswaModal" tabindex="-1" aria-labelledby="siswaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="siswaModalLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        <span id="modalTitle">Tambah Siswa Baru</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="siswaForm" method="POST" action="">
                        <input type="hidden" name="operation" id="operation" value="add">
                        <input type="hidden" name="id" id="siswaId" value="">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="nis" class="form-label">NIS</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input type="text" class="form-control" id="nis" name="nis" placeholder="Nomor Induk Siswa" required
                                       value="<?php echo isset($editData) ? $editData['nis'] : ''; ?>">
                            </div>
                            <div class="form-text">NIS harus terdiri dari 8-11 digit angka.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama lengkap siswa" required
                                       value="<?php echo isset($editData) ? $editData['nama'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="kelas" class="form-label">Kelas</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <input type="text" class="form-control" id="kelas" name="kelas" placeholder="Contoh: X IPA 1" required
                                       value="<?php echo isset($editData) ? $editData['kelas'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" class="form-control" id="nomor_telepon" name="nomor_telepon" placeholder="Nomor telepon aktif" required
                                       value="<?php echo isset($editData) ? $editData['nomor_telepon'] : ''; ?>">
                            </div>
                            <div class="form-text">Nomor telepon harus terdiri dari 11-14 digit angka.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tingkatan" class="form-label">Tingkatan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-award"></i></span>
                                <select class="form-select" id="tingkatan" name="tingkatan" required>
                                    <option value="" disabled selected>Pilih tingkatan</option>
                                    <option value="Calon Bantara" <?php echo (isset($editData) && $editData['tingkatan'] == 'Calon Bantara') ? 'selected' : ''; ?>>Calon Bantara</option>
                                    <option value="Bantara" <?php echo (isset($editData) && $editData['tingkatan'] == 'Bantara') ? 'selected' : ''; ?>>Bantara</option>
                                    <option value="Laksana" <?php echo (isset($editData) && $editData['tingkatan'] == 'Laksana') ? 'selected' : ''; ?>>Laksana</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-hourglass"></i></span>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="" disabled selected>Pilih Status</option>
                                    <option value="Aktif" <?php echo (isset($editData) && $editData['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Tidak Aktif" <?php echo (isset($editData) && $editData['status'] == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                    <option value="Purna" <?php echo (isset($editData) && $editData['status'] == 'Purna') ? 'selected' : ''; ?>>Purna</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email aktif" required
                                       value="<?php echo isset($editData) ? $editData['email'] : ''; ?>">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="saveSiswa">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal View Siswa -->
    <div class="modal fade" id="viewSiswaModal" tabindex="-1" aria-labelledby="viewSiswaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSiswaModalLabel">
                        <i class="fas fa-info-circle me-2"></i> Detail Siswa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="view-profile">
                        <div class="view-profile-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4 id="viewNama">Nama Siswa</h4>
                        <span id="viewTingkatan" class="badge badge-laksana">Tingkatan</span>
                    </div>
                    <table class="table table-borderless view-table">
                        <tbody>
                            <tr>
                                <td><i class="fas fa-id-card me-2 text-primary"></i> NIS</td>
                                <td id="viewNIS">-</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-building me-2 text-primary"></i> Kelas</td>
                                <td id="viewKelas">-</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-phone me-2 text-primary"></i> Nomor Telepon</td>
                                <td id="viewTelepon">-</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-envelope me-2 text-primary"></i> Email</td>
                                <td id="viewEmail">-</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-check-circle me-2 text-primary"></i> Status</td>
                                <td id="viewStatus">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times-circle me-1"></i> Tutup
                    </button>
                    <button type="button" class="btn btn-primary" id="editFromView">
                        <i class="fas fa-pencil-alt me-1"></i> Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const siswaModal = new bootstrap.Modal(document.getElementById('siswaModal'));
                const viewSiswaModal = new bootstrap.Modal(document.getElementById('viewSiswaModal'));
                const addSiswaBtn = document.getElementById('addSiswaBtn');
                const refreshData = document.getElementById('refreshData');
                const saveSiswa = document.getElementById('saveSiswa');
                const siswaForm = document.getElementById('siswaForm');
                const modalTitle = document.getElementById('modalTitle');

                // Add new student
                addSiswaBtn.addEventListener('click', function() {
                    try {
                        document.getElementById('operation').value = 'add';
                        modalTitle.textContent = 'Tambah Siswa Baru';
                        siswaForm.reset();
                        siswaForm.querySelector('#siswaId').value = '';
                        removeAllErrorMessages();
                        siswaModal.show();
                    } catch (error) {
                        console.error('Error in addSiswaBtn:', error);
                        showAlert('error', 'Gagal', 'Terjadi kesalahan saat membuka form.');
                    }
                });

                // Refresh data
                refreshData.addEventListener('click', function(e) {
                    try {
                        e.preventDefault();
                        showLoading();
                        window.location.href = window.location.pathname;
                    } catch (error) {
                        console.error('Error in refreshData:', error);
                        showAlert('error', 'Gagal', 'Terjadi kesalahan saat me-refresh data.');
                    }
                });

                // Save student data
                saveSiswa.addEventListener('click', function() {
                    try {
                        if (validateForm()) {
                            showLoading();
                            siswaForm.submit();
                        }
                    } catch (error) {
                        console.error('Error in saveSiswa:', error);
                        showAlert('error', 'Gagal', 'Terjadi kesalahan saat menyimpan data.');
                    }
                });

                // Form validation
                function validateForm() {
                    try {
                        const nis = document.getElementById('nis').value;
                        const nama = document.getElementById('nama').value;
                        const kelas = document.getElementById('kelas').value;
                        const nomor_telepon = document.getElementById('nomor_telepon').value;
                        const email = document.getElementById('email').value;
                        const tingkatan = document.getElementById('tingkatan').value;
                        
                        removeAllErrorMessages();
                        let isValid = true;
                        
                        if (!nis || !nama || !kelas || !nomor_telepon || !email || !tingkatan) {
                            showAlert('error', 'Validasi Gagal', 'Semua field harus diisi!');
                            isValid = false;
                        }
                        
                        if (!/^[0-9]{8,11}$/.test(nis)) {
                            addErrorMessage('nis', 'NIS harus terdiri dari 8 hingga 11 digit angka!');
                            isValid = false;
                        }
                        
                        if (!/^[A-Za-z\s]+$/.test(nama)) {
                            addErrorMessage('nama', 'Nama hanya boleh berisi huruf!');
                            isValid = false;
                        }
                        
                        if (!/^[0-9]{11,14}$/.test(nomor_telepon)) {
                            addErrorMessage('nomor_telepon', 'Nomor telepon harus terdiri dari 11 hingga 14 digit angka!');
                            isValid = false;
                        }
                        
                        if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})$/.test(email)) {
                            addErrorMessage('email', 'Format email tidak valid.');
                            isValid = false;
                        }
                        
                        return isValid;
                    } catch (error) {
                        console.error('Error in validateForm:', error);
                        showAlert('error', 'Gagal', 'Terjadi kesalahan saat validasi.');
                        return false;
                    }
                }

                function addErrorMessage(inputId, message) {
                    try {
                        const input = document.getElementById(inputId);
                        input.classList.add('is-invalid');
                        const invalidFeedback = document.createElement('div');
                        invalidFeedback.className = 'invalid-feedback';
                        invalidFeedback.textContent = message;
                        input.parentNode.appendChild(invalidFeedback);
                    } catch (error) {
                        console.error('Error in addErrorMessage:', error);
                    }
                }

                function removeAllErrorMessages() {
                    try {
                        document.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
                        document.querySelectorAll('.invalid-feedback').forEach(feedback => feedback.remove());
                    } catch (error) {
                        console.error('Error in removeAllErrorMessages:', error);
                    }
                }

                // Edit student
                document.querySelectorAll('.edit-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        try {
                            showLoading();
                            window.location.href = window.location.pathname + "?edit_id=" + this.getAttribute('data-id');
                        } catch (error) {
                            console.error('Error in edit-btn:', error);
                            showAlert('error', 'Gagal', 'Terjadi kesalahan saat mengedit data.');
                        }
                    });
                });

                // Show edit modal if edit_id is present
                <?php if (isset($editData)): ?>
                window.addEventListener('load', function() {
                    try {
                        document.getElementById('operation').value = 'edit';
                        document.getElementById('siswaId').value = '<?php echo $editData['id']; ?>';
                        modalTitle.textContent = 'Edit Data Siswa';
                        siswaModal.show();
                    } catch (error) {
                        console.error('Error in edit modal load:', error);
                        showAlert('error', 'Gagal', 'Terjadi kesalahan saat membuka form edit.');
                    }
                });
                <?php endif; ?>

                // View student details
                document.querySelectorAll('.view-btn').forEach(button => {
                    button.addEventListener('click', function(e) {
                        try {
                            e.preventDefault();
                            const id = this.getAttribute('data-id');
                            const card = this.closest('.card');
                            const nama = card.querySelector('h5').textContent;
                            const nis = card.querySelector('p:nth-child(2)').textContent.replace('NIS: ', '');
                            const kelas = card.querySelector('p:nth-child(3)').textContent.replace('Kelas: ', '');
                            const tingkatan = card.querySelector('.badge').textContent;

                            // Populate basic info
                            document.getElementById('viewNIS').textContent = nis;
                            document.getElementById('viewNama').textContent = nama;
                            document.getElementById('viewKelas').textContent = kelas;
                            const tingkatanEl = document.getElementById('viewTingkatan');
                            tingkatanEl.textContent = tingkatan;
                            tingkatanEl.className = `badge ${tingkatan === 'Bantara' ? 'badge-bantara' : tingkatan === 'Calon Bantara' ? 'badge-calon-bantara' : 'badge-laksana'}`;
                            document.getElementById('editFromView').setAttribute('data-id', id);

                            // Show modal with basic info
                            viewSiswaModal.show();

                            // Focus modal for accessibility
                            document.getElementById('viewSiswaModal').focus();

                            // Fetch additional details
                            fetch(`api-siswa.php?action=getById&id=${id}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success && data.data) {
                                        document.getElementById('viewTelepon').textContent = data.data.nomor_telepon || '-';
                                        document.getElementById('viewEmail').textContent = data.data.email || '-';
                                        document.getElementById('viewStatus').innerHTML = `<span class="badge bg-${data.data.status === 'Aktif' ? 'success' : data.data.status === 'Tidak Aktif' ? 'danger' : 'primary'}">${data.data.status}</span>`;
                                    } else {
                                        showAlert('error', 'Gagal', data.message || 'Data siswa tidak ditemukan.');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error fetching student data:', error);
                                    document.getElementById('viewTelepon').textContent = '-';
                                    document.getElementById('viewEmail').textContent = '-';
                                    document.getElementById('viewStatus').textContent = '-';
                                });
                        } catch (error) {
                            console.error('Error in view-btn:', error);
                            showAlert('error', 'Gagal', 'Terjadi kesalahan saat menampilkan data.');
                        }
                    });
                });

                // Edit from view modal
                document.getElementById('editFromView').addEventListener('click', function() {
                    try {
                        viewSiswaModal.hide();
                        showLoading();
                        window.location.href = window.location.pathname + "?edit_id=" + this.getAttribute('data-id');
                    } catch (error) {
                        console.error('Error in editFromView:', error);
                        showAlert('error', 'Gagal', 'Terjadi kesalahan saat mengedit data.');
                    }
                });

                // Delete student
                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        try {
                            const id = this.getAttribute('data-id');
                            const csrf = this.getAttribute('data-csrf');
                            Swal.fire({
                                title: 'Konfirmasi Hapus',
                                text: 'Anda yakin ingin menghapus data siswa ini?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#dc3545',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Ya, Hapus!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    showLoading();
                                    window.location.href = window.location.pathname + "?delete=" + id + "&csrf_token=" + csrf;
                                }
                            });
                        } catch (error) {
                            console.error('Error in delete-btn:', error);
                            showAlert('error', 'Gagal', 'Terjadi kesalahan saat menghapus data.');
                        }
                    });
                });

                // Show loading
                function showLoading() {
                    try {
                        document.querySelector('.loading-overlay').style.display = 'flex';
                    } catch (error) {
                        console.error('Error in showLoading:', error);
                    }
                }

                // Show alert
                function showAlert(icon, title, text) {
                    try {
                        Swal.fire({
                            icon: icon,
                            title: title,
                            text: text,
                            confirmButtonColor: '#2c8030'
                        });
                    } catch (error) {
                        console.error('Error in showAlert:', error);
                    }
                }

                // Auto-hide notifications
                setTimeout(() => {
                    try {
                        const notifications = document.querySelectorAll('.notification');
                        notifications.forEach(notification => notification.remove());
                    } catch (error) {
                        console.error('Error in notification timeout:', error);
                    }
                }, 5000);

                // Ripple effect for nav items
                const rippleContainers = document.querySelectorAll('.ripple-container');
                rippleContainers.forEach(container => {
                    container.addEventListener('click', (e) => {
                        const ripple = document.createElement('span');
                        const rect = container.getBoundingClientRect();
                        const size = Math.max(rect.width, rect.height);
                        const x = e.clientX - rect.left - size / 2;
                        const y = e.clientY - rect.top - size / 2;

                        ripple.style.width = ripple.style.height = `${size}px`;
                        ripple.style.left = `${x}px`;
                        ripple.style.top = `${y}px`;
                        ripple.classList.add('ripple');

                        container.appendChild(ripple);

                        setTimeout(() => ripple.remove(), 600);
                    });
                });

                // Active nav item
                const currentPage = window.location.pathname.split('/').pop();
                const navItems = document.querySelectorAll('.nav-item');
                navItems.forEach(item => {
                    const href = item.getAttribute('href');
                    if (href && href.includes(currentPage)) {
                        navItems.forEach(nav => nav.classList.remove('active'));
                        item.classList.add('active');
                    }
                });
            } catch (error) {
                console.error('Error in DOMContentLoaded:', error);
                showAlert('error', 'Gagal', 'Terjadi kesalahan saat memuat halaman.');
            }
        });
    </script>
</body>
</html>