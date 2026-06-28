<?php
// Aktifkan mode keamanan
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

session_start();

// Pengecekan login untuk username admin1 dan sudo
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || !in_array($_SESSION['username'], ['admin1', 'sudo'])) {
    header("Location: /login.php");
    exit();
}

// Fungsi validasi input
function sanitizeInput($input) {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

// Proses tambah jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_jadwal'])) {
    include 'config/database.php';
    try {
        $hari = sanitizeInput($_POST['hari']);
        $waktu = sanitizeInput($_POST['waktu']);
        $judul = sanitizeInput($_POST['judul']);
        $lokasi = sanitizeInput($_POST['lokasi']);
        $pembina = sanitizeInput($_POST['pembina']);
        $pakaian = sanitizeInput($_POST['pakaian'] ?? '');
        $note = sanitizeInput($_POST['note'] ?? '');

        // Validasi input
        $errors = [];
        if (empty($hari)) $errors[] = "Hari wajib diisi.";
        if (empty($waktu)) $errors[] = "Waktu wajib diisi.";
        if (empty($judul)) $errors[] = "Judul wajib diisi.";
        if (empty($lokasi)) $errors[] = "Lokasi wajib diisi.";
        if (empty($pembina)) $errors[] = "Pembina wajib diisi.";
        if (strlen($judul) > 100) $errors[] = "Judul terlalu panjang (maks. 100 karakter).";
        if (strlen($lokasi) > 100) $errors[] = "Lokasi terlalu panjang (maks. 100 karakter).";
        if (strlen($pembina) > 50) $errors[] = "Pembina terlalu panjang (maks. 50 karakter).";
        if (strlen($pakaian) > 100) $errors[] = "Pakaian terlalu panjang (maks. 100 karakter).";
        if (strlen($note) > 500) $errors[] = "Catatan terlalu panjang (maks. 500 karakter).";

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: " . $_SERVER['PHP_SELF'] . "?error=1");
            exit();
        }

        $stmt = $koneksi->prepare("INSERT INTO jadwal (hari, waktu, judul, lokasi, pembina, pakaian, note) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) throw new Exception("Gagal menyiapkan query: " . $koneksi->error);
        $stmt->bind_param("sssssss", $hari, $waktu, $judul, $lokasi, $pembina, $pakaian, $note);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Jadwal berhasil ditambahkan!";
            header("Location: " . $_SERVER['PHP_SELF']);
        } else {
            throw new Exception("Gagal menambahkan jadwal: " . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=1");
        exit();
    }
}

// Proses edit jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_jadwal'])) {
    include 'config/database.php';
    try {
        $id = intval($_POST['id']);
        $hari = sanitizeInput($_POST['hari']);
        $waktu = sanitizeInput($_POST['waktu']);
        $judul = sanitizeInput($_POST['judul']);
        $lokasi = sanitizeInput($_POST['lokasi']);
        $pembina = sanitizeInput($_POST['pembina']);
        $pakaian = sanitizeInput($_POST['pakaian'] ?? '');
        $note = sanitizeInput($_POST['note'] ?? '');

        // Validasi input
        $errors = [];
        if (empty($hari)) $errors[] = "Hari wajib diisi.";
        if (empty($waktu)) $errors[] = "Waktu wajib diisi.";
        if (empty($judul)) $errors[] = "Judul wajib diisi.";
        if (empty($lokasi)) $errors[] = "Lokasi wajib diisi.";
        if (empty($pembina)) $errors[] = "Pembina wajib diisi.";
        if (strlen($judul) > 100) $errors[] = "Judul terlalu panjang (maks. 100 karakter).";
        if (strlen($lokasi) > 100) $errors[] = "Lokasi terlalu panjang (maks. 100 karakter).";
        if (strlen($pembina) > 50) $errors[] = "Pembina terlalu panjang (maks. 50 karakter).";
        if (strlen($pakaian) > 100) $errors[] = "Pakaian terlalu panjang (maks. 100 karakter).";
        if (strlen($note) > 500) $errors[] = "Catatan terlalu panjang (maks. 500 karakter).";

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: " . $_SERVER['PHP_SELF'] . "?error=1");
            exit();
        }

        $stmt = $koneksi->prepare("UPDATE jadwal SET hari = ?, waktu = ?, judul = ?, lokasi = ?, pembina = ?, pakaian = ?, note = ? WHERE id = ?");
        if ($stmt === false) throw new Exception("Gagal menyiapkan query: " . $koneksi->error);
        $stmt->bind_param("sssssssi", $hari, $waktu, $judul, $lokasi, $pembina, $pakaian, $note, $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Jadwal berhasil diupdate!";
            header("Location: " . $_SERVER['PHP_SELF']);
        } else {
            throw new Exception("Gagal mengupdate jadwal: " . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=1");
        exit();
    }
}

// Proses hapus jadwal
if (isset($_GET['hapus_jadwal'])) {
    include 'config/database.php';
    try {
        $id = intval($_GET['hapus_jadwal']);
        $stmt = $koneksi->prepare("DELETE FROM jadwal WHERE id = ?");
        if ($stmt === false) throw new Exception("Gagal menyiapkan query: " . $koneksi->error);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Jadwal berhasil dihapus!";
            header("Location: " . $_SERVER['PHP_SELF']);
        } else {
            throw new Exception("Gagal menghapus jadwal: " . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=1");
        exit();
    }
}

// Ambil daftar jadwal
include 'config/database.php';
$query_jadwal = mysqli_query($koneksi, "SELECT * FROM jadwal ORDER BY hari, waktu ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Jadwal Pramuka - Soefat</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2c8030;
            --secondary-color: #4a7c59;
            --accent-color: #78c679;
            --background-light: #f8f9fa;
            --text-dark: #2c3e50;
            --card-shadow: 0 4px 12px rgba(44, 128, 48, 0.1);
            --card-default-bg: rgba(108, 117, 125, 0.1);
        }

        body {
            background-color: var(--background-light);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-size: 14px;
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
            flex: 1;
            padding: 70px 0.5rem 70px 0.5rem;
            max-width: 100%;
            margin: 0 auto;
        }

        .form-card, .jadwal-card {
            background: white;
            border-radius: 10px;
            padding: 1.2rem;
            box-shadow: var(--card-shadow);
            max-width: 450px;
            margin: 1rem auto;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.5s ease-in;
        }

        .form-card::before, .jadwal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 0.8rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.4rem;
            display: block;
            font-size: 0.9rem;
        }

        .form-control, .form-control-textarea {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 3rem;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: border-color 0.3s, box-shadow 0.3s;
            background: #fafafa;
        }

        .form-control-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-control:focus, .form-control-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 128, 48, 0.2);
            background: white;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            font-size: 1rem;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 5px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            border: none;
            justify-content: center;
            width: 100%;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(44, 128, 48, 0.3);
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
            border: none;
            padding: 0.5rem 1rem;
        }

        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-3px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-3px);
        }

        .alert {
            padding: 0.8rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .alert-success {
            background: rgba(44, 128, 48, 0.1);
            color: var(--primary-color);
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .alert i {
            margin-right: 0.5rem;
        }

        .error-list {
            list-style-type: disc;
            padding-left: 20px;
            margin: 0;
        }

        .jadwal-list {
            margin-top: 1.5rem;
        }

        .jadwal-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.8rem;
            background: var(--card-default-bg);
            transition: transform 0.3s;
        }

        .jadwal-item:hover {
            transform: translateY(-3px);
        }

        .jadwal-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            padding: 1.2rem;
            max-width: 400px;
            width: 90%;
            position: relative;
            animation: modalFadeIn 0.3s ease-in;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 1rem;
            cursor: pointer;
            color: var(--text-dark);
        }

        .btn-close:hover {
            color: var(--primary-color);
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

        /* Loading Animation */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .spinner {
            width: 30px;
            height: 30px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

        /* Responsive */
        @media (min-width: 768px) {
            .main-content {
                padding: 70px 1rem 70px 1rem;
                max-width: 900px;
            }

            .form-card, .jadwal-card {
                padding: 1.8rem;
                max-width: 500px;
                min-height: 400px;
                margin: 1.5rem auto;
            }

            .form-title {
                font-size: 1.5rem;
            }

            .form-control, .form-control-textarea {
                padding: 0.7rem 1.2rem 0.7rem 3.2rem;
                font-size: 1rem;
            }

            .input-group i {
                font-size: 1.2rem;
                left: 1.2rem;
            }

            .form-label {
                font-size: 1rem;
            }

            .alert {
                font-size: 1rem;
            }

            .btn-primary {
                padding: 0.7rem 1.5rem;
            }

            .jadwal-item {
                padding: 1.2rem;
            }
        }

        @media (max-width: 767px) {
            .form-card, .jadwal-card {
                padding: 1.2rem;
                margin: 1rem 0.5rem;
            }

            .form-control, .form-control-textarea {
                padding: 0.6rem 1rem 0.6rem 3rem;
                font-size: 0.9rem;
            }

            .input-group i {
                font-size: 1rem;
                left: 1rem;
            }

            .form-title {
                font-size: 1.3rem;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .alert {
                font-size: 0.9rem;
            }

            .btn-primary {
                padding: 0.6rem 1.2rem;
            }

            .jadwal-item {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .jadwal-actions {
                width: 100%;
                justify-content: center;
            }
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
    <!-- Loading -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
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
    <main class="main-content">
        <!-- Alerts -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?php if (is_array($_SESSION['errors'])): ?>
                <ul class="error-list">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <?= htmlspecialchars($_SESSION['error']) ?>
            <?php endif; ?>
            <?php unset($_SESSION['errors'], $_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="form-card">
            <h2 class="form-title">
                <i class="fas fa-calendar-plus"></i> Tambah Jadwal
            </h2>
            <form method="POST" id="tambahForm">
                <div class="form-group">
                    <label for="hari" class="form-label">Hari</label>
                    <div class="input-group">
                        <i class="fas fa-calendar-day"></i>
                        <input type="text" name="hari" class="form-control" id="hari" required maxlength="20" placeholder="contoh: Senin">
                    </div>
                </div>
                <div class="form-group">
                    <label for="waktu" class="form-label">Waktu</label>
                    <div class="input-group">
                        <i class="fas fa-clock"></i>
                        <input type="text" name="waktu" class="form-control" id="waktu" required maxlength="20" placeholder="contoh: 15.30 - 18.00">
                    </div>
                </div>
                <div class="form-group">
                    <label for="judul" class="form-label">Judul</label>
                    <div class="input-group">
                        <i class="fas fa-heading"></i>
                        <input type="text" name="judul" class="form-control" id="judul" required maxlength="100" placeholder="contoh: Latihan Reguler">
                    </div>
                </div>
                <div class="form-group">
                    <label for="lokasi" class="form-label">Lokasi</label>
                    <div class="input-group">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" name="lokasi" class="form-control" id="lokasi" required maxlength="100" placeholder="contoh: Lapangan Sekolah">
                    </div>
                </div>
                <div class="form-group">
                    <label for="pembina" class="form-label">Pembina</label>
                    <div class="input-group">
                        <i class="fas fa-user-tie"></i>
                        <input type="text" name="pembina" class="form-control" id="pembina" required maxlength="50" placeholder="contoh: Pak Budi">
                    </div>
                </div>
                <div class="form-group">
                    <label for="pakaian" class="form-label">Pakaian</label>
                    <div class="input-group">
                        <i class="fas fa-tshirt"></i>
                        <input type="text" name="pakaian" class="form-control" id="pakaian" maxlength="100" placeholder="contoh: Seragam Pramuka Lengkap">
                    </div>
                </div>
                <div class="form-group">
                    <label for="note" class="form-label">Catatan</label>
                    <div class="input-group">
                        <i class="fas fa-sticky-note"></i>
                        <textarea name="note" class="form-control form-control-textarea" id="note" maxlength="500" placeholder="Catatan tambahan"></textarea>
                    </div>
                </div>
                <button type="submit" name="tambah_jadwal" class="btn btn-primary ripple-container">
                    <i class="fas fa-save"></i> Tambah Jadwal
                </button>
            </form>
        </div>

        <!-- Daftar Jadwal -->
        <div class="jadwal-card">
            <h2 class="form-title">
                <i class="fas fa-list"></i> Daftar Jadwal
            </h2>
            <div class="jadwal-list">
                <?php while ($jadwal = mysqli_fetch_assoc($query_jadwal)): ?>
                    <div class="jadwal-item">
                        <div>
                            <strong><?= htmlspecialchars($jadwal['hari']) ?></strong> - <?= htmlspecialchars($jadwal['waktu']) ?><br>
                            <span><?= htmlspecialchars($jadwal['judul']) ?></span><br>
                            <small>Lokasi: <?= htmlspecialchars($jadwal['lokasi']) ?></small><br>
                            <small>Pembina: <?= htmlspecialchars($jadwal['pembina']) ?></small><br>
                            <small>Pakaian: <?= htmlspecialchars($jadwal['pakaian'] ?: 'Tidak ditentukan') ?></small><br>
                            <small>Catatan: <?= htmlspecialchars($jadwal['note'] ?: 'Tidak ada catatan') ?></small>
                        </div>
                        <div class="jadwal-actions">
                            <button type="button" class="btn btn-warning ripple-container" onclick="showModal('editModal<?= $jadwal['id'] ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?hapus_jadwal=<?= $jadwal['id'] ?>" class="btn btn-danger ripple-container" onclick="return confirmDelete()">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Modal untuk Edit -->
                    <div class="modal" id="editModal<?= $jadwal['id'] ?>">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Jadwal</h5>
                                <button type="button" class="btn-close" onclick="hideModal('editModal<?= $jadwal['id'] ?>')"><i class="fas fa-times"></i></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" id="editForm<?= $jadwal['id'] ?>">
                                    <input type="hidden" name="id" value="<?= $jadwal['id'] ?>">
                                    <div class="form-group">
                                        <label for="hari<?= $jadwal['id'] ?>" class="form-label">Hari</label>
                                        <div class="input-group">
                                            <i class="fas fa-calendar-day"></i>
                                            <input type="text" name="hari" class="form-control" id="hari<?= $jadwal['id'] ?>" value="<?= htmlspecialchars($jadwal['hari']) ?>" required maxlength="20">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="waktu<?= $jadwal['id'] ?>" class="form-label">Waktu</label>
                                        <div class="input-group">
                                            <i class="fas fa-clock"></i>
                                            <input type="text" name="waktu" class="form-control" id="waktu<?= $jadwal['id'] ?>" value="<?= htmlspecialchars($jadwal['waktu']) ?>" required maxlength="20" placeholder="contoh: 15.30 - 18.00">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="judul<?= $jadwal['id'] ?>" class="form-label">Judul</label>
                                        <div class="input-group">
                                            <i class="fas fa-heading"></i>
                                            <input type="text" name="judul" class="form-control" id="judul<?= $jadwal['id'] ?>" value="<?= htmlspecialchars($jadwal['judul']) ?>" required maxlength="100">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="lokasi<?= $jadwal['id'] ?>" class="form-label">Lokasi</label>
                                        <div class="input-group">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <input type="text" name="lokasi" class="form-control" id="lokasi<?= $jadwal['id'] ?>" value="<?= htmlspecialchars($jadwal['lokasi']) ?>" required maxlength="100">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="pembina<?= $jadwal['id'] ?>" class="form-label">Pembina</label>
                                        <div class="input-group">
                                            <i class="fas fa-user-tie"></i>
                                            <input type="text" name="pembina" class="form-control" id="pembina<?= $jadwal['id'] ?>" value="<?= htmlspecialchars($jadwal['pembina']) ?>" required maxlength="50">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="pakaian<?= $jadwal['id'] ?>" class="form-label">Pakaian</label>
                                        <div class="input-group">
                                            <i class="fas fa-tshirt"></i>
                                            <input type="text" name="pakaian" class="form-control" id="pakaian<?= $jadwal['id'] ?>" value="<?= htmlspecialchars($jadwal['pakaian'] ?? '') ?>" maxlength="100" placeholder="contoh: Seragam Pramuka Lengkap">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="note<?= $jadwal['id'] ?>" class="form-label">Catatan</label>
                                        <div class="input-group">
                                            <i class="fas fa-sticky-note"></i>
                                            <textarea name="note" class="form-control form-control-textarea" id="note<?= $jadwal['id'] ?>" maxlength="500"><?= htmlspecialchars($jadwal['note'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <button type="submit" name="edit_jadwal" class="btn btn-primary ripple-container">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item ripple-container">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="credential-add.php" class="nav-item ripple-container">
            <i class="fas fa-user-plus"></i>
            <span>User</span>
        </a>
        <a href="news-manager.php" class="nav-item ripple-container">
            <i class="fas fa-newspaper"></i>
            <span>Berita</span>
        </a>
        <a href="org-chart-manager.php" class="nav-item ripple-container">
            <i class="fas fa-sitemap"></i>
            <span>Bagan</span>
        </a>
        <a href="../logout.php" class="nav-item ripple-container">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Loading animation
            const showLoading = () => {
                document.getElementById('loading').style.display = 'flex';
            };

            const hideLoading = () => {
                document.getElementById('loading').style.display = 'none';
            };

            // Form submission (Tambah)
            const tambahForm = document.getElementById('tambahForm');
            if (tambahForm) {
                tambahForm.addEventListener('submit', (e) => {
                    const hari = document.getElementById('hari').value;
                    const waktu = document.getElementById('waktu').value;
                    const judul = document.getElementById('judul').value;
                    const lokasi = document.getElementById('lokasi').value;
                    const pembina = document.getElementById('pembina').value;
                    const pakaian = document.getElementById('pakaian').value;
                    const note = document.getElementById('note').value;

                    if (!hari || !waktu || !judul || !lokasi || !pembina) {
                        e.preventDefault();
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-error';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Hari, waktu, judul, lokasi, dan pembina wajib diisi!';
                        tambahForm.prepend(alertDiv);
                        setTimeout(() => alertDiv.remove(), 3000);
                        return;
                    }

                    if (judul.length > 100 || lokasi.length > 100) {
                        e.preventDefault();
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-error';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Judul atau lokasi terlalu panjang (maks. 100 karakter)!';
                        tambahForm.prepend(alertDiv);
                        setTimeout(() => alertDiv.remove(), 3000);
                        return;
                    }

                    if (pembina.length > 50) {
                        e.preventDefault();
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-error';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Pembina terlalu panjang (maks. 50 karakter)!';
                        tambahForm.prepend(alertDiv);
                        setTimeout(() => alertDiv.remove(), 3000);
                        return;
                    }

                    if (pakaian.length > 100) {
                        e.preventDefault();
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-error';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Pakaian terlalu panjang (maks. 100 karakter)!';
                        tambahForm.prepend(alertDiv);
                        setTimeout(() => alertDiv.remove(), 3000);
                        return;
                    }

                    if (note.length > 500) {
                        e.preventDefault();
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-error';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Catatan terlalu panjang (maks. 500 karakter)!';
                        tambahForm.prepend(alertDiv);
                        setTimeout(() => alertDiv.remove(), 3000);
                        return;
                    }

                    showLoading();
                    setTimeout(hideLoading, 1000);
                });
            }

            // Form submission (Edit)
            document.querySelectorAll('[id^="editForm"]').forEach(form => {
                form.addEventListener('submit', (e) => {
                    const id = form.querySelector('input[name="id"]').value;
                    const hari = form.querySelector(`#hari${id}`).value;
                    const waktu = form.querySelector(`#waktu${id}`).value;
                    const judul = form.querySelector(`#judul${id}`).value;
                    const lokasi = form.querySelector(`#lokasi${id}`).value;
                    const pembina = form.querySelector(`#pembina${id}`).value;
                    const pakaian = form.querySelector(`#pakaian${id}`).value;
                    const note = form.querySelector(`#note${id}`).value;

                    if (!hari || !waktu || !judul || !lokasi || !pembina) {
                        e.preventDefault();
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-error';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Hari, waktu, judul, lokasi, dan pembina wajib diisi!';
                        form.prepend(alertDiv);
                        setTimeout(() => alertDiv.remove(), 3000);
                        return;
                    }

                    if (judul.length > 100 || lokasi.length > 100) {
                        e.preventDefault();
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-error';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Judul atau lokasi terlalu panjang (maks. 100 karakter)!';
                        form.prepend(alertDiv);
                        setTimeout(() => alertDiv.remove(), 3000);
                        return;
                    }

                    if (pembina.length > 50) {
                        e.preventDefault();
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-error';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Pembina terlalu panjang (maks. 50 karakter)!';
                        form.prepend(alertDiv);
                        setTimeout(() => alertDiv.remove(), 3000);
                        return;
                    }

                    if (pakaian.length > 100) {
                        e.preventDefault();
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-error';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Pakaian terlalu panjang (maks. 100 karakter)!';
                        form.prepend(alertDiv);
                        setTimeout(() => alertDiv.remove(), 3000);
                        return;
                    }

                    if (note.length > 500) {
                        e.preventDefault();
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-error';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Catatan terlalu panjang (maks. 500 karakter)!';
                        form.prepend(alertDiv);
                        setTimeout(() => alertDiv.remove(), 3000);
                        return;
                    }

                    showLoading();
                    setTimeout(hideLoading, 1000);
                });
            });

            // Real-time validation
            document.querySelectorAll('input[name="hari"], input[name="waktu"]').forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value.length > 20) {
                        this.value = this.value.slice(0, 20);
                    }
                });
            });

            document.querySelectorAll('input[name="judul"], input[name="lokasi"]').forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value.length > 100) {
                        this.value = this.value.slice(0, 100);
                    }
                });
            });

            document.querySelectorAll('input[name="pembina"]').forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value.length > 50) {
                        this.value = this.value.slice(0, 50);
                    }
                });
            });

            document.querySelectorAll('input[name="pakaian"]').forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value.length > 100) {
                        this.value = this.value.slice(0, 100);
                    }
                });
            });

            document.querySelectorAll('textarea[name="note"]').forEach(textarea => {
                textarea.addEventListener('input', function() {
                    if (this.value.length > 500) {
                        this.value = this.value.slice(0, 500);
                    }
                });
            });

            // Modal handling
            window.showModal = (modalId) => {
                const modal = document.getElementById(modalId);
                if (modal) modal.classList.add('show');
            };

            window.hideModal = (modalId) => {
                const modal = document.getElementById(modalId);
                if (modal) modal.classList.remove('show');
            };

            // Confirm delete
            window.confirmDelete = () => {
                if (confirm('Yakin ingin menghapus jadwal ini?')) {
                    showLoading();
                    setTimeout(hideLoading, 1000);
                    return true;
                }
                return false;
            };

            // Ripple effect
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

            // Card hover effect
            document.querySelectorAll('.form-card, .jadwal-card, .jadwal-item').forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.boxShadow = '0 10px 20px rgba(44, 128, 48, 0.15)';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.boxShadow = '0 4px 12px rgba(44, 128, 48, 0.1)';
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
        });
    </script>
</body>
</html>