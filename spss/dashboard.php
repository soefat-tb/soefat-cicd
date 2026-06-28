<?php
session_start();

// Check for success message and clear it if present
if (isset($_SESSION['success'])) {
    $showSuccessAlert = true;
    unset($_SESSION['success']);
} else {
    $showSuccessAlert = false;
}

// Redirect to login page if not authenticated
// Cek baik $_SESSION['login'] (lama) maupun $_SESSION['authenticated'] (WebAuthn)
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        header("Location: login.php");
        exit();
    } else {
        // Jika WebAuthn authenticated, set $_SESSION['login'] biar kompatibel
        $_SESSION['login'] = true;
    }
}

// Regenerate session ID to prevent session fixation
session_regenerate_id(true);

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Get user info for personalization
$userName = $_SESSION['nama_lengkap'] ?? 'Siswa';
$userNIS = $_SESSION['nis'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SPSS - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <meta name="theme-color" content="#2c8030">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <style>
        :root {
            --primary-green: #2c8030;
            --secondary-green: #4a7c59;
            --light-green: #f0f9f1;
            --accent-green: #34d058;
            --dark-gray: #1a1a1a;
            --medium-gray: #6b7280;
            --light-gray: #f3f4f6;
            --white: #ffffff;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
            --shadow-xl: 0 12px 32px rgba(0, 0, 0, 0.15);
            --border-radius-sm: 8px;
            --border-radius-md: 12px;
            --border-radius-lg: 16px;
            --border-radius-xl: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --spring: cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            line-height: 1.6;
            color: var(--dark-gray);
        }

        .app-container {
            width: 100%;
            min-height: 100vh;
            background: var(--white);
            position: relative;
            box-shadow: var(--shadow-xl);
            max-width: 428px;
            margin: 0 auto;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            color: var(--white);
            padding: 1.25rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .header-info {
            flex: 1;
            min-width: 0;
        }

        .greeting {
            font-size: 0.875rem;
            opacity: 0.9;
            font-weight: 400;
            margin-bottom: 0.25rem;
        }

        .user-name {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.2;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }

        .time-widget {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            opacity: 0.95;
        }

        .date-display {
            font-size: 0.75rem;
            opacity: 0.8;
            margin-top: 0.25rem;
        }

        .profile-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-avatar {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            cursor: pointer;
        }

        .profile-avatar:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        .nis-badge {
            background: rgba(255, 255, 255, 0.15);
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        /* Quick Stats */
        .quick-stats {
            padding: 1rem 1.5rem 0;
            margin-bottom: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1rem;
            border-radius: var(--border-radius-md);
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            color: var(--white);
            font-size: 1rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--medium-gray);
            font-weight: 500;
        }

        /* Main Menu */
        .main-content {
            padding: 0 1.5rem 1.5rem;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .menu-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            text-decoration: none;
            color: inherit;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-height: 120px;
        }

        .menu-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-green), var(--secondary-green));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .menu-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .menu-card:hover::before {
            transform: scaleX(1);
        }

        .menu-card:active {
            transform: translateY(-2px) scale(0.98);
        }

        .menu-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            border-radius: var(--border-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            font-size: 1.5rem;
            color: var(--white);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .menu-card:hover .menu-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: var(--shadow-md);
        }

        .menu-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--dark-gray);
            line-height: 1.3;
        }

        .menu-description {
            font-size: 0.8rem;
            color: var(--medium-gray);
            line-height: 1.4;
            opacity: 0.9;
        }

        /* Featured Menu Card */
        .menu-card.featured {
            grid-column: span 2;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: var(--white);
            padding: 2rem 1.5rem;
            flex-direction: row;
            text-align: left;
            min-height: auto;
        }

        .menu-card.featured .menu-icon {
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
            margin-right: 1rem;
            margin-bottom: 0;
            backdrop-filter: blur(10px);
        }

        .menu-card.featured .menu-title {
            color: var(--white);
            font-size: 1.125rem;
        }

        .menu-card.featured .menu-description {
            color: rgba(255, 255, 255, 0.9);
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 428px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 0.75rem 1rem 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            z-index: 50;
        }

        .nav-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            max-width: 100%;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            color: var(--medium-gray);
            transition: var(--transition);
            position: relative;
            flex: 1;
            max-width: 80px;
        }

        .nav-item i {
            font-size: 1.25rem;
            transition: var(--transition);
        }

        .nav-item span {
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .nav-item.active {
            color: var(--primary-green);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            top: -0.75rem;
            left: 50%;
            transform: translateX(-50%);
            width: 24px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-green), var(--secondary-green));
            border-radius: 0 0 3px 3px;
        }

        .nav-item:hover {
            color: var(--primary-green);
            background: rgba(44, 128, 48, 0.05);
        }

        /* Content spacing for bottom nav */
        .main-content {
            padding-bottom: 120px;
        }

        /* Loading Animation */
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: var(--border-radius-md);
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Responsive Design */
        @media (min-width: 480px) {
            .app-container {
                max-width: 480px;
            }
            
            .menu-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
            }
            
            .stats-grid {
                gap: 1rem;
            }
        }

        @media (max-width: 375px) {
            .header {
                padding: 1rem 1.25rem;
            }
            
            .main-content,
            .quick-stats {
                padding-left: 1.25rem;
                padding-right: 1.25rem;
            }
            
            .menu-card {
                padding: 1.25rem;
                min-height: 110px;
            }
            
            .menu-icon {
                width: 48px;
                height: 48px;
                font-size: 1.25rem;
            }
            
            .menu-title {
                font-size: 0.9rem;
            }
            
            .menu-description {
                font-size: 0.75rem;
            }
        }

        @media (max-height: 640px) {
            .main-content {
                padding-bottom: 100px;
            }
            
            .bottom-nav {
                padding: 0.5rem 1rem 0.75rem;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --white: #1a1a1a;
                --light-gray: #2a2a2a;
                --dark-gray: #ffffff;
                --medium-gray: #a0a0a0;
            }
            
            .app-container {
                background: var(--white);
            }
            
            .menu-card,
            .stat-card {
                background: #2a2a2a;
                border-color: rgba(255, 255, 255, 0.1);
            }
            
            .bottom-nav {
                background: rgba(26, 26, 26, 0.95);
                border-top-color: rgba(255, 255, 255, 0.1);
            }
        }

        /* Accessibility improvements */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Focus states for keyboard navigation */
        .menu-card:focus,
        .nav-item:focus {
            outline: 2px solid var(--primary-green);
            outline-offset: 2px;
        }

        /* High contrast mode */
        @media (prefers-contrast: high) {
            .menu-card {
                border: 2px solid var(--dark-gray);
            }
        }

        /* Print styles */
        @media print {
            .bottom-nav,
            .profile-avatar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php if ($showSuccessAlert): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Operasi telah berhasil dilakukan',
                confirmButtonColor: 'var(--primary-green)',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        });
    </script>
    <?php endif; ?>

    <div class="app-container">
        <!-- Header Section -->
        <header class="header">
            <div class="header-content">
                <div class="header-info">
                    <div class="greeting">Selamat datang kembali,</div>
                    <h1 class="user-name"><?= htmlspecialchars($userName) ?></h1>
                    <div class="time-widget">
                        <i class="fas fa-clock"></i>
                        <span id="time">--:--</span>
                    </div>
                    <div class="date-display" id="date">Loading...</div>
                </div>
                <div class="profile-section">
                    <div class="profile-avatar" onclick="window.location.href='profile-edit.php'">
                        <i class="fas fa-user"></i>
                    </div>
                    <?php if ($userNIS): ?>
                    <div class="nis-badge"><?= htmlspecialchars($userNIS) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Quick Stats Section -->
        <section class="quick-stats">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-label">Kehadiran</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-label">Kegiatan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-label">Prestasi</div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <main class="main-content">
            <h2 class="section-title">
                <i class="fas fa-th-large"></i>
                Menu Utama
            </h2>

            <div class="menu-grid">
                <!-- Featured Menu Item -->
                <a href="attendance.php" class="menu-card featured" role="button" aria-label="Buka halaman presensi">
                    <div class="menu-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <h3 class="menu-title">Presensi Harian</h3>
                        <p class="menu-description">Catat kehadiran dan aktivitas pramuka anda</p>
                    </div>
                </a>

                <!-- Regular Menu Items -->
                <a href="profile-edit.php" class="menu-card" role="button" aria-label="Lihat data pribadi">
                    <div class="menu-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h3 class="menu-title">Data Pribadi</h3>
                    <p class="menu-description">Kelola informasi pribadi</p>
                </a>

                <a href="schedule.php" class="menu-card" role="button" aria-label="Lihat jadwal kegiatan">
                    <div class="menu-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="menu-title">Jadwal</h3>
                    <p class="menu-description">Jadwal kegiatan pramuka</p>
                </a>

                <a href="member-list.php" class="menu-card" role="button" aria-label="Lihat data anggota">
                    <div class="menu-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="menu-title">Data Anggota</h3>
                    <p class="menu-description">Daftar anggota pramuka</p>
                </a>

                <a href="organization-chart.php" class="menu-card" role="button" aria-label="Lihat struktur organisasi">
                    <div class="menu-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <h3 class="menu-title">Organisasi</h3>
                    <p class="menu-description">Struktur organisasi</p>
                </a>
            </div>
        </main>

        <!-- Bottom Navigation -->
        <nav class="bottom-nav" role="navigation" aria-label="Navigasi utama">
            <div class="nav-container">
                <a href="dashboard.php" class="nav-item active" aria-current="page">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                <a href="attendance.php" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Presensi</span>
                </a>
                <a href="profile-edit.php" class="nav-item">
                    <i class="fas fa-user"></i>
                    <span>Profil</span>
                </a>
                <a href="logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Enhanced Real-time Clock
        function updateClock() {
            const now = new Date();
            const timeOptions = { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false
            };
            const dateOptions = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            
            document.getElementById('time').textContent = 
                now.toLocaleTimeString('id-ID', timeOptions);
            document.getElementById('date').textContent = 
                now.toLocaleDateString('id-ID', dateOptions);
        }

        // Initialize clock
        updateClock();
        setInterval(updateClock, 1000);

        // Enhanced Card Interactions with Spring Animation
        document.querySelectorAll('.menu-card').forEach(card => {
            card.addEventListener('mousedown', function() {
                this.style.transform = 'translateY(-2px) scale(0.98)';
            });
            
            card.addEventListener('mouseup', function() {
                this.style.transform = '';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });

            // Touch events for mobile
            card.addEventListener('touchstart', function() {
                this.style.transform = 'translateY(-2px) scale(0.98)';
            });
            
            card.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });

        // Enhanced Session Management
        let lastActivity = <?php echo $_SESSION['last_activity'] ?? time(); ?>;
        const sessionTimeout = 1800; // 30 minutes
        let warningShown = false;

        function checkSession() {
            const currentTime = Date.now() / 1000;
            const timeRemaining = sessionTimeout - (currentTime - lastActivity);
            
            // Show warning at 5 minutes remaining
            if (timeRemaining <= 300 && !warningShown) {
                warningShown = true;
                Swal.fire({
                    title: 'Sesi Akan Berakhir',
                    text: 'Sesi anda akan berakhir dalam 5 menit. Lakukan aktivitas untuk memperpanjang sesi.',
                    icon: 'warning',
                    confirmButtonText: 'Perpanjang Sesi',
                    confirmButtonColor: 'var(--primary-green)',
                    showCancelButton: true,
                    cancelButtonText: 'Keluar Sekarang'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Reset activity timestamp
                        lastActivity = currentTime;
                        warningShown = false;
                        fetch('refresh-session.php', { method: 'POST' });
                    } else {
                        window.location.href = 'logout.php';
                    }
                });
            }
            
            // Auto logout when session expires
            if (timeRemaining <= 0) {
                Swal.fire({
                    title: 'Sesi Berakhir',
                    text: 'Sesi anda telah berakhir. Silakan login kembali.',
                    icon: 'info',
                    confirmButtonText: 'Login Kembali',
                    confirmButtonColor: 'var(--primary-green)',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = 'logout.php';
                });
            }
        }

        // Check session every 30 seconds
        setInterval(checkSession, 30000);

        // Update activity on user interaction
        ['click', 'keypress', 'scroll', 'mousemove'].forEach(event => {
            document.addEventListener(event, () => {
                lastActivity = Date.now() / 1000;
            }, { passive: true });
        });

        // Progressive Loading Animation
        function showLoadingSkeleton() {
            const cards = document.querySelectorAll('.menu-card');
            cards.forEach(card => {
                card.classList.add('loading-skeleton');
            });
            
            setTimeout(() => {
                cards.forEach(card => {
                    card.classList.remove('loading-skeleton');
                });
            }, 1000);
        }

        // Smooth scroll behavior for better UX
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
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

        // Service Worker Registration for PWA capabilities
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('SW registered: ', registration);
                }, function(registrationError) {
                    console.log('SW registration failed: ', registrationError);
                });
            });
        }

        // Network status indicator
        function updateNetworkStatus() {
            if (navigator.onLine) {
                document.body.classList.remove('offline');
            } else {
                document.body.classList.add('offline');
                Swal.fire({
                    title: 'Tidak Ada Koneksi',
                    text: 'Periksa koneksi internet anda',
                    icon: 'warning',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        }

        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);

        // Performance optimization: Lazy load images if any
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('loading');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }

        // Accessibility improvements
        document.addEventListener('keydown', function(e) {
            // Enable keyboard navigation for menu cards
            if (e.key === 'Enter' || e.key === ' ') {
                const focusedElement = document.activeElement;
                if (focusedElement.classList.contains('menu-card')) {
                    e.preventDefault();
                    focusedElement.click();
                }
            }
        });

        // Add ripple effect to menu cards
        function createRipple(event) {
            const card = event.currentTarget;
            const ripple = document.createElement('span');
            const rect = card.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = event.clientX - rect.left - size / 2;
            const y = event.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            card.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        }

        // Add ripple CSS
        const rippleCSS = `
            .menu-card {
                position: relative;
                overflow: hidden;
            }
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        
        const style = document.createElement('style');
        style.textContent = rippleCSS;
        document.head.appendChild(style);

        // Add ripple effect to all menu cards
        document.querySelectorAll('.menu-card').forEach(card => {
            card.addEventListener('click', createRipple);
        });

        // Battery status API for mobile optimization
        if ('getBattery' in navigator) {
            navigator.getBattery().then(function(battery) {
                if (battery.level < 0.2) {
                    // Reduce animations and effects when battery is low
                    document.body.classList.add('low-battery');
                    const lowBatteryCSS = `
                        .low-battery * {
                            animation-duration: 0.1s !important;
                            transition-duration: 0.1s !important;
                        }
                    `;
                    const batteryStyle = document.createElement('style');
                    batteryStyle.textContent = lowBatteryCSS;
                    document.head.appendChild(batteryStyle);
                }
            });
        }

        // Haptic feedback for supported devices
        function triggerHaptic() {
            if ('vibrate' in navigator) {
                navigator.vibrate(10);
            }
        }

        document.querySelectorAll('.menu-card, .nav-item').forEach(element => {
            element.addEventListener('touchstart', triggerHaptic);
        });

        // Performance monitoring
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (entry.loadEventEnd - entry.navigationStart > 3000) {
                        console.warn('Page load time is slow:', entry.loadEventEnd - entry.navigationStart);
                    }
                });
            });
            observer.observe({entryTypes: ['navigation']});
        }

        // Error boundary for JavaScript errors
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
            // Could implement error reporting here
        });

        // Unhandled promise rejection handler
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Unhandled Promise Rejection:', e.reason);
            e.preventDefault();
        });

        // Page visibility API to pause/resume timers
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Pause non-essential operations
                console.log('Page hidden, pausing operations');
            } else {
                // Resume operations
                console.log('Page visible, resuming operations');
                updateClock(); // Update clock immediately when page becomes visible
            }
        });

        // Prefetch important resources
        function prefetchResource(url) {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            document.head.appendChild(link);
        }

        // Prefetch likely next pages
        prefetchResource('attendance.php');
        prefetchResource('profile-edit.php');
        prefetchResource('schedule.php');

        console.log('SPSS Dashboard initialized successfully');
    </script>
</body>
</html>