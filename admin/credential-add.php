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

// Proses form ketika di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'config/database.php';

    try {
        $nis = sanitizeInput($_POST['nis']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validasi input
        if (empty($nis) || empty($password) || empty($confirm_password)) {
            throw new Exception("Semua field harus diisi!");
        }
        if (!preg_match('/^[0-9]{1,20}$/', $nis)) {
            throw new Exception("NIS hanya boleh berisi angka (maksimal 20 digit)!");
        }
        if ($password !== $confirm_password) {
            throw new Exception("Password dan konfirmasi password tidak sama!");
        }
        if (strlen($password) < 6) {
            throw new Exception("Password minimal 6 karakter!");
        }

        // Cek apakah NIS sudah ada
        $check_query = "SELECT * FROM login WHERE nis = ?";
        $check_stmt = $koneksi->prepare($check_query);
        if ($check_stmt === false) {
            throw new Exception("Gagal menyiapkan query: " . $koneksi->error);
        }
        $check_stmt->bind_param("s", $nis);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("NIS sudah terdaftar!");
        }

        // Insert ke database (password plain text)
        $insert_query = "INSERT INTO login (nis, password) VALUES (?, ?)";
        $insert_stmt = $koneksi->prepare($insert_query);
        if ($insert_stmt === false) {
            throw new Exception("Gagal menyiapkan query insert: " . $koneksi->error);
        }
        $insert_stmt->bind_param("ss", $nis, $password);

        if ($insert_stmt->execute()) {
            $_SESSION['success'] = "User berhasil ditambahkan!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            throw new Exception("Gagal menambahkan user: " . $insert_stmt->error);
        }

        $insert_stmt->close();
        $check_stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tambah Kredensial Login - Soefat</title>
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

        .form-card {
            background: white;
            border-radius: 10px;
            padding: 1.2rem;
            box-shadow: var(--card-shadow);
            max-width: 450px;
            min-height: 350px;
            margin: 1rem auto;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.5s ease-in;
        }

        .form-card::before {
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

        .form-control {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 3rem;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: border-color 0.3s, box-shadow 0.3s;
            background: #fafafa;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 128, 48, 0.2);
            background: white;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--secondary-color);
            font-size: 1rem;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: var(--accent-color);
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

            .form-card {
                padding: 1.8rem;
                max-width: 500px;
                min-height: 400px;
                margin: 1.5rem auto;
            }

            .form-title {
                font-size: 1.5rem;
            }

            .form-control {
                padding: 0.7rem 1.2rem 0.7rem 3.2rem;
                font-size: 1rem;
            }

            .input-group i {
                font-size: 1.2rem;
                left: 1.2rem;
            }

            .password-toggle {
                font-size: 1.2rem;
                right: 1.2rem;
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
        }

        @media (max-width: 767px) {
            .form-card {
                padding: 1.2rem;
                margin: 1rem 0.5rem;
            }

            .form-control {
                padding: 0.6rem 1rem 0.6rem 3rem;
                font-size: 0.9rem;
            }

            .input-group i {
                font-size: 1rem;
                left: 1rem;
            }

            .password-toggle {
                font-size: 1rem;
                right: 1rem;
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
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
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
                <i class="fas fa-user-plus"></i> Tambah Kredensial Login
            </h2>
            <form method="POST" action="" id="userForm">
                <div class="form-group">
                    <label for="nis" class="form-label">NIS</label>
                    <div class="input-group">
                        <i class="fas fa-id-card"></i>
                        <input type="text" class="form-control" id="nis" name="nis" placeholder="Masukkan NIS" required
                               value="<?php echo isset($_POST['nis']) ? htmlspecialchars($_POST['nis']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi password" required>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary ripple-container">
                    <i class="fas fa-save"></i> Tambah User
                </button>
            </form>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item ripple-container">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="credential-add.php" class="nav-item active ripple-container">
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

            // Form submission
            const form = document.getElementById('userForm');
            form.addEventListener('submit', (e) => {
                const nis = document.getElementById('nis').value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                if (!nis || !password || !confirmPassword) {
                    e.preventDefault();
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-error';
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Semua field harus diisi!';
                    form.prepend(alertDiv);
                    setTimeout(() => alertDiv.remove(), 3000);
                    return;
                }

                if (!/^[0-9]{1,20}$/.test(nis)) {
                    e.preventDefault();
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-error';
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> NIS hanya boleh berisi angka (maksimal 20 digit)!';
                    form.prepend(alertDiv);
                    setTimeout(() => alertDiv.remove(), 3000);
                    return;
                }

                if (password !== confirmPassword) {
                    e.preventDefault();
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-error';
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Password dan konfirmasi password tidak sama!';
                    form.prepend(alertDiv);
                    setTimeout(() => alertDiv.remove(), 3000);
                    return;
                }

                if (password.length < 6) {
                    e.preventDefault();
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-error';
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Password minimal 6 karakter!';
                    form.prepend(alertDiv);
                    setTimeout(() => alertDiv.remove(), 3000);
                    return;
                }

                showLoading();
                setTimeout(hideLoading, 1000);
            });

            // Toggle password visibility
            window.togglePassword = (fieldId) => {
                const field = document.getElementById(fieldId);
                const icon = field.nextElementSibling;
                if (field && icon) {
                    if (field.type === 'password') {
                        field.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        field.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            };

            // Real-time validation for NIS
            const nis = document.getElementById('nis');
            if (nis) {
                nis.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value.length > 20) {
                        this.value = this.value.slice(0, 20);
                    }
                });
            }

            // Real-time validation for confirm password
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword) {
                confirmPassword.addEventListener('input', function() {
                    const password = document.getElementById('password').value;
                    if (this.value && password !== this.value) {
                        this.style.borderColor = '#dc3545';
                    } else {
                        this.style.borderColor = '#e9ecef';
                    }
                });
            }

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

            // Form card hover effect
            const formCard = document.querySelector('.form-card');
            if (formCard) {
                formCard.addEventListener('mouseenter', () => {
                    formCard.style.boxShadow = '0 10px 20px rgba(44, 128, 48, 0.15)';
                });
                formCard.addEventListener('mouseleave', () => {
                    formCard.style.boxShadow = '0 4px 12px rgba(44, 128, 48, 0.1)';
                });
            }

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