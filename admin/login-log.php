<?php
// Aktifkan mode keamanan
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

session_start();

// Error reporting untuk development
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config/database.php';

// Pengecekan akses sudo
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'sudo') {
    $_SESSION['access_denied'] = true;
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = floor($diff->d / 7);
    $daysRemaining = $diff->d % 7;

    $string = array(
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks,
        'd' => $daysRemaining,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    );

    $units = array(
        'y' => 'tahun',
        'm' => 'bulan',
        'w' => 'minggu',
        'd' => 'hari',
        'h' => 'jam',
        'i' => 'menit',
        's' => 'detik',
    );

    $result = array();
    foreach ($units as $k => $v) {
        if ($string[$k] > 0) {
            $result[] = $string[$k] . ' ' . $v;
        }
    }

    if (!$full) $result = array_slice($result, 0, 1);
    return $result ? implode(', ', $result) . ' lalu' : 'baru saja';
}

// Query dengan LEFT JOIN untuk mendapatkan nama dari tabel siswa
$query = "SELECT login_attempts.*, siswa.nama FROM login_attempts LEFT JOIN siswa ON login_attempts.nis = siswa.nis ORDER BY timestamp DESC";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    $error = "Query error: " . mysqli_error($koneksi);
}

// Simpan pesan ke session jika ada
if ($error) {
    $_SESSION['error'] = $error;
} elseif ($success) {
    $_SESSION['success'] = $success;
}

// Redirect ke halaman ini untuk refresh dan tampilkan SweetAlert
if ($error || $success) {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Login - Soefat Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
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
            --neutral-gray: #6c757d;
            --danger-color: #EF4444;
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
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            min-height: 60px;
        }

        .header .logo {
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .header .logo i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .header .actions {
            display: flex;
            align-items: center;
        }

        .header .actions .user-avatar {
            width: 35px;
            height: 35px;
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
            font-size: 1.2rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 80px 1rem 80px 1rem;
            max-width: 100vw;
        }

        .welcome-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
        }

        .welcome-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: var(--neutral-gray);
            font-size: 0.9rem;
        }

        /* Search Container */
        .search-container {
            position: relative;
            max-width: 400px;
            margin-bottom: 1.5rem;
        }

        .search-input {
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            width: 100%;
            font-size: 0.9rem;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 8px rgba(44, 128, 48, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--neutral-gray);
            font-size: 1rem;
        }

        /* Data Cards */
        .data-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--card-shadow);
            text-align: center;
            transition: transform 0.3s;
        }

        .data-card:hover {
            transform: translateY(-4px);
        }

        .data-card h6 {
            color: var(--neutral-gray);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .data-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .data-card.success h3 {
            color: var(--secondary-color);
        }

        .data-card.failed h3 {
            color: var(--danger-color);
        }

        /* Login Attempts */
        .login-attempts {
            display: grid;
            gap: 1rem;
        }

        .attempt-card {
            background: white;
            border-radius: 16px;
            padding: 1rem;
            box-shadow: var(--card-shadow);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .attempt-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(44, 128, 48, 0.15);
        }

        .attempt-card .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .attempt-card .success-badge {
            background: #D1FAE5;
            color: var(--secondary-color);
        }

        .attempt-card .failed-badge {
            background: #FEE2E2;
            color: var(--danger-color);
        }

        .attempt-card .detail-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .attempt-card .detail-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 128, 48, 0.3);
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
            padding: 0.8rem 0;
            box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            border-top: 1px solid #e9ecef;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--neutral-gray);
            font-size: 0.7rem;
            font-weight: 500;
            transition: all 0.3s;
            padding: 0.5rem;
            border-radius: 12px;
            min-width: 60px;
            position: relative;
            overflow: hidden;
        }

        .nav-item.active {
            color: var(--neutral-gray);
            background: rgba(108, 117, 125, 0.1);
        }

        .nav-item:hover {
            color: var(--neutral-gray);
            background: rgba(108, 117, 125, 0.05);
            transform: scale(1.05);
        }

        .nav-item i {
            font-size: 1.3rem;
            margin-bottom: 0.3rem;
            transition: transform 0.3s;
        }

        .nav-item.active i {
            transform: scale(1.1);
        }

        .nav-item .ripple {
            position: absolute;
            background: rgba(108, 117, 125, 0.3);
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

        /* Responsive */
        @media (min-width: 768px) {
            .main-content {
                max-width: 1200px;
                margin: 0 auto;
                padding: 80px 2rem 80px 2rem;
            }

            .login-attempts {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1200px) {
            .login-attempts {
                grid-template-columns: repeat(3, 1fr);
            }
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
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        /* Custom SweetAlert Styles */
        .custom-swal-popup {
            border-radius: 12px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.2);
            padding: 1.5rem;
            background: #ffffff;
            border: 1px solid var(--primary-color);
            position: relative;
        }

        .custom-swal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
        }

        .custom-swal-content {
            font-size: 1rem;
            color: var(--text-dark);
            line-height: 1.6;
        }

        .custom-swal-button {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.75rem 2rem;
            background: var(--primary-color);
            color: white;
            border: none;
            transition: all 0.2s ease;
        }

        .custom-swal-button:hover {
            background: var(--secondary-color);
            box-shadow: 0 4px 12px rgba(44, 128, 48, 0.3);
            transform: translateY(-2px);
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
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-title">Riwayat Login</div>
            <div class="welcome-subtitle">Pantau aktivitas login sistem</div>
        </div>

        <!-- Search -->
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Cari berdasarkan NIS, Nama, atau IP...">
        </div>

        <!-- Data Cards -->
        <div class="data-card">
            <h6>Total Percobaan</h6>
            <h3><?= mysqli_num_rows($result) ?></h3>
        </div>
        <div class="data-card success">
            <h6>Berhasil</h6>
            <h3><?= mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM login_attempts WHERE success = 1")) ?></h3>
        </div>
        <div class="data-card failed">
            <h6>Gagal</h6>
            <h3><?= mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM login_attempts WHERE success = 0")) ?></h3>
        </div>

        <!-- Login Attempts -->
        <div class="login-attempts">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="attempt-card">
                <div><strong>#:</strong> <?= htmlspecialchars($row['id']) ?></div>
                <div><strong>NIS:</strong> <?= htmlspecialchars($row['nis']) ?></div>
                <div><strong>Nama:</strong> <?= htmlspecialchars($row['nama'] ?? 'Tidak Diketahui') ?></div>
                <div>
                    <strong>Status:</strong>
                    <span class="status-badge <?= $row['success'] ? 'success-badge' : 'failed-badge' ?>">
                        <?= $row['success'] ? 'Berhasil' : 'Gagal' ?>
                    </span>
                </div>
                <div>
                    <strong>Lokasi:</strong>
                    <i class="fas fa-map-marker-alt me-1"></i>
                    <?= htmlspecialchars($row['ip_address']) ?>
                </div>
                <div>
                    <strong>Waktu:</strong>
                    <?php 
                    $timestamp = strtotime($row['timestamp']) + 25200; // Adjust for WIB (UTC+7)
                    echo date('d M Y H:i', $timestamp);
                    ?>
                </div>
                <button class="detail-btn">
                    <i class="fas fa-info-circle me-1"></i>Detail
                </button>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="credential-add.php" class="nav-item">
            <i class="fas fa-user-plus"></i>
            <span>User</span>
        </a>
        <a href="news-manager.php" class="nav-item">
            <i class="fas fa-newspaper"></i>
            <span>Berita</span>
        </a>
        <a href="org-chart-manager.php" class="nav-item">
            <i class="fas fa-sitemap"></i>
            <span>Bagan</span>
        </a>
        <a href="../logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Custom SweetAlert for error/success
            <?php if (isset($_SESSION['error'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?php echo addslashes($_SESSION['error']); ?>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: 'var(--primary-color)',
                    backdrop: `rgba(0, 0, 0, 0.5)`,
                    iconColor: '#EF4444',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        content: 'custom-swal-content',
                        confirmButton: 'custom-swal-button'
                    },
                    showClass: {
                        popup: 'animate__animated animate__fadeIn animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOut animate__faster'
                    }
                }).then(() => {
                    <?php unset($_SESSION['error']); ?>
                });
            <?php elseif (isset($_SESSION['success'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses',
                    text: '<?php echo addslashes($_SESSION['success']); ?>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: 'var(--primary-color)',
                    backdrop: `rgba(0, 0, 0, 0.5)`,
                    iconColor: 'var(--secondary-color)',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        content: 'custom-swal-content',
                        confirmButton: 'custom-swal-button'
                    },
                    showClass: {
                        popup: 'animate__animated animate__fadeIn animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOut animate__faster'
                    }
                }).then(() => {
                    <?php unset($_SESSION['success']); ?>
                });
            <?php endif; ?>

            // Loading animation
            function showLoading() {
                const loading = document.getElementById('loading');
                if (loading) {
                    loading.style.display = 'flex';
                }
            }

            function hideLoading() {
                const loading = document.getElementById('loading');
                if (loading) {
                    loading.style.display = 'none';
                }
            }

            // Add ripple effect to nav items and detail buttons
            const interactiveElements = document.querySelectorAll('.nav-item, .detail-btn');
            interactiveElements.forEach(item => {
                item.addEventListener('mousedown', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Active nav item management
            const currentPage = window.location.pathname.split('/').pop();
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                const href = item.getAttribute('href');
                if (href && href.includes(currentPage)) {
                    navItems.forEach(nav => nav.classList.remove('active'));
                    item.classList.add('active');
                }
            });

            // Search functionality
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const filter = this.value.toLowerCase();
                    const cards = document.querySelectorAll('.attempt-card');
                    cards.forEach(card => {
                        const nis = card.querySelector('div:nth-child(2)').textContent.toLowerCase();
                        const nama = card.querySelector('div:nth-child(3)').textContent.toLowerCase();
                        const ip = card.querySelector('div:nth-child(5)').textContent.toLowerCase();
                        if (nis.includes(filter) || nama.includes(filter) || ip.includes(filter)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });

        // Add ripple CSS
        const style = document.createElement('style');
        style.textContent = `
            .nav-item, .detail-btn, .attempt-card {
                position: relative;
                overflow: hidden;
            }
            .ripple {
                position: absolute;
                background: rgba(108, 117, 125, 0.3);
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
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>