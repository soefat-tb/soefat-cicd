<?php
include 'config/database.php';

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query = "SELECT * FROM berita WHERE id = '$id'";
$result = mysqli_query($koneksi, $query);
$berita = mysqli_fetch_assoc($result);

if (!$berita) {
    echo "Berita tidak ditemukan!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2ecc71">
    <title><?= htmlspecialchars($berita['judul']) ?> - SMKS Taruna Bangsa</title>
    
    <!-- CSS Utama -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2ecc71;
            --secondary-color: #27ae60;
            --text-color: #333;
            --bg-color: #f4f7fa;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.7;
            overflow-x: hidden;
        }

        /* Header Styles */
        .artikel-header {
            position: relative;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 1rem 2rem;
            margin-bottom: 2rem;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
            overflow: hidden;
        }

        .artikel-header-content {
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .artikel-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .artikel-meta {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            color: rgba(255,255,255,0.8);
            margin-bottom: 1rem;
        }

        /* Back Button Styles */
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: rgba(255,255,255,0.2);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
            user-select: none;
            touch-action: manipulation;
            transition: all 0.3s ease;
            z-index: 3;
        }

        .back-button:hover,
        .back-button:focus {
            background-color: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }

        .back-button:active {
            transform: scale(0.9);
            background-color: rgba(255,255,255,0.4);
        }

        /* Image Styles */
        .artikel-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            transition: all 0.4s ease;
        }

        .artikel-image:hover {
            transform: scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }

        /* Content Styles */
        .artikel-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .artikel-content {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .artikel-content p {
            margin-bottom: 1.2rem;
            text-align: justify;
        }

        /* Additional Files Button */
        .view-more-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-top: 1rem;
        }

        .view-more-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 15px;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .modal-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1rem;
        }

        .modal-gallery-item {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            cursor: pointer;
        }

        .modal-gallery-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .modal-gallery-item:hover img {
            transform: scale(1.05);
        }

        /* Caption Styles */
        .image-caption {
            font-size: 0.9rem;
            color: var(--text-color);
            margin-top: 0.5rem;
            text-align: center;
        }

        /* Responsivitas */
        @media (max-width: 768px) {
            .artikel-title {
                font-size: 1.8rem;
            }
            .artikel-header {
                padding: 3rem 1rem 1rem;
            }
            .back-button {
                width: 44px;
                height: 44px;
                position: static;
                margin-bottom: 1rem;
                align-self: center;
            }
            .artikel-header-content {
                padding: 0 15px;
            }
            .artikel-content {
                padding: 1rem;
            }
            .modal-gallery-item img {
                height: 120px;
            }
        }

        @media (max-width: 480px) {
            .modal-gallery {
                grid-template-columns: 1fr;
            }
        }

        /* Fullscreen Styles */
        :fullscreen img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .fullscreen-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1050;
            justify-content: center;
            align-items: center;
        }

        .fullscreen-overlay.active {
            display: flex;
        }

        .fullscreen-close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="artikel-header">
        <a href="javascript:void(0)" class="back-button" id="backButton">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="artikel-header-content">
            <h1 class="artikel-title"><?= htmlspecialchars($berita['judul']) ?></h1>
            <div class="artikel-meta">
                <span>
                    <i class="fas fa-tag me-2"></i>
                    <?= htmlspecialchars(ucfirst($berita['kategori'])) ?>
                </span>
                <span>
                    <i class="far fa-calendar me-2"></i>
                    <?= date('d M Y', strtotime($berita['tanggal'])) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="artikel-container">
        <?php if (!empty($berita['gambar'])): ?>
            <figure>
                <img 
                    src="<?= htmlspecialchars($berita['gambar']) ?>" 
                    class="artikel-image" 
                    alt="<?= htmlspecialchars($berita['judul']) ?>" 
                    loading="lazy"
                >
                <figcaption class="image-caption"><?= htmlspecialchars($berita['judul']) ?></figcaption>
            </figure>
        <?php endif; ?>

        <div class="artikel-content">
            <?php 
            $konten = $berita['konten'];
            $konten = trim($konten);
            echo nl2br(htmlspecialchars($konten, ENT_QUOTES, 'UTF-8'));
            ?>
        </div>

        <?php
        // Array untuk semua file tambahan
        $additional_files = array_filter([
            $berita['additional_files'],
            $berita['additional_files_1'],
            $berita['additional_files_2'],
            $berita['additional_files_3'],
            $berita['additional_files_4']
        ]);

        if (!empty($additional_files)): ?>
            <button type="button" class="view-more-btn" data-bs-toggle="modal" data-bs-target="#additionalFilesModal">
                <i class="fas fa-images me-2"></i>Lihat Dokumentasi Lainnya
            </button>
        <?php endif; ?>
    </div>

    <!-- Modal untuk File Tambahan -->
    <div class="modal fade" id="additionalFilesModal" tabindex="-1" aria-labelledby="additionalFilesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="additionalFilesModalLabel">Dokumentasi Tambahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="modal-gallery">
                        <?php foreach ($additional_files as $index => $file): ?>
                            <div class="modal-gallery-item" onclick="openFullscreen(this)">
                                <img 
                                    src="<?= htmlspecialchars($file) ?>" 
                                    alt="<?= htmlspecialchars($berita['judul'] . ' - Gambar Tambahan ' . ($index + 1)) ?>" 
                                    loading="lazy"
                                >
                                <figcaption class="image-caption"><?= htmlspecialchars($berita['judul'] . ' - Gambar ' . ($index + 1)) ?></figcaption>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fullscreen Overlay -->
    <div class="fullscreen-overlay" id="fullscreenOverlay">
        <span class="fullscreen-close" onclick="closeFullscreen()">&times;</span>
        <img id="fullscreenImage" src="" alt="Fullscreen Image">
    </div>

    <!-- Structured Data untuk Gambar -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NewsArticle",
        "headline": "<?= htmlspecialchars($berita['judul']) ?>",
        "datePublished": "<?= date('c', strtotime($berita['tanggal'])) ?>",
        "dateModified": "<?= date('c', strtotime($berita['tanggal'])) ?>",
        "author": {
            "@type": "Organization",
            "name": "SMK Taruna Bangsa"
        },
        "publisher": {
            "@type": "Organization",
            "name": "SMK Taruna Bangsa",
            "logo": {
                "@type": "ImageObject",
                "url": "https://soefat-tb.wuaze.com/assets/logo.png"
            }
        },
        "image": [
            <?php 
            $images = array_filter([
                $berita['gambar'],
                $berita['additional_files'],
                $berita['additional_files_1'],
                $berita['additional_files_2'],
                $berita['additional_files_3'],
                $berita['additional_files_4']
            ]);
            $image_json = [];
            foreach ($images as $index => $image) {
                $image_json[] = '{
                    "@type": "ImageObject",
                    "url": "' . htmlspecialchars('https://soefat-tb.wuaze.com/' . $image) . '",
                    "caption": "' . htmlspecialchars($berita['judul'] . ' - Gambar ' . ($index + 1)) . '",
                    "width": "800",
                    "height": "600"
                }';
            }
            echo implode(',', $image_json);
            ?>
        ],
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "https://soefat-tb.wuaze.com/detail_news.php?id=<?= $berita['id'] ?>"
        }
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi kembali yang responsif
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = 'news.php';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const backButton = document.getElementById('backButton');
            
            backButton.addEventListener('click', (e) => {
                e.preventDefault();
                goBack();
            });

            backButton.addEventListener('touchstart', (e) => {
                e.preventDefault();
                backButton.style.transform = 'scale(0.9)';
            });

            backButton.addEventListener('touchend', (e) => {
                e.preventDefault();
                backButton.style.transform = 'scale(1)';
                goBack();
            });

            function responsiveImage() {
                const images = document.querySelectorAll('.artikel-image');
                images.forEach(img => {
                    img.style.maxHeight = `${window.innerHeight * 0.6}px`;
                });
            }

            responsiveImage();
            window.addEventListener('resize', responsiveImage);
        });

        // Fungsi Fullscreen
        function openFullscreen(element) {
            const img = element.querySelector('img');
            const fullscreenImage = document.getElementById('fullscreenImage');
            const fullscreenOverlay = document.getElementById('fullscreenOverlay');

            fullscreenImage.src = img.src;
            fullscreenOverlay.classList.add('active');

            // Gunakan API Fullscreen jika tersedia
            if (element.requestFullscreen) {
                element.requestFullscreen();
            } else if (element.mozRequestFullScreen) { // Firefox
                element.mozRequestFullScreen();
            } else if (element.webkitRequestFullscreen) { // Chrome, Safari, Edge
                element.webkitRequestFullscreen();
            } else if (element.msRequestFullscreen) { // IE/Edge
                element.msRequestFullscreen();
            }
        }

        function closeFullscreen() {
            const fullscreenOverlay = document.getElementById('fullscreenOverlay');
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
            fullscreenOverlay.classList.remove('active');
        }

        // Tambahkan event listener untuk menutup dengan tombol Esc
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && document.fullscreenElement) {
                closeFullscreen();
            }
        });

        window.addEventListener('scroll', () => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
        });
    </script>
</body>
</html>