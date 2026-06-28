<?php
// Aktifkan mode keamanan
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Generate nonce for CSP
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'nonce-$nonce'; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'nonce-$nonce'; font-src 'self' https://cdnjs.cloudflare.com;");

session_start();

// Pengecekan login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || ($_SESSION['username'] !== 'admin1' && $_SESSION['username'] !== 'sudo')) {
    header("Location: /login.php");
    exit();
}

include 'config/database.php';

// Direktori penyimpanan file
$uploadDir = 'uploads/dokumen/';
$maxFileSize = 10 * 1024 * 1024; // 10MB to align with InfinityFree's likely default

// Jenis file yang diizinkan
$allowedTypes = [
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'
];

// Variabel untuk pesan
$success = '';
$error = '';

// Cek dan buat direktori dengan penanganan error
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $error = "Gagal membuat direktori uploads/dokumen!";
        error_log("Failed to create upload directory: " . date('Y-m-d H:i:s'));
    }
}

// Cek koneksi database
if (!$koneksi) {
    $error = "Koneksi database gagal!";
    error_log("Database connection failed: " . mysqli_connect_error() . " - " . date('Y-m-d H:i:s'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && empty($error)) {
    $file = $_FILES['file'];
    
    // Validasi ukuran file
    if ($file['size'] > $maxFileSize) {
        $error = "File terlalu besar! Maksimum 10MB.";
    } elseif ($file['error'] === UPLOAD_ERR_OK) {
        // Validasi tipe file menggunakan MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            $error = "Gagal memeriksa tipe file!";
            error_log("finfo_open failed: " . date('Y-m-d H:i:s'));
        } else {
            $fileType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!array_key_exists($fileType, $allowedTypes)) {
                $error = "Jenis file tidak diizinkan! Hanya PDF, Word, dan Excel yang diperbolehkan.";
            } else {
                // Sanitasi nama file
                $fileName = preg_replace("/[^A-Za-z0-9._-]/", '', basename($file['name']));
                $fileExtension = $allowedTypes[$fileType];
                $newFileName = uniqid() . '.' . $fileExtension;
                $destination = $uploadDir . $newFileName;

                // Pindahkan file
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Simpan informasi file ke database
                    $query = "INSERT INTO uploaded_files (filename, original_name, file_type, file_size, upload_date, uploaded_by) 
                             VALUES (?, ?, ?, ?, NOW(), ?)";
                    $stmt = mysqli_prepare($koneksi, $query);
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, 'sssis', $newFileName, $file['name'], $fileType, $file['size'], $_SESSION['username']);
                        if (mysqli_stmt_execute($stmt)) {
                            $success = "File berhasil diunggah!";
                        } else {
                            $error = "Gagal menyimpan informasi file ke database.";
                            unlink($destination); // Hapus file jika gagal simpan ke DB
                            error_log("Database insert failed: " . mysqli_error($koneksi) . " - " . date('Y-m-d H:i:s'));
                        }
                        mysqli_stmt_close($stmt);
                    } else {
                        $error = "Gagal mempersiapkan query database!";
                        unlink($destination);
                        error_log("Prepare statement failed: " . mysqli_error($koneksi) . " - " . date('Y-m-d H:i:s'));
                    }
                } else {
                    $error = "Gagal mengunggah file! Periksa izin direktori.";
                    error_log("move_uploaded_file failed for: " . $file['name'] . " - " . date('Y-m-d H:i:s'));
                }
            }
        }
    } else {
        $error = "Terjadi kesalahan saat mengunggah file: " . $file['error'];
        error_log("Upload error code: " . $file['error'] . " for file: " . $file['name'] . " - " . date('Y-m-d H:i:s'));
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload File - Soefat Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
</head>
<body>
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
        <div class="upload-section">
            <h2>Upload File</h2>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form action="" method="post" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="file" class="form-label">Pilih File (PDF, Word, Excel)</label>
                    <input type="file" name="file" id="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                    <div class="file-info">
                        Maksimum ukuran file: 10MB. Hanya file PDF, Word, dan Excel yang diizinkan.
                    </div>
                </div>
                <button type="submit" class="btn-upload">
                    <i class="fas fa-upload me-2"></i> Unggah File
                </button>
            </form>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item">
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

    <style nonce="<?php echo $nonce; ?>">
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
            --error-color: #dc3545;
            --success-color: #28a745;
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

        .main-content {
            flex: 1;
            padding: 80px 1rem 80px 1rem;
            overflow-y: auto;
            max-width: 1200px;
            margin: 0 auto;
        }

        .upload-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
        }

        .upload-section h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .upload-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 128, 48, 0.25);
        }

        .btn-upload {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-upload:hover {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 128, 48, 0.3);
        }

        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            border-left: 4px solid var(--success-color);
            color: var(--success-color);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border-left: 4px solid var(--error-color);
            color: var(--error-color);
        }

        .file-info {
            color: var(--neutral-gray);
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 80px 1rem 80px 1rem;
            }

            .upload-section {
                padding: 1rem;
            }
        }

        /* Bottom Navigation Styles */
        .bottom-nav {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            display: flex;
            justify-content: space-around;
            align-items: center;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .nav-item {
            color: white;
            text-align: center;
            text-decoration: none;
            flex: 1;
            padding: 8px 0;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .nav-item i {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 2px;
        }

        .nav-item span {
            display: block;
            font-size: 0.7rem;
        }

        .nav-item:hover,
        .nav-item:active {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        @media (max-width: 576px) {
            .nav-item span {
                display: none; /* Hide text on very small screens */
            }

            .nav-item i {
                font-size: 1.5rem; /* Larger icons on mobile */
            }
        }

        /* Loading spinner styles */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <script nonce="<?php echo $nonce; ?>">
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.upload-form');
            form.addEventListener('submit', function(e) {
                const fileInput = document.getElementById('file');
                const file = fileInput.files[0];
                
                if (file && file.size > <?php echo $maxFileSize; ?>) {
                    e.preventDefault();
                    alert('Ukuran File Terlalu Besar\nFile tidak boleh lebih dari 10MB');
                } else if (!this.querySelector('.alert-danger')) {
                    showLoading();
                }
            });

            // Loading animation
            function showLoading() {
                const loading = document.createElement('div');
                loading.className = 'loading';
                loading.innerHTML = '<div class="spinner"></div>';
                document.body.appendChild(loading);
            }

            function hideLoading() {
                const loading = document.querySelector('.loading');
                if (loading) {
                    loading.remove();
                }
            }

            // Hide loading on page load (in case of page refresh)
            hideLoading();
        });
    </script>
</body>
</html>