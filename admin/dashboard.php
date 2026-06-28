<?php
// Aktifkan mode keamanan
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

session_start();

// Pengecekan login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || ($_SESSION['username'] !== 'admin1' && $_SESSION['username'] !== 'sudo')) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Soefat</title>
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
            overflow-y: auto;
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

        /* Menu Grid */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .menu-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: var(--text-dark);
            box-shadow: var(--card-shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 120px;
            position: relative;
            overflow: hidden;
        }

        .menu-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transform: scaleX(0);
            transition: transform 0.3s;
        }

        .menu-card:hover::before {
            transform: scaleX(1);
        }

        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(44, 128, 48, 0.15);
            color: var(--primary-color);
        }

        .menu-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.8rem;
            transition: transform 0.3s;
        }

        .menu-card:hover .icon {
            transform: scale(1.1);
        }

        .menu-card .icon i {
            font-size: 1.5rem;
            color: white;
        }

        .menu-card .title {
            font-size: 0.9rem;
            font-weight: 600;
            line-height: 1.2;
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

        .nav-item .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            font-size: 0.6rem;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 16px;
            text-align: center;
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
            .menu-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 1.5rem;
            }
            
            .menu-card {
                min-height: 140px;
            }

            .main-content {
                max-width: 1200px;
                margin: 0 auto;
                padding: 80px 2rem 80px 2rem;
            }
        }

        @media (min-width: 1200px) {
            .menu-grid {
                grid-template-columns: repeat(4, 1fr);
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
            <div class="welcome-title">Selamat Datang, Admin!</div>
            <div class="welcome-subtitle">Kelola sistem dengan mudah dan efisien</div>
        </div>

        <!-- Menu Grid -->
        <div class="menu-grid">
            <a href="credential-add.php" class="menu-card">
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="title">Tambah Username</div>
            </a>

            <a href="schedule-manager.php" class="menu-card">
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="title">Tambah Jadwal</div>
            </a>

            <a href="registration-list.php" class="menu-card">
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="title">Pendaftaran</div>
            </a>

            <a href="siswa-manager.php" class="menu-card">
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="title">Data Anggota</div>
            </a>

            <a href="user-manager.php" class="menu-card">
                <div class="icon">
                    <i class="fas fa-key"></i>
                </div>
                <div class="title">Reset Password</div>
            </a>

            <a href="login-log.php" class="menu-card">
                <div class="icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="title">Riwayat Login</div>
            </a>

            <a href="attendance-list.php" class="menu-card">
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="title">Data Absensi</div>
            </a>

            <a href="org-chart-manager.php" class="menu-card">
                <div class="icon">
                    <i class="fas fa-sitemap"></i>
                </div>
                <div class="title">Bagan Dewan</div>
            </a>

            <a href="news-manager.php" class="menu-card">
                <div class="icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="title">Tambah Berita</div>
            </a>
            <a href="file-upload.php" class="menu-card">
               <div class="icon">
                 <i class="fas fa-upload"></i>
                </div>
            <div class="title">Upload File</div>
          </a>
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
            // Custom SweetAlert for access denied
            <?php if (isset($_SESSION['access_denied']) && $_SESSION['access_denied'] === true): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Ditolak',
                    text: '<?php echo addslashes('Maaf, hanya pengguna dengan hak akses "sudo" yang dapat mengakses halaman tersebut.'); ?>',
                    confirmButtonText: 'Mengerti',
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
                    <?php unset($_SESSION['access_denied']); ?>
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

            // Add click effect to menu cards
            const menuCards = document.querySelectorAll('.menu-card');
            menuCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    showLoading();
                    setTimeout(hideLoading, 1000);
                });
            });

            // Add ripple and scale effect to nav items
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
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
            navItems.forEach(item => {
                const href = item.getAttribute('href');
                if (href && href.includes(currentPage)) {
                    navItems.forEach(nav => nav.classList.remove('active'));
                    item.classList.add('active');
                }
            });

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
            .menu-card, .nav-item {
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