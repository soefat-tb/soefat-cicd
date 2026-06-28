

    
  
    <?php
    session_start();
include '../config/database.php';

// Logika login hanya dieksekusi jika Kodular WebView terdeteksi
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    usleep(rand(100000, 300000));
    
    $query = "SELECT * FROM admin WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);
    
    if (!$result) {
        die("Query error: " . mysqli_error($koneksi));
    }

    $admin = mysqli_fetch_assoc($result);

    if ($admin) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['username'] = $admin['username'];
            $_SESSION['last_activity'] = time();
            
            if ($admin['username'] === 'admin1') {
                header("Location: dashboard.php");
            } elseif ($admin['username'] === 'sudo') {
                header("Location: user-manager.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        } elseif ($password === $admin['password']) {
            $_SESSION['login'] = true;
            $_SESSION['username'] = $admin['username'];
            $_SESSION['last_activity'] = time();
            
            if ($admin['username'] === 'admin1') {
                header("Location: dashboard.php");
            } elseif ($admin['username'] === 'sudo') {
                header("Location: user-manager.php");
            } else {
                header("Location: login.php");
            }
            exit();
        } else {
            $error = "Password salah!";
            error_log("Failed login attempt: username $username - " . date('Y-m-d H:i:s'));
        }
    } else {
        $error = "Username tidak ditemukan!";
        error_log("Failed login attempt with invalid username: $username - " . date('Y-m-d H:i:s'));
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #4caf50;
            --accent-color: #ffc107;
            --background-color: #f5f9f6;
            --text-color: #2c3e50;
            --error-color: #e53935;
            --success-color: #43a047;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('https://source.unsplash.com/random/1920x1080/?nature,green'), linear-gradient(135deg, rgba(46, 125, 50, 0.85) 0%, rgba(76, 175, 80, 0.85) 100%);
            background-blend-mode: overlay;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(46, 125, 50, 0.5) 0%, rgba(76, 175, 80, 0.5) 100%);
            backdrop-filter: blur(5px);
            z-index: -1;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 40px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
            transition: all 0.4s ease;
            position: relative;
            z-index: 10;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 100px;
            height: 100px;
            background-color: var(--accent-color);
            border-radius: 50%;
            opacity: 0.2;
            z-index: -1;
        }

        .login-container::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 100px;
            height: 100px;
            background-color: var(--secondary-color);
            border-radius: 50%;
            opacity: 0.2;
            z-index: -1;
        }

        .login-container:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .school-logo {
            width: 90px;
            height: 90px;
            margin-bottom: 15px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            padding: 15px;
            transition: all 0.3s ease;
        }

        .school-logo i {
            font-size: 40px;
            color: white;
        }

        .school-logo:hover {
            transform: scale(1.1) rotate(5deg);
        }

        .login-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 28px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .login-header p {
            color: var(--text-color);
            opacity: 0.8;
            font-size: 14px;
        }

        .form-control, .input-group-text {
            border-radius: 10px;
            padding: 12px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.1);
            font-size: 15px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.25);
            background-color: rgba(255, 255, 255, 0.95);
        }

        .input-group-text {
            background-color: var(--primary-color);
            border: none;
            color: white;
        }

        .form-label {
            color: var(--text-color);
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .btn-login {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s ease;
        }

        .btn-login:hover {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.5);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            color: var(--text-color);
            opacity: 0.7;
            font-size: 13px;
            position: relative;
        }

        .login-footer::before {
            content: '';
            display: block;
            height: 1px;
            width: 80%;
            background: linear-gradient(to right, transparent, rgba(0,0,0,0.1), transparent);
            margin: 15px auto;
        }

        .alert-danger {
            background-color: rgba(229, 57, 53, 0.1);
            border-left: 4px solid var(--error-color);
            color: var(--error-color);
            border-radius: 10px;
            padding: 15px;
            font-size: 14px;
        }

        .password-toggle-btn {
            background-color: transparent;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            transition: all 0.3s ease;
        }

        .password-toggle-btn:hover {
            color: var(--secondary-color);
        }

        .form-floating {
            position: relative;
            margin-bottom: 20px;
        }

        .password-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            font-size: 18px;
        }

        .input-with-icon {
            padding-left: 45px;
        }

        .login-bottom-decor {
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            z-index: -1;
            opacity: 0.1;
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .floating-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 0;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
            animation: float-up 15s linear infinite;
            z-index: 0;
        }

        @keyframes float-up {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            20% {
                opacity: 1;
            }
            80% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) scale(1);
                opacity: 0;
            }
        }

        .login-help {
            text-align: right;
            margin-top: 5px;
        }

        .login-help a {
            font-size: 13px;
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .login-help a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .shake {
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }
        
        @keyframes floating {
            0% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0); }
        }

        .floating-icon {
            animation: floating 3s ease-in-out infinite;
            display: inline-block;
        }
        
        @media (max-width: 576px) {
            .login-container {
                max-width: 90%;
                padding: 30px 20px;
            }
            
            .school-logo {
                width: 70px;
                height: 70px;
            }
            
            .school-logo i {
                font-size: 30px;
            }
            
            .login-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="floating-particles" id="particles"></div>

    <div class="login-container">
        <div class="login-header">
            <div class="school-logo floating-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <h2>||||Admin Portal||||</h2>
            <p>DEWAN SOEFAT</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show shake" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="mb-4">
                <label class="form-label">Username</label>
                <div class="form-floating">
                    <div class="position-relative">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="username" class="form-control input-with-icon" 
                            id="usernameInput"
                            required minlength="3" maxlength="50"
                            placeholder="Masukkan username">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="form-floating">
                    <div class="password-wrapper position-relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control input-with-icon" 
                            id="passwordInput"
                            required minlength="6"
                            placeholder="Masukkan password">
                        <button type="button" class="password-toggle-btn" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="login-help">
                    <a href="#" id="forgotPassword">Lupa password?</a>
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100 mt-2">
                <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Admin Panel
            </button>
        </form>

        <div class="login-footer">
            <small>© <?= date('Y') ?> SMKS Taruna Bangsa. All Rights Reserved</small>
            <div class="mt-2">
                <small>Secured with encryption</small> <i class="fas fa-shield-alt ms-1 text-success"></i>
            </div>
        </div>
        
        <svg class="login-bottom-decor" viewBox="0 0 200 200">
            <path fill="var(--primary-color)" d="M41.3,-65.7C53.4,-60.8,63.3,-49.9,69.5,-37.5C75.7,-25.1,78.3,-11.1,77.3,3.2C76.4,17.5,72,32.1,63.4,43.8C54.8,55.5,42,64.3,28.4,70C14.8,75.7,0.3,78.2,-14.7,75.8C-29.8,73.4,-45.4,66,-52.5,53.7C-59.6,41.4,-58.2,24.4,-60.7,8.3C-63.2,-7.8,-69.7,-23.1,-67.8,-38C-65.9,-52.9,-55.7,-67.3,-42.2,-71.8C-28.7,-76.2,-12,-70.6,1.2,-72.8C14.5,-74.9,29.2,-84.7,41.3,-79.1C53.5,-73.5,65.8,-52.6,77.4,-36.3C89,-20,99.9,-8.7,98.4,0.9C96.9,10.5,83,20.2,75,33.4C66.9,46.5,64.7,63,54.8,70.4C44.9,77.8,27.3,76.2,13,70C-1.3,63.8,-12.3,53.2,-25.8,46.9C-39.3,40.7,-55.3,38.9,-66.4,30.7C-77.5,22.6,-83.7,8.1,-81.7,-5.2C-79.6,-18.5,-69.3,-30.5,-59.2,-41.4C-49.1,-52.3,-39.2,-62,-27.7,-67.6C-16.2,-73.3,-3.1,-75,9.4,-72.3C22,-69.7,34,-74.6,41.3,-69.3C48.7,-64,51.5,-48.4,58.6,-36.4C65.8,-24.5,77.3,-16.1,82.6,-4.4C87.9,7.4,87,22.5,80.8,34.9C74.6,47.3,63.1,57,49.9,63.2C36.8,69.4,21.9,72.1,6.4,75.6C-9.1,79.1,-25.1,83.4,-37.7,78.9C-50.3,74.4,-59.5,61.1,-66.9,47.7C-74.3,34.3,-79.9,20.8,-79.9,7.2C-79.9,-6.3,-74.3,-19.8,-68.6,-33.7C-62.9,-47.5,-57.2,-61.6,-46.6,-68.3C-36,-75,-20.3,-74.4,-5.6,-71.3C9.1,-68.3,23.2,-62.9,34.7,-55.7C46.2,-48.6,55.2,-39.8,61.1,-29.8C67,-19.8,69.9,-8.5,65.4,0.3C60.9,9,49.1,15.6,42.3,25.4C35.6,35.2,33.9,48.1,26.3,54.9C18.8,61.7,5.4,62.2,-5.6,58.5C-16.6,54.9,-25.2,47,-33.5,39.7C-41.8,32.3,-49.9,25.5,-54.5,16.7C-59.2,7.9,-60.4,-2.9,-59.2,-14C-58,-25.1,-54.4,-36.5,-47.3,-46.6C-40.2,-56.7,-29.6,-65.4,-17.5,-70.3C-5.5,-75.2,8,-76.3,20.2,-71.8C32.5,-67.2,43.6,-57.1,54.6,-46.9C65.7,-36.8,76.8,-26.6,81.9,-14.1C87.1,-1.6,86.3,13.3,79.2,24.2C72.1,35.1,58.7,42.1,46.1,48.8C33.6,55.5,21.9,61.9,7.5,67.5C-6.9,73.1,-24,77.9,-39.4,74.6C-54.9,71.3,-68.6,60,-74.1,45.8C-79.6,31.7,-76.9,14.8,-74.4,-0.1C-71.9,-15,-69.5,-28,-63.6,-40.3C-57.7,-52.6,-48.3,-64.2,-36.4,-71.4C-24.5,-78.6,-10.1,-81.3,2.7,-78.5C15.6,-75.6,29.2,-67.3,41.3,-59.7Z" transform="translate(100 100)" />
        </svg>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const togglePasswordBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('passwordInput');
            const forgotPasswordLink = document.getElementById('forgotPassword');
            const particlesContainer = document.getElementById('particles');
            
            createParticles();
            
            togglePasswordBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            
            loginForm.addEventListener('submit', function(e) {
                const username = document.getElementById('usernameInput');
                const password = passwordInput;
                let isValid = true;

                username.classList.remove('is-invalid');
                password.classList.remove('is-invalid');
                
                if (username.value.trim().length < 3 || username.value.trim().length > 50) {
                    e.preventDefault();
                    isValid = false;
                    username.classList.add('is-invalid');
                    showError('Username harus antara 3-50 karakter');
                }

                if (password.value.length < 6) {
                    e.preventDefault();
                    isValid = false;
                    password.classList.add('is-invalid');
                    showError('Password minimal 6 karakter');
                }
                
                if (isValid) {
                    Swal.fire({
                        title: 'Memverifikasi...',
                        html: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }
            });
            
            forgotPasswordLink.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Lupa Password?',
                    text: 'Silakan hubungi administrator sistem untuk mereset password Anda.',
                    icon: 'info',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#2e7d32'
                });
            });
            
            function showError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: message,
                    confirmButtonColor: '#2e7d32'
                });
            }
            
            function createParticles() {
                for (let i = 0; i < 20; i++) {
                    const particle = document.createElement('div');
                    particle.classList.add('particle');
                    const size = Math.random() * 15 + 5;
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;
                    const posX = Math.random() * 100;
                    particle.style.left = `${posX}%`;
                    particle.style.opacity = Math.random() * 0.5 + 0.1;
                    const duration = Math.random() * 20 + 10;
                    const delay = Math.random() * 10;
                    particle.style.animationDuration = `${duration}s`;
                    particle.style.animationDelay = `${delay}s`;
                    particlesContainer.appendChild(particle);
                }
            }
            
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.setAttribute('autocomplete', 'off');
            });
            
            const loginContainer = document.querySelector('.login-container');
            loginContainer.addEventListener('mouseenter', function() {
                this.classList.add('active');
                document.querySelectorAll('.particle').forEach(p => {
                    p.style.animationPlayState = 'paused';
                });
            });
            
            loginContainer.addEventListener('mouseleave', function() {
                this.classList.remove('active');
                document.querySelectorAll('.particle').forEach(p => {
                    p.style.animationPlayState = 'running';
                });
            });
            
            window.addEventListener('beforeunload', function() {
                localStorage.removeItem('login_attempt');
                sessionStorage.removeItem('login_session');
            });
        });
    </script>
</body>
</html>