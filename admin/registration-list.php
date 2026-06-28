<?php
// Aktifkan mode keamanan
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

session_start();
include 'config/database.php';

// Fungsi sanitasi input
function sanitizeInput($input) {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

// Validasi login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || !in_array($_SESSION['username'], ['admin1', 'sudo'])) {
    header("Location: /login.php");
    exit();
}

// Inisialisasi CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fungsi redirect dengan pesan
function redirectWithMessage($message, $type = 'success') {
    $_SESSION[$type] = $message;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Menghapus pendaftaran
if (isset($_GET['hapus_pendaftaran']) && isset($_GET['csrf_token']) && $_GET['csrf_token'] === $_SESSION['csrf_token']) {
    $id = intval($_GET['hapus_pendaftaran']);
    $stmt = $koneksi->prepare("DELETE FROM pendaftaran WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        redirectWithMessage("Pendaftaran berhasil dihapus!");
    } else {
        redirectWithMessage("Gagal menghapus pendaftaran: " . $stmt->error, 'error');
    }
    $stmt->close();
}

// Ambil daftar pendaftaran
$pendaftaran = [];
$search = isset($_POST['search']) ? sanitizeInput($_POST['search']) : '';
if ($search) {
    $search_param = "%$search%";
    $stmt = $koneksi->prepare("SELECT * FROM pendaftaran WHERE nama LIKE ? OR kelas LIKE ? OR nomor_telepon LIKE ? ORDER BY id DESC");
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pendaftaran[] = $row;
    }
    $stmt->close();
} else {
    $result = $koneksi->query("SELECT * FROM pendaftaran ORDER BY id DESC");
    while ($row = $result->fetch_assoc()) {
        $pendaftaran[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Sistem Manajemen Pendaftaran Pramuka - Soefat Admin">
    <meta name="theme-color" content="#2c8030">
    <title>Pendaftaran Pramuka - Soefat</title>
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
            --background-light: #f8f9fa;
            --text-dark: #2c3e50;
            --card-shadow: 0 4px 12px rgba(44, 128, 48, 0.1);
            --success-color: #28a745;
            --danger-color: #dc3545;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--background-light);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-size: 14px;
            overflow-x: hidden;
            padding-bottom: 70px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.8rem 1rem;
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
            gap: 0.5rem;
        }
.header .actions .export-btn {
    background: var(--primary-color);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-size: 0.9rem; /* Ngegedein teks dari 0.8rem ke 0.9rem */
    display: flex;
    align-items: center;
    gap: 0.3rem;
    text-decoration: none;
    transition: all 0.3s; /* Animasi smooth */
    position: relative; /* Untuk pembungkus animasi */
    overflow: hidden; /* Pastikan animasi tidak overflow */
}

.header .actions .export-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.2); /* Efek hover ringan */
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.4s, height 0.4s; /* Animasi lingkaran */
}

.header .actions .export-btn:hover::before {
    width: 200px;
    height: 200px; /* Lingkaran membesar saat hover */
}

.header .actions .export-btn:hover {
    background: var(--secondary-color); /* Warna berubah saat hover */
    transform: translateY(-1px); /* Efek angkat sedikit */
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
            padding: 1rem;
            flex: 1;
            margin-top: 70px;
            margin-bottom: 70px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        /* Search Bar */
        .search-container {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-container input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            font-size: 0.9rem;
            transition: border-color 0.3s;
        }

        .search-container input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(44, 128, 48, 0.25);
        }

        .search-container i {
            position: absolute;
            left: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            margin-bottom: 1rem;
            border-top: 4px solid var(--primary-color);
            transition: transform 0.2s;
            max-width: 100%;
            width: 100%;
        }
        .card-header {
    font-size: 1.5rem; /* Ukuran default untuk mobile */
    font-weight: 600;
    padding: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-dark);
}


        .card:hover {
            transform: translateY(-3px);
        }

        .card-body {
            padding: 0.8rem;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .card-content {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .card-content h5 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            color: var(--text-dark);
            word-break: break-word;
        }

        .card-content .subjudul {
            font-size: 0.85rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            word-break: break-word;
        }

        .card-content .isi {
            font-size: 0.8rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            word-break: break-word;
        }

        .card-content .isi.message {
            max-width: 100%;
        }

        .card-content i {
            width: 16px;
            text-align: center;
            color: var(--primary-color);
        }

        .card-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.5rem;
        }

        .btn {
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        /* Card Grid */
        .card-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            width: 100%;
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
            font-size: 0.9rem;
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
            .header .logo {
                font-size: 1.2rem;
            }

            .main-content {
                padding: 1.5rem;
            }

            .card-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .card-body {
                padding: 1rem;
            }

            .card-content h5 {
                font-size: 1.1rem;
            }

            .card-content .subjudul {
                font-size: 0.9rem;
            }

            .card-content .isi {
                font-size: 0.85rem;
            }

            .nav-item {
                font-size: 0.7rem;
            }

            .nav-item i {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 767px) {
            .card-body {
                padding: 0.6rem;
            }

            .card-content h5 {
                font-size: 0.9rem;
            }

            .card-content .subjudul {
                font-size: 0.8rem;
            }

            .card-content .isi {
                font-size: 0.75rem;
            }

            .btn {
                font-size: 0.7rem;
                padding: 0.2rem 0.4rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <i class="fas fa-shield-alt"></i>
            Soefat Admin
        </div>
        <div class="actions">
            <a href="../export-pendaftaran.php?csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="export-btn ripple-container">
                <i class="fas fa-file-excel"></i>
                Export
            </a>
            <div class="user-avatar ripple-container">
                <i class="fas fa-user"></i>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Notifikasi -->
            <?php if (isset($_SESSION['error'])): ?>
            <div class="notification notification-error">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo sanitizeInput($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
            <div class="notification notification-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo sanitizeInput($_SESSION['success']); ?>
                <?php unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <div class="search-container">
                <form method="POST">
                    <i class="fas fa-search"></i>
                    <input type="search" name="search" placeholder="Cari Nama/Kelas/Nomor Telepon" value="<?php echo htmlspecialchars($search); ?>">
                </form>
            </div>

            <!-- Daftar Pendaftaran -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-plus"></i>
                    Pendaftaran Pramuka
                </div>
                <div class="card-body">
                    <?php if (empty($pendaftaran)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-search text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-2">Tidak ada pendaftaran yang ditemukan</h5>
                            <p class="text-muted">Coba ubah kata kunci pencarian.</p>
                        </div>
                    <?php else: ?>
                        <div class="card-grid">
                            <?php foreach ($pendaftaran as $pendaftar): ?>
                            <div class="card pendaftaran-card">
                                <div class="card-body">
                                    <div class="card-content">
                                        <h5><?php echo htmlspecialchars($pendaftar['nama']); ?></h5>
                                        <p class="subjudul"><i class="fas fa-school"></i><?php echo htmlspecialchars($pendaftar['kelas']); ?></p>
                                        <p class="subjudul"><i class="fas fa-phone"></i><?php echo htmlspecialchars($pendaftar['nomor_telepon']); ?></p>
                                        <p class="isi message"><i class="fas fa-comment"></i><?php echo htmlspecialchars($pendaftar['pesan'] ?? '-'); ?></p>
                                        <p class="isi"><i class="fas fa-calendar-alt"></i><?php echo date('d M Y H:i', strtotime($pendaftar['tanggal_daftar'])); ?></p>
                                    </div>
                                    <div class="card-actions">
                                        <button class="btn btn-danger delete-btn ripple-container" 
                                                data-id="<?php echo $pendaftar['id']; ?>" 
                                                data-csrf="<?php echo $_SESSION['csrf_token']; ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item ripple-container">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="credential-add.php" class="nav-item ripple-container">
            <i class="fas fa-user-plus"></i>
            <span>Data Siswa</span>
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

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set active nav item
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                if (item.href.includes('registration-list.php')) {
                    item.classList.add('active');
                }
            });

            // Ripple effect
            document.querySelectorAll('.ripple-container').forEach(container => {
                container.addEventListener('click', function(e) {
                    const rect = this.getBoundingClientRect();
                    const ripple = document.createElement('span');
                    ripple.className = 'ripple';
                    const diameter = Math.max(this.clientWidth, this.clientHeight);
                    const radius = diameter / 2;
                    ripple.style.width = ripple.style.height = `${diameter}px`;
                    ripple.style.left = `${e.clientX - rect.left - radius}px`;
                    ripple.style.top = `${e.clientY - rect.top - radius}px`;
                    this.appendChild(ripple);
                    setTimeout(() => ripple.remove(), 600);
                });
            });

            // Delete button
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const csrf = this.getAttribute('data-csrf');
                    Swal.fire({
                        title: 'Konfirmasi Hapus',
                        text: 'Anda yakin ingin menghapus pendaftaran ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `?hapus_pendaftaran=${id}&csrf_token=${csrf}`;
                        }
                    });
                });
            });

            // Auto-hide notifications
            setTimeout(() => {
                document.querySelectorAll('.notification').forEach(notification => {
                    notification.style.animation = 'fadeOut 0.3s';
                    setTimeout(() => notification.remove(), 300);
                });
            }, 5000);

            // Real-time search filter
            const searchInput = document.querySelector('input[name="search"]');
            const cards = document.querySelectorAll('.pendaftaran-card');
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                cards.forEach(card => {
                    const nama = card.querySelector('h5').textContent.toLowerCase();
                    const kelas = card.querySelector('.subjudul:nth-of-type(1)').textContent.toLowerCase();
                    const telepon = card.querySelector('.subjudul:nth-of-type(2)').textContent.toLowerCase();
                    if (nama.includes(query) || kelas.includes(query) || telepon.includes(query)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>