<?php

include 'config/database.php';



// Ambil semua berita atau filter berdasarkan kategori

$kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';

$query = $kategori 

    ? "SELECT * FROM berita WHERE kategori = '$kategori' ORDER BY tanggal DESC" 

    : "SELECT * FROM berita ORDER BY tanggal DESC";

$result = mysqli_query($koneksi, $query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Berita Pramuka SMK Taruna Bangsa | Pramuka TB | Soefat Taruna Bangsa | Soefat TB</title>
    <link rel="canonical" href="https://soefat-tb.wuaze.com/news.php">
    <meta name="description" content="Berita Pramuka SMK Taruna Bangsa, Pramuka TB, Pramuka Taruna Bangsa, Soefat Taruna Bangsa, Soefat TB. Info kegiatan, prestasi, dan update terbaru pramuka sekolah.">
    <meta name="keywords" content="berita pramuka smk taruna bangsa, pramuka tb, pramuka taruna bangsa, soefat taruna bangsa, soefat tb, berita pramuka, pramuka sekolah, kegiatan pramuka, prestasi pramuka, info pramuka">
    <meta name="robots" content="index, follow">
    <meta name="author" content="SMK Taruna Bangsa">
    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:title" content="Berita Pramuka SMK Taruna Bangsa | Pramuka TB | Soefat Taruna Bangsa | Soefat TB">
    <meta property="og:description" content="Update berita pramuka SMK Taruna Bangsa, Pramuka TB, Pramuka Taruna Bangsa, Soefat Taruna Bangsa, Soefat TB, kegiatan, prestasi, dan info terbaru.">
    <meta property="og:url" content="https://soefat-tb.wuaze.com/news.php">
    <meta property="og:site_name" content="SMK Taruna Bangsa">
    <meta property="og:image" content="https://soefat-tb.wuaze.com/assets/img/pramuka-taruna-bangsa.png">
    <meta property="og:locale" content="id_ID">
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Berita Pramuka SMK Taruna Bangsa | Pramuka TB | Soefat Taruna Bangsa | Soefat TB">
    <meta name="twitter:description" content="Update berita pramuka SMK Taruna Bangsa, Pramuka TB, Pramuka Taruna Bangsa, Soefat Taruna Bangsa, Soefat TB, kegiatan, prestasi, dan info terbaru.">
    <meta name="twitter:image" content="https://soefat-tb.wuaze.com/assets/img/pramuka-taruna-bangsa.png">
    <!-- Structured Data JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "NewsArticle",
      "headline": "Berita Pramuka SMK Taruna Bangsa | Pramuka TB | Soefat Taruna Bangsa | Soefat TB",
      "description": "Berita Pramuka SMK Taruna Bangsa, Pramuka TB, Pramuka Taruna Bangsa, Soefat Taruna Bangsa, Soefat TB. Info kegiatan, prestasi, dan update terbaru pramuka sekolah.",
      "image": "https://soefat-tb.wuaze.com/assets/img/pramuka-taruna-bangsa.png",
      "datePublished": "2024-07-01",
      "dateModified": "2024-07-01",
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
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "https://soefat-tb.wuaze.com/news.php"
      }
    }
    </script>
    <!-- Favicon -->
    <link rel="icon" href="https://soefat-tb.wuaze.com/favicon.ico" type="image/x-icon">
    <meta name="theme-color" content="#4776e6">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
    --primary-color: #2c8030;
    --secondary-color: #4a7c59;
    --text-dark: #333;
    --text-light: #ffffff;
    --background-light: #f4f9f4;
}
   body {
    background-color: #f0f4f0;
     font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.card {
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(46,204,113,0.1);
}
.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(46,204,113,0.2);
}
.card-img-top {
    height: 250px;
    object-fit: cover;
}
.btn-category {
    margin-bottom: 10px;
}
.page-header {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    color: white;
    padding: 3rem 0;
    margin-bottom: 2rem;
}
@media (max-width: 768px) {
    .navbar-brand { font-size: 1rem; }
    .navbar img { height: 30px; }
    .btn-category { padding: 5px 10px; font-size: 0.8rem; background-color: #2ecc71; color: white; }
    .page-header { padding: 2rem 0; background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); }
    .display-4 { font-size: 2rem; }
    .card-img-top { height: 200px; }
    .btn-group { flex-direction: column; }
    .btn-group > .btn { margin-bottom: 10px; width: 100%; background-color: #2ecc71; border-color: #27ae60; }
}
@media (max-width: 576px) {
    .navbar-brand { font-size: 0.9rem; }
    .navbar img { height: 25px; }
    .display-4 { font-size: 1.5rem; }
    .card-img-top { height: 150px; }
}
@media (max-width: 576px) {
    .navbar .container-fluid { flex-direction: column; }
    .navbar .d-flex { width: 100%; flex-direction: column; align-items: center !important; }
    .navbar .btn { margin-top: 10px; background-color: #2ecc71; border-color: #27ae60; }
}
.container-fluid { width: 100%; padding-right: 15px; padding-left: 15px; margin-right: auto; margin-left: auto; }
.btn-group { display: flex; flex-wrap: wrap; justify-content: center; }
.card-img-top { width: 100%; height: 250px; object-fit: cover; transition: transform 0.4s ease; }
@media (min-width: 1200px) and (max-width: 1600px) { .card-img-top { height: 400px; } }
@media (min-width: 1600px) { .card-img-top { height: 500px; } }
.card:hover .card-img-top { transform: scale(1.05); }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-light bg-light mb-4">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between w-100 align-items-center">
                <div class="d-flex align-items-center">
                    <img src="https://soefat-tb.wuaze.com/assets/logo.png" alt="Logo Sekolah" height="40" class="me-2">
                    <span class="navbar-brand mb-0 h1">Soefat Nasa</span>
                </div>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
                </a>
            </div>
        </div>
    </nav>
    <!-- Header -->
    <div class="page-header text-center">
        <div class="container">
            <h1 class="display-4">
                <i class="fas fa-newspaper me-3"></i>Berita Pramuka
            </h1>
            <p class="lead">Informasi Terkini Seputar SOEFAT Taruna Bangsa</p>
        </div>
    </div>
    <div class="container">
        <!-- Filter Kategori -->
        <div class="text-center mb-5">
            <div class="btn-group" role="group" aria-label="Kategori Berita">
                <a href="news.php" class="btn btn-outline-primary btn-category <?= empty($kategori) ? 'active' : '' ?>">
                    <i class="fas fa-globe me-2"></i>Semua Berita
                </a>
                <a href="news.php?kategori=akademik" class="btn btn-outline-primary btn-category <?= $kategori == 'akademik' ? 'active' : '' ?>">
                    <i class="fas fa-graduation-cap me-2"></i>Akademik
                </a>
                <a href="news.php?kategori=prestasi" class="btn btn-outline-primary btn-category <?= $kategori == 'prestasi' ? 'active' : '' ?>">
                    <i class="fas fa-trophy me-2"></i>Prestasi
                </a>
                <a href="news.php?kategori=kegiatan" class="btn btn-outline-primary btn-category <?= $kategori == 'kegiatan' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt me-2"></i>Kegiatan
                </a>
            </div>
        </div>
        <!-- Daftar Berita -->
        <div class="row">
            <?php 
            if (mysqli_num_rows($result) > 0) {
                while($berita = mysqli_fetch_assoc($result)): 
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <?php if(!empty($berita['gambar'])): ?>
                        <img src="<?= htmlspecialchars($berita['gambar']) ?>" class="card-img-top" alt="<?= htmlspecialchars($berita['judul']) ?>">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-primary mb-2">
                            <?= htmlspecialchars(ucfirst($berita['kategori'])) ?>
                        </span>
                        <h5 class="card-title"><?= htmlspecialchars($berita['judul']) ?></h5>
                        <p class="card-text text-muted flex-grow-1">
                            <?= substr(strip_tags($berita['konten']), 0, 100) ?>...
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                <i class="far fa-calendar me-2"></i>
                                <?= date('d M Y', strtotime($berita['tanggal'])) ?>
                            </small>
                            <a href="detail_news.php?id=<?= $berita['id'] ?>" class="btn btn-sm btn-outline-primary">
                                Baca <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                endwhile; 
            } else {
                echo '<div class="col-12 text-center">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>Tidak ada berita ditemukan.
                        </div>
                      </div>';
            }
            ?>
        </div>
        <!-- Pagination (opsional, tambahkan jika diperlukan) -->
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>