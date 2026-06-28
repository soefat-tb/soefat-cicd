<?php
session_start();
include '../config/database.php'; // Assumes config/database.php exists for database connection

// Check if user is logged in
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: dashboard.php");
    exit();
}

// Fetch organizational chart data
$stmt = $koneksi->prepare("SELECT * FROM org_chart ORDER BY level ASC, sort_order ASC");
$stmt->execute();
$result = $stmt->get_result();
$org_data = [];
while ($row = $result->fetch_assoc()) {
    $org_data[$row['level']][] = $row;
}
$stmt->close();

// Define level titles
$level_titles = [
    1 => 'Kepemimpinan Utama',
    3 => 'Pradana & Pemangku Adat',
    4 => 'Pimpinan Sangga',
    5 => 'Kerani',
    6 => 'Juru Uang',
    7 => 'Humas & Acara'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bagan Organisasi Pramuka Soefat</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c8030;  /* Hijau pramuka */
            --secondary-color: #4a7c59;  /* Hijau lebih gelap */
            --accent-color: #FFD700;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --card-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
            line-height: 1.6;
            padding-bottom: 0;
        }
        
        .navbar-custom {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            padding: 0.7rem 0;
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
            color: white !important;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-right: 10px;
            font-size: 1.8rem;
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 10px;
        }
        
        .nav-link:hover {
            color: var(--accent-color) !important;
            transform: translateY(-2px);
        }
        
        .dashboard-link {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            padding: 5px 15px !important;
            margin-left: 15px;
            display: flex;
            align-items: center;
        }
        
        .dashboard-link i {
            margin-right: 5px;
        }
        
        .dashboard-link:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        .banner {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 3rem 1rem;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
            border-radius: 0 0 15px 15px;
            box-shadow: var(--card-shadow);
        }
        
        .banner::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('/api/placeholder/1200/300');
            background-size: cover;
            background-position: center;
            opacity: 0.2;
            z-index: 0;
        }
        
        .banner-content {
            position: relative;
            z-index: 1;
        }
        
        .banner h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        .banner p {
            font-size: 1.2rem;
            font-weight: 300;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .org-chart {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
            position: relative;
            display: inline-block;
            padding: 0 20px;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
            margin: 10px auto 0;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 2.5rem;
            margin-top: 4rem;
        }
        
        .hierarchy {
            position: relative;
        }
        
        .level {
            position: relative;
            margin-bottom: 4rem;
        }
        
        .level::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            width: 2px;
            height: 2rem;
            background-color: var(--primary-color);
            transform: translateX(-50%);
        }
        
        .level:last-child::after {
            display: none;
        }
        
        .level-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            text-align: center;
            border: none;
            position: relative;
            overflow: hidden;
            height: 100%;
            width: 100%;
            max-width: 320px;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background-color: var(--secondary-color);
        }
        
        .card.primary::before {
            background-color: var(--primary-color);
        }
        
        .card.accent::before {
            background-color: var(--accent-color);
        }
        
        .position {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }
        
        .name {
            font-size: 1.1rem;
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .title {
            font-size: 0.9rem;
            color: #6c757d;
            font-style: italic;
            margin-top: 0.3rem;
        }
        
        .divider {
            height: 2px;
            background-color: var(--accent-color);
            width: 50px;
            margin: 1rem auto;
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 4rem;
        }
        
        .footer-content {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            margin: 1rem 0;
        }
        
        .footer-links a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--accent-color);
        }
        
        .copyright {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .no-data {
            text-align: center;
            color: #6c757d;
            font-size: 1.2rem;
            margin: 2rem 0;
        }
        
        @media (max-width: 991px) {
            .banner h1 {
                font-size: 2.3rem;
            }
            
            .banner p {
                font-size: 1rem;
            }
            
            .card {
                max-width: 280px;
            }
        }
        
        @media (max-width: 768px) {
            .level-container {
                gap: 15px;
            }
            
            .card {
                padding: 1.2rem;
                max-width: 100%;
            }
            
            .banner {
                padding: 2rem 1rem;
            }
            
            .banner h1 {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
            
            .position {
                font-size: 1.1rem;
            }
            
            .name {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .banner h1 {
                font-size: 1.8rem;
            }
            
            .level {
                margin-bottom: 3rem;
            }
            
            .level::after {
                height: 1.5rem;
            }
            
            .section-header {
                margin-top: 3rem;
                margin-bottom: 2rem;
            }
            
            .card {
                padding: 1rem;
            }
            
            .position {
                font-size: 1rem;
            }
            
            .navbar-brand {
                font-size: 1.3rem;
            }
            
            .navbar-brand i {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-award"></i>
                Soefat Nasa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link dashboard-link" href="dashboard.php">
                            <i class="bi bi-arrow-left-circle"></i>
                            Dashboard
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Banner -->
    <div class="banner">
        <div class="banner-content">
            <h1>Struktur Organisasi Pramuka</h1>
            <p>Gugus Depan Soefat Tahun 2025</p>
        </div>
    </div>

    <!-- Organizational Chart -->
    <div class="org-chart">
        <div class="hierarchy">
            <?php if (empty($org_data)): ?>
                <div class="no-data">Tidak ada data organisasi tersedia.</div>
            <?php else: ?>
                <?php foreach ($org_data as $level => $members): ?>
                    <div class="level">
                        <?php if (isset($level_titles[$level])): ?>
                            <div class="section-header">
                                <h2 class="section-title"><?= htmlspecialchars($level_titles[$level]) ?></h2>
                            </div>
                        <?php endif; ?>
                        <div class="level-container">
                            <?php foreach ($members as $member): ?>
                                <div class="card <?= htmlspecialchars($member['card_style']) ?>">
                                    <div class="position"><?= htmlspecialchars($member['position']) ?></div>
                                    <div class="divider"></div>
                                    <div class="name"><?= htmlspecialchars($member['name']) ?></div>
                                    <?php if (!empty($member['title'])): ?>
                                        <div class="title"><?= htmlspecialchars($member['title']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-links">
                    <a href="dashboard.php"><i class="bi bi-house-door"></i> Beranda</a>
                    <a href="../news.php"><i class="bi bi-calendar-event"></i> Berita</a>
                </div>
                <p class="copyright">© 2025 Organisasi Pramuka Soefat. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>