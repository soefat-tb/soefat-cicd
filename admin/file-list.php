<?php
// Aktifkan mode keamanan
// header('X-Frame-Options: DENY'); // Removed to allow preview links
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Generate nonce for CSP
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'nonce-$nonce'; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'nonce-$nonce'; font-src 'self' https://cdnjs.cloudflare.com; frame-src 'self';");

session_start();

include 'config/database.php';

// Ambil daftar file dari database  
$files = [];
$query = "SELECT id, filename, original_name, file_type, file_size, upload_date, uploaded_by FROM uploaded_files ORDER BY upload_date DESC";
$result = mysqli_query($koneksi, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $files[] = $row;
    }
} else {
    error_log("Database query failed: " . mysqli_error($koneksi) . " - " . date('Y-m-d H:i:s'));
}

// Function untuk mendapatkan icon file berdasarkan extension
function getFileIcon($fileType) {
    $icons = [
        'pdf' => 'fas fa-file-pdf',
        'doc' => 'fas fa-file-word', 
        'docx' => 'fas fa-file-word',
        'xls' => 'fas fa-file-excel',
        'xlsx' => 'fas fa-file-excel', 
        'ppt' => 'fas fa-file-powerpoint',
        'pptx' => 'fas fa-file-powerpoint',
        'jpg' => 'fas fa-file-image',
        'jpeg' => 'fas fa-file-image',
        'png' => 'fas fa-file-image',
        'gif' => 'fas fa-file-image',
        'txt' => 'fas fa-file-alt',
        'zip' => 'fas fa-file-archive',
        'rar' => 'fas fa-file-archive'
    ];
    
    $ext = strtolower(pathinfo($fileType, PATHINFO_EXTENSION));
    return isset($icons[$ext]) ? $icons[$ext] : 'fas fa-file';
}

// Function untuk format ukuran file
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar File - Soefat Admin</title>
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
        <div class="file-list-section">
            <h2><i class="fas fa-folder-open"></i> Daftar File</h2>
            <?php if (empty($files)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Belum ada file yang diunggah.
                </div>
            <?php else: ?>
                <div class="file-grid">
                    <?php foreach ($files as $file): ?>
                        <?php
                        $filePath = 'uploads/dokumen/' . $file['filename'];
                        $fileExists = file_exists($filePath);
                        $fileExt = strtolower(pathinfo($file['original_name'], PATHINFO_EXTENSION));
                        ?>
                        <div class="file-card animate__animated animate__fadeInUp">
                            <div class="file-preview">
                                <?php if ($fileExists): ?>
                                    <?php if ($fileExt === 'pdf'): ?>
                                        <!-- PDF Preview with embedded viewer -->
                                        <div class="pdf-preview">
                                            <iframe src="<?= htmlspecialchars($filePath) ?>#toolbar=0&navpanes=0&scrollbar=0" 
                                                    frameborder="0" 
                                                    class="pdf-frame"></iframe>
                                            <div class="preview-overlay">
                                                <div class="overlay-content">
                                                    <i class="fas fa-file-pdf"></i>
                                                    <span>PDF Preview</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php elseif (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                        <!-- Image Preview -->
                                        <div class="image-preview">
                                            <img src="<?= htmlspecialchars($filePath) ?>" 
                                                 alt="<?= htmlspecialchars($file['original_name']) ?>"
                                                 class="preview-image">
                                            <div class="preview-overlay">
                                                <div class="overlay-content">
                                                    <i class="fas fa-image"></i>
                                                    <span>Image Preview</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Other file types with icon -->
                                        <div class="file-icon-preview">
                                            <i class="<?= getFileIcon($file['original_name']) ?>"></i>
                                            <div class="file-type-label"><?= strtoupper($fileExt) ?></div>
                                            <div class="preview-overlay">
                                                <div class="overlay-content">
                                                    <i class="fas fa-eye"></i>
                                                    <span>Klik untuk membuka</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="no-preview">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <p>File tidak ditemukan</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="file-details">
                                <h3 title="<?= htmlspecialchars($file['original_name']) ?>">
                                    <?= strlen($file['original_name']) > 25 ? substr(htmlspecialchars($file['original_name']), 0, 25) . '...' : htmlspecialchars($file['original_name']) ?>
                                </h3>
                                
                                <div class="file-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-tag"></i>
                                        <span><?= htmlspecialchars($file['file_type']) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-weight-hanging"></i>
                                        <span><?= formatFileSize($file['file_size']) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-user"></i>
                                        <span><?= htmlspecialchars($file['uploaded_by']) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?= date('d/m/Y H:i', strtotime($file['upload_date'])) ?></span>
                                    </div>
                                </div>
                                
                                <div class="file-actions">
                                    <a href="<?= htmlspecialchars($filePath) ?>" 
                                       class="btn-action btn-view" 
                                       target="_blank"
                                       title="Lihat file">
                                        <i class="fas fa-eye"></i>
                                        <span>Lihat</span>
                                    </a>
                                    <a href="<?= htmlspecialchars($filePath) ?>" 
                                       class="btn-action btn-download" 
                                       download="<?= htmlspecialchars($file['original_name']) ?>"
                                       title="Unduh file">
                                        <i class="fas fa-download"></i>
                                        <span>Unduh</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
        <a href="file-upload.php" class="nav-item">
            <i class="fas fa-upload"></i>
            <span>Upload</span>
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
            --card-shadow: 0 4px 20px rgba(44, 128, 48, 0.1);
            --neutral-gray: #6c757d;
            --error-color: #dc3545;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --hover-shadow: 0 8px 30px rgba(44, 128, 48, 0.15);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            min-height: 60px;
            backdrop-filter: blur(10px);
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
            padding: 90px 1rem 90px 1rem;
            overflow-y: auto;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .file-list-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .file-list-section h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        .file-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: all 0.4s ease;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .file-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--hover-shadow);
        }

        .file-preview {
            height: 200px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        /* PDF Preview Styles */
        .pdf-preview {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .pdf-frame {
            width: 100%;
            height: 100%;
            border: none;
            pointer-events: none;
            transform: scale(1); /* Adjusted to fit properly */
            transform-origin: top left;
            overflow: hidden;
        }

        /* Hide browser PDF viewer UI where possible */
        .pdf-frame::-webkit-scrollbar {
            display: none;
        }

        .pdf-frame {
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
        }

        /* Image Preview Styles */
        .image-preview {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .file-card:hover .preview-image {
            transform: scale(1.05);
        }

        /* File Icon Preview Styles */
        .file-icon-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .file-icon-preview i {
            font-size: 4rem;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .file-type-label {
            font-size: 1.2rem;
            font-weight: 600;
            opacity: 0.8;
        }

        /* Preview Overlay */
        .preview-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .file-card:hover .preview-overlay {
            opacity: 1;
        }

        .overlay-content {
            text-align: center;
            color: white;
        }

        .overlay-content i {
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .overlay-content span {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .no-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: #f8f9fa;
            color: var(--neutral-gray);
        }

        .no-preview i {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .file-details {
            padding: 1.5rem;
        }

        .file-details h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
            line-height: 1.3;
        }

        .file-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: var(--neutral-gray);
        }

        .meta-item i {
            width: 14px;
            font-size: 0.8rem;
            color: var(--accent-color);
        }

        .file-actions {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0.7rem;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
            color: white;
        }

        .btn-view:hover {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            transform: translateY(-2px);
        }

        .btn-download {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .btn-download:hover {
            background: linear-gradient(135deg, var(--secondary-color), #2c5530);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            border: none;
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(23, 162, 184, 0.1), rgba(23, 162, 184, 0.05));
            color: var(--info-color);
            border-left: 4px solid var(--info-color);
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
            height: 65px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .nav-item {
            color: white;
            text-align: center;
            text-decoration: none;
            flex: 1;
            padding: 10px 0;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            border-radius: 8px;
            margin: 0 4px;
        }

        .nav-item i {
            display: block;
            font-size: 1.3rem;
            margin-bottom: 4px;
        }

        .nav-item span {
            display: block;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .nav-item:hover,
        .nav-item:active {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: 80px 0.5rem 80px 0.5rem;
            }

            .file-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .file-preview {
                height: 180px;
            }

            .file-details {
                padding: 1rem;
            }

            .file-meta {
                grid-template-columns: 1fr;
            }

            .nav-item span {
                display: none;
            }

            .nav-item i {
                font-size: 1.5rem;
            }
        }

        /* Loading Animation */
        @keyframes shimmer {
            0% { background-position: -200px 0; }
            100% { background-position: calc(200px + 100%) 0; }
        }

        .loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200px 100%;
            animation: shimmer 1.5s infinite;
        }
    </style>

    <script nonce="<?php echo $nonce; ?>">
        // Add click functionality to file previews
        document.addEventListener('DOMContentLoaded', function() {
            const filePreviews = document.querySelectorAll('.file-preview');
            
            filePreviews.forEach(preview => {
                preview.addEventListener('click', function() {
                    const card = this.closest('.file-card');
                    const viewButton = card.querySelector('.btn-view');
                    if (viewButton) {
                        viewButton.click();
                    }
                });
            });

            // Add hover effects for better UX
            const fileCards = document.querySelectorAll('.file-card');
            fileCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });

        // Add loading states for slow connections
        window.addEventListener('load', function() {
            const loadingElements = document.querySelectorAll('.loading');
            loadingElements.forEach(el => el.classList.remove('loading'));
        });
    </script>
</body>
</html>