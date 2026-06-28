<?php
// ===================================================
// KONFIGURASI SESSION
// ===================================================
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// ===================================================
// KONEKSI DATABASE
// ===================================================
include '../config/database.php';

// ===================================================
// PROSES LOGIN BACKUP CODE
// ===================================================
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['backup_code'] ?? '';
    $code = str_replace('-', '', $code); // Hilangkan separator
    $code = substr($code, 0, 8); // Pastikan 8 karakter
    $hashedCode = hash('sha256', $code);
    
    // Cek kode backup di database
    $stmt = $koneksi->prepare("SELECT * FROM admin1 
        WHERE backup_code = ? AND code_expiry > NOW()");
    $stmt->bind_param("s", $hashedCode);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    
    if ($admin) {
        // Invalidasi kode setelah digunakan
        $koneksi->query("UPDATE admin1 SET backup_code = NULL, code_expiry = NULL 
            WHERE id = {$admin['id']}");
        
        // Set session
        $_SESSION = [
            'login' => true,
            'username' => $admin['username'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Kode backup tidak valid atau telah kadaluarsa!";
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔒 Login Backup Code - SMKS Taruna Bangsa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a6741;
            --neon-shadow: 0 0 15px rgba(109, 150, 101, 0.5);
        }
        
        body {
            background: #1a1a1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 400px;
            border-radius: 15px;
            box-shadow: var(--neon-shadow);
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 1.2rem;
            text-align: center;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container p-4 mx-auto">
            <div class="text-center mb-4">
                <img src="logo.png" alt="Logo Sekolah" width="100" class="mb-3">
                <h2 class="text-light">SMKS Taruna Bangsa</h2>
                <p class="text-muted">Masukkan Kode Backup Anda</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <input type="text" 
                        id="display_backup_code"
                        class="form-control form-control-lg" 
                        placeholder="XXXX-XXXX"
                        required
                        oninput="formatBackupCode(this)">
                    <small class="text-muted">Format: XXXX-XXXX (8 karakter alfanumerik)</small>
                </div>
                
                <button type="submit" class="btn btn-success w-100 btn-lg">
                    <i class="fas fa-unlock-alt"></i> Masuk dengan Kode Backup
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function formatBackupCode(input) {
        // Ambil nilai input dan bersihkan karakter non-alphanumeric
        let value = input.value.replace(/[^A-Z0-9]/g, '');
        
        // Format tampilan dengan separator
        let formattedValue = value
            .replace(/([A-Z0-9]{4})([A-Z0-9]{4})/, "$1-$2")
            .substring(0, 9);
        
        // Update nilai yang ditampilkan
        input.value = formattedValue.toUpperCase();
        
        // Validasi visual
        if (value.length === 8) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
        }
    }
    </script>
</body>
</html>