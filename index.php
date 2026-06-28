<?php
// Koneksi database dengan penanganan error yang lebih baik
include 'config/database.php';

// Gunakan prepared statement untuk keamanan
$query = "SELECT id, judul, konten, gambar, tanggal FROM berita ORDER BY tanggal DESC LIMIT 3";
$result = mysqli_query($koneksi, $query);

// Cek apakah query berhasil
if (!$result) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary Meta Tags -->
    <title>Pramuka Taruna Bangsa - Soefat Nasa & Soefat TB | Official Website</title>
    <meta name="title" content="Pramuka Taruna Bangsa - Soefat Nasa & Soefat TB | Official Website">
    <meta name="description" content="Gerakan Pramuka Soefat Nasa, bagian dari Pramuka Taruna Bangsa (Soefat TB), membentuk karakter dan kepemimpinan generasi muda di Bekasi. Bergabung sekarang!">
    <meta name="keywords" content="pramuka nasa, pramuka taruna bangsa, Soefat TB, pramuka bekasi, kegiatan pramuka, kepanduan, pembentukan karakter, Soefat Nasa">
    <meta name="author" content="Pramuka Soefat Nasa">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Indonesia">
    <meta name="revisit-after" content="7 days">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://soefat-tb.wuaze.com/">
    <meta property="og:title" content="Pramuka Taruna Bangsa - Soefat Nasa & Soefat TB">
    <meta property="og:description" content="Bergabung dengan Gerakan Pramuka Soefat Nasa (Soefat TB) untuk mengembangkan karakter, kepemimpinan, dan keterampilan hidup di Bekasi.">
    <meta property="og:image" content="https://soefat-tb.wuaze.com/assets/baner/pramukabaner.png">
    <meta property="og:image:alt" content="Banner Pramuka Taruna Bangsa Soefat Nasa">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://soefat-tb.wuaze.com/">
    <meta property="twitter:title" content="Pramuka Taruna Bangsa - Soefat Nasa & Soefat TB">
    <meta property="twitter:description" content="Bergabung dengan Gerakan Pramuka Soefat Nasa (Soefat TB) untuk mengembangkan karakter, kepemimpinan, dan keterampilan hidup di Bekasi.">
    <meta property="twitter:image" content="https://soefat-tb.wuaze.com/assets/baner/pramukabaner.png">
    
    <!-- WhatsApp Meta Tags -->
    <meta property="og:site_name" content="Pramuka Soefat Nasa">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://soefat-tb.wuaze.com/">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/logo.png">
    <link rel="apple-touch-icon" href="assets/logo.png">
    
    <!-- Schema.org Markup -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Pramuka Soefat Nasa",
        "url": "https://soefat-tb.wuaze.com",
        "logo": "https://soefat-tb.wuaze.com/assets/logo.png",
        "description": "Gerakan Pramuka Soefat Nasa, bagian dari Pramuka Taruna Bangsa (Soefat TB), fokus pada pembentukan karakter dan kepemimpinan generasi muda di Bekasi.",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Jl.Lingkar Utara Bekasi Kel. Perwira Kec. Bekasi Utara",
            "addressLocality": "Bekasi",
            "addressRegion": "Jawa Barat",
            "postalCode": "",
            "addressCountry": "ID"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+62-851-8214-5822",
            "contactType": "customer service",
            "email": "soefattarunabangsa@gmail.com"
        },
        "sameAs": [
            "https://web.facebook.com/smktarunabangsabekasiofficial/",
            "https://www.instagram.com/pramuka_nasa/",
            "https://www.youtube.com/@smktarunabangsabekasi4045"
        ]
    }
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
        --primary-color: #2c8030;  /* Hijau pramuka */
        --secondary-color: #4a7c59;  /* Hijau lebih gelap */
        --text-dark: #333;
        --text-light: #ffffff;
        --background-light: #f4f9f4;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: var(--text-dark);
        overflow-x: hidden;
    }

    /* Navbar Styles */ 
    .navbar { transition: all 0.3s ease; background-color: rgba(44, 128, 48, 0.9) !important; }

    .navbar-brand { color: var(--text-light) !important; font-weight: bold; }

    .navbar-nav .nav-link { color: var(--text-light) !important; transition: color 0.3s ease; }

    .navbar-nav .nav-link:hover { color: #ffffff !important; transform: translateY(-3px); }

    /* Hero Carousel Styles */
    #heroCarousel {
        position: relative;
        height: 100vh;
        overflow: hidden;
    }

    #heroCarousel .carousel-item {
        height: 100vh;
        position: relative;
    }

    #heroCarousel .carousel-item img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: brightness(0.6);
    }

    #heroCarousel .carousel-caption {
        position: absolute;
        top: 50%;
        left: 10%;
        transform: translateY(-50%);
        text-align: left;
        z-index: 10;
        color: white;
        text-shadow: 0 4px 6px rgba(0,0,0,0.5);
        width: 70%;
        padding: 20px;
        background: rgba(0,0,0,0.3);
        border-radius: 10px;
    }

    #heroCarousel .carousel-caption h1 {
        font-size: 3.5rem;
        font-weight: bold;
        margin-bottom: 20px;
        color: var(--text-light);
        line-height: 1.2;
        text-align: left;
    }

    #heroCarousel .carousel-caption p {
        font-size: 1.2rem;
        margin-bottom: 30px;
        color: rgba(255,255,255,0.8);
        text-align: left;
    }

    #heroCarousel .carousel-caption .btn {
        text-align: left;
        padding: 10px 20px;
        font-size: 1rem;
    }

    /* Kegiatan Section Styles */
    #kegiatan {
        background-color: var(--background-light);
        padding: 60px 0;
    }

    #kegiatan .container {
        max-width: 1200px;
    }

    #kegiatan h2 {
        color: var(--primary-color);
        margin-bottom: 40px;
        position: relative;
        text-align: center;
    }

    #kegiatan h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 3px;
        background-color: var(--primary-color);
    }

    #kegiatan .card {
        transition: all 0.3s ease;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    #kegiatan .card:hover {
        transform: translateY(-15px);
        box-shadow: 0 20px 35px rgba(0,0,0,0.15);
    }

    #kegiatan .card-img-top {
        height: 350px;
        width: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    #kegiatan .card:hover .card-img-top {
        transform: scale(1.1);
    }

    #kegiatan .card-body {
        padding: 25px;
    }

    #kegiatan .card-title {
        color: var(--primary-color);
        font-weight: bold;
        margin-bottom: 15px;
        font-size: 1.2rem;
    }

    #kegiatan .card-text {
        color: var(--text-dark);
        margin-bottom: 15px;
        font-size: 0.95rem;
    }

    #kegiatan .badge {
        font-size: 0.8rem;
        padding: 5px 10px;
    }

    #kegiatan .btn {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
    }

    #kegiatan .btn i {
        margin-left: 10px;
    }

    #kegiatan .btn:hover {
        background-color: var(--secondary-color);
        transform: translateY(-3px);
    }

    /* Responsive Adjustments */
    @media (max-width: 1600px) {
        #kegiatan .card-img-top {
            height: 300px;
        }
    }

    @media (max-width: 1200px) {
        #kegiatan .card-img-top {
            height: 250px;
        }
    }

    @media (max-width: 992px) {
        #kegiatan .card-img-top {
            height: 220px;
        }
    }

    @media (max-width: 768px) {
        #kegiatan h2 {
            font-size: 1.8rem;
        }

        #kegiatan .card-img-top {
            height: 200px;
        }

        #kegiatan .card-title {
            font-size: 1.1rem;
        }

        #kegiatan .card-text {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        #kegiatan .card-img-top {
            height: 180px;
        }

        #kegiatan .card-title {
            font-size: 1rem;
        }

        #kegiatan .card-text {
            font-size: 0.85rem;
        }
    }

    /* Responsive Adjustments for Hero Carousel */
    @media (max-width: 1200px) {
        #heroCarousel .carousel-caption {
            left: 7%;
            width: 80%;
        }

        #heroCarousel .carousel-caption h1 {
            font-size: 3rem;
        }

        #heroCarousel .carousel-caption p {
            font-size: 1.1rem;
        }
    }

    @media (max-width: 992px) {
        #heroCarousel .carousel-caption {
            left: 5%;
            width: 90%;
        }

        #heroCarousel .carousel-caption h1 {
            font-size: 2.5rem;
        }

        #heroCarousel .carousel-caption p {
            font-size: 1rem;
        }
    }

    @media (max-width: 768px) {
        #heroCarousel .carousel-caption {
            left: 3%;
            width: 94%;
            padding: 15px;
        }

        #heroCarousel .carousel-caption h1 {
            font-size: 2rem;
        }

        #heroCarousel .carousel-caption p {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        #heroCarousel .carousel-caption {
            left: 2%;
            width: 96%;
            padding: 10px;
        }

        #heroCarousel .carousel-caption h1 {
            font-size: 1.5rem;
        }

        #heroCarousel .carousel-caption p {
            font-size: 0.8rem;
            margin-bottom: 15px;
        }

        #heroCarousel .carousel-caption .btn {
            padding: 8px 15px;
            font-size: 0.9rem;
        }
    }

    /* Berita Terbaru Section Styles */
    #berita .card-img-top {
        width: 100%;
        height: 250px;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    @media (min-width: 1200px) and (max-width: 1600px) {
        #berita .card-img-top {
            height: 400px;
        }
    }

    @media (min-width: 1600px) {
        #berita .card-img-top {
            height: 500px;
        }
    }

    #berita .card:hover .card-img-top {
        transform: scale(1.05);
    }

    /* Footer Styles */
    #kontak {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: var(--text-light);
        padding: 60px 0;
        width: 100vw;
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
        bottom: 0;
        margin-top: auto;
    }

    #kontak .container-fluid {
        max-width: 1200px;
        margin: 0 auto;
        padding-left: 15px;
        padding-right: 15px;
    }

    #kontak .row {
        display: flex;
        flex-wrap: wrap;
        margin-left: -15px;
        margin-right: -15px;
    }

    #kontak .col-md-4 {
        padding-left: 15px;
        padding-right: 15px;
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
    }

    #kontak a {
        display: inline-block;
        padding: 5px 0;
        transition: all 0.3s ease;
    }

    #kontak a:hover {
        transform: translateX(5px);
        color: rgba(255,255,255,0.8);
    }

    #kontak .social-icons a {
        padding: 10px;
        margin-right: 10px;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    #kontak .social-icons a:hover {
        background: rgba(255,255,255,0.2);
        transform: scale(1.1);
    }

    /* Responsive Adjustments for Footer */
    @media (max-width: 992px) {
        #kontak {
            width: 100%;
            left: 0;
            right: 0;
            margin-left: 0;
            margin-right: 0;
        }

        #kontak .col-md-4 {
            flex: 0 0 100%;
            max-width: 100%;
            text-align: center;
            margin-bottom: 30px;
        }

        #kontak .social-icons {
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        #kontak {
            padding: 40px 0;
        }

        #kontak .container-fluid {
            padding-left: 15px;
            padding-right: 15px;
        }
    }

    /* Global Responsive Typography */
    @media (max-width: 768px) {
        body {
            font-size: 14px;
        }

        h1 {
            font-size: 2rem;
        }

        h2 {
            font-size: 1.75rem;
        }

        h3 {
            font-size: 1.5rem;
        }
    }

    /* Smooth Scrolling */
    html {
        scroll-behavior: smooth;
    }

    /* Scrollbar Styling */
    ::-webkit-scrollbar {
        width: 10px;
    }

    ::-webkit-scrollbar-track {
        background: var(--background-light);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 5px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--secondary-color);
    }

    /* Accessibility Improvements */
    a, button {
        transition: all 0.3s ease;
    }

    a:focus, button:focus {
        outline: 2px solid var(--primary-color);
        outline-offset: 3px;
    }

    /* Print Styles */
    @media print {
        body {
            font-size: 12pt;
        }

        .navbar, #kontak {
            display: none;
        }
    }

    /* Animation Keyframes */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fadeIn 0.8s ease-out;
    }

    /* Performance Optimization */
    .lazy-load {
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
    }

    .lazy-load.loaded {
        opacity: 1;
    }

    /* Mobile Touch Improvements */
    @media (max-width: 768px) {
        a, button {
            min-height: 44px;
            min-width: 44px;
        }

        .navbar-toggler {
            padding: 10px;
        }
    }

    /* Responsive Image Handling */
    img {
        max-width: 100%;
        height: auto;
    }
    </style>
</head>
<body>
    <!-- Primary Heading for SEO -->
    <h1 class="visually-hidden">Pramuka Taruna Bangsa - Soefat Nasa Bekasi</h1>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid px-5">
            <div class="d-flex align-items-center">
                <a class="navbar-brand d-flex align-items-center" href="#">
                    <img src="assets/logo.png" alt="Logo Pramuka Taruna Bangsa" height="50" class="me-2">
                    Soefat Nasa
                </a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#heroCarousel">
                            <i class="fas fa-home me-2"></i>Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#berita">
                            <i class="fas fa-newspaper me-2"></i>Berita
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang">
                            <i class="fas fa-info-circle me-2"></i>Tentang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kegiatan">
                            <i class="fas fa-campground me-2"></i>Kegiatan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="organization-chart.php">
                            <i class="fas fa-trophy me-2"></i>Struktur Organisasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kontak">
                            <i class="fas fa-envelope me-2"></i>Kontak
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Carousel -->
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/baner/pramukabaner.png" class="d-block w-100" alt="Banner Pramuka Taruna Bangsa Soefat Nasa" loading="eager">
                <div class="carousel-caption">
                    <h2>Gerakan Pramuka Taruna Bangsa</h2>
                    <p class="lead">Membentuk Karakter Generasi Muda yang Tangguh dan Berkarakter di Bekasi</p>
                    <a href="registration.php" class="btn btn-lg btn-success">Bergabung Sekarang</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Berita Terbaru Section -->
    <section id="berita" class="py-5 bg-light">
       <div class="container">
            <h2 class="text-center mb-5 fw-bold">Berita Terbaru Pramuka</h2>
            <div class="row">
                <?php 
                // Periksa apakah ada berita
                if (mysqli_num_rows($result) > 0):
                    while($berita = mysqli_fetch_assoc($result)): 
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-img-container" style="height: 250px; overflow: hidden;">
                            <?php if(!empty($berita['gambar'])): ?>
                                <img 
                                    src="<?= htmlspecialchars($berita['gambar']) ?>" 
                                    class="card-img-top" 
                                    alt="<?= htmlspecialchars($berita['judul']) ?>" 
                                    style="width: 100%; height: 100%; object-fit: cover;"
                                >
                            <?php else: ?>
                                <img 
                                    src="assets/placeholder-berita.jpg" 
                                    class="card-img-top" 
                                    alt="Placeholder Berita" 
                                    style="width: 100%; height: 100%; object-fit: cover;"
                                >
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-success mb-3">
                                <?= htmlspecialchars($berita['judul']) ?>
                            </h5>
                            <p class="card-text text-muted flex-grow-1">
                                <?= substr(strip_tags($berita['konten']), 0, 100) ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <a 
                                    href="detail_news.php?id=<?= $berita['id'] ?>" 
                                    class="btn btn-sm btn-outline-success"
                                >
                                    Baca Selengkapnya
                                </a>
                                <small class="text-muted">
                                    <i class="far fa-calendar me-2"></i>
                                    <?= date('d M Y', strtotime($berita['tanggal'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                <div class="col-12 text-center">
                    <div class="alert alert-warning" role="alert">
                        Tidak ada berita yang tersedia saat ini.
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <a href="news.php" class="btn btn-success">
                    Lihat Semua Berita <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>
    <!-- Tentang Pramuka Taruna Bangsa Section -->
    <section id="tentang-taruna-bangsa" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold">Pramuka Taruna Bangsa - Soefat Nasa</h2>
            <p>Gerakan Pramuka Soefat Nasa, dikenal sebagai Soefat TB, adalah bagian dari Pramuka Taruna Bangsa yang berfokus pada pembentukan karakter, kepemimpinan, dan keterampilan hidup di Bekasi. Bergabunglah dengan kegiatan pramuka kami untuk pengalaman yang bermakna!</p>
        </div>
    </section>

    <!-- Tentang Pramuka Section -->
    <section id="tentang" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-4 fw-bold text-success">Apa Itu Gerakan Pramuka Soefat Nasa?</h2>
                    <p class="text-muted">
                        Gerakan Pramuka Soefat Nasa, bagian dari Pramuka Taruna Bangsa (Soefat TB), adalah organisasi kepanduan yang bertujuan membentuk karakter generasi muda yang beriman, bertakwa, berakhlak mulia, berjiwa patriotik, taat hukum, disiplin, dan memiliki kecakapan hidup.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Membina mental spiritual
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Mengembangkan keterampilan kepemimpinan
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Melatih kedisiplinan dan kemandirian
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <img src="assets/tentang.jpg" alt="Kegiatan Pramuka Soefat TB Bekasi" class="img-fluid rounded shadow-lg" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <!-- Kegiatan Pramuka Section -->
    <section id="kegiatan" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold">Kegiatan Pramuka Taruna Bangsa</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="assets/pelantikan.jpg" class="card-img-top" alt="Pelantikan Bantara Laksana Soefat TB" loading="lazy">
                        <div class="card-body">
                            <h3 class="card-title text-success">Pelantikan Bantara Laksana</h3>
                            <p class="card-text text-muted">
                                Prosesi pelantikan anggota Pramuka tingkat Bantara dan Laksana untuk meningkatkan dedikasi dan tanggung jawab sebagai Pramuka sejati.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Kegiatan Khusus</span>
                                <small class="text-muted">Juni 2024</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="assets/tenteng.jpg" class="card-img-top" alt="Bakti Sosial Pramuka Soefat Nasa" loading="lazy">
                        <div class="card-body">
                            <h3 class="card-title text-success">Bakti Sosial</h3>
                            <p class="card-text text-muted">
                                Kegiatan pengabdian masyarakat untuk menumbuhkan jiwa kepedulian sosial dan nasionalisme.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-info">Peduli Lingkungan</span>
                                <small class="text-muted">Agustus 2024</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="assets/rabu.jpg" class="card-img-top" alt="Latihan Rutin Pramuka Taruna Bangsa" loading="lazy">
                        <div class="card-body">
                            <h3 class="card-title text-success">Latihan Rutin Hari Rabu</h3>
                            <p class="card-text text-muted">
                                Kegiatan pembinaan rutin untuk meningkatkan keterampilan kepramukaan, kedisiplinan, dan semangat kebersamaan.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary">Pembinaan Anggota</span>
                                <small class="text-muted">Setiap Rabu/Sabtu</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="semua_kegiatan.php" class="btn btn-success">
                    Lihat Semua Kegiatan <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Prestasi Section -->
    <section id="prestasi" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold">Prestasi Pramuka Indonesia</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body">
                            <i class="fas fa-trophy text-warning fa-3x mb-3"></i>
                            <h3 class="text-success">Juara Nasional</h3>
                            <p class="text-muted">Perkemahan Nasional Pramuka 2023</p>
                            <span class="badge bg-success">Juara 1 Tingkat Nasional</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body">
                            <i class="fas fa-medal text-primary fa-3x mb-3"></i>
                            <h3 class="text-success">Penghargaan Internasional</h3>
                            <p class="text-muted">Lomba Kepemimpinan ASEAN</p>
                            <span class="badge bg-info">Delegasi Terbaik</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body">
                            <i class="fas fa-globe text-danger fa-3x mb-3"></i>
                            <h3 class="text-success">Pengakuan Global</h3>
                            <p class="text-muted">Kontribusi Kepemudaan</p>
                            <span class="badge bg-warning">Apresiasi Internasional</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="kontak" class="bg-dark text-white py-5">
        <div class="container-fluid px-md-5 px-3">
            <div class="row">
                <div class="col-md-4">
                    <h3>Kontak Kami</h3>
                    <p class="sp-contact-email"><i class="fa fa-envelope"></i> <a href="mailto:soefattarunabangsa@gmail.com">soefattarunabangsa@gmail.com</a></p>
                    <p class="sp-contact-phone"><i class="fa fa-phone"></i> <a href="https://wa.me/6285182145822">+62 851-8214-5822</a></p>
                    <p>Jl.Lingkar Utara Bekasi Kel. Perwira Kec. Bekasi Utara (sebelah BSI Kaliabang) Raya Bekasi KM.27 Pondok Ungu</p>
                </div>
                <div class="col-md-4">
                    <h3>Link Cepat</h3>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Beranda</a></li>
                        <li><a href="pendaftaran-pramuka.php" class="text-white">Pendaftaran Pramuka</a></li>
                        <li><a href="news.php" class="text-white">Berita Soefat TB</a></li>
                        <li><a href="organization-chart.php" class="text-white">Struktur Organisasi</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h3>Media Sosial</h3>
                    <div class="social-icons">
                        <a href="https://web.facebook.com/smktarunabangsabekasiofficial/" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/pramuka_nasa/" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.youtube.com/@smktarunabangsabekasi4045" class="text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <div class="text-center">
                <p>© 2025 Pramuka Taruna Bangsa - Soefat Nasa. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Modal Sukses Pendaftaran -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Pendaftaran Berhasil</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                    <h4>Terima Kasih!</h4>
                    <p>Pendaftaran Anda telah kami terima. Tim kami akan segera menghubungi Anda.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form pendaftaran
            const pendaftaranForm = document.getElementById('pendaftaranForm');
            if (pendaftaranForm) {
                pendaftaranForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (pendaftaranForm.checkValidity()) {
                        const formData = new FormData(pendaftaranForm);
                        fetch('proses_pendaftaran.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                                successModal.show();
                                pendaftaranForm.reset();
                            } else {
                                alert('Pendaftaran gagal: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan. Silakan coba lagi.');
                        });
                    } else {
                        pendaftaranForm.classList.add('was-validated');
                    }
                }, false);
            }

            // Smooth scrolling
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // Navbar scroll effect
            const navbar = document.querySelector('.navbar');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('bg-dark');
                } else {
                    navbar.classList.remove('bg-dark');
                }
            });

            // Lazy load images
            document.querySelectorAll('img.lazy-load').forEach(img => {
                img.addEventListener('load', () => img.classList.add('loaded'));
            });
        });
    </script>
</body>
</html>

<?php
// Tutup koneksi database
mysqli_free_result($result);
mysqli_close($koneksi);
?>