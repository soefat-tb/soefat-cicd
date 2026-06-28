<?php
session_start();

// Redirect to login page if not authenticated
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit();
}

// Regenerate session ID to prevent session fixation
session_regenerate_id(true);

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Database connection
include '../config/database.php';

try {
    // Query untuk mengambil data anggota menggunakan mysqli
    $query = "SELECT nama, kelas, tingkatan, status FROM siswa ORDER BY nama ASC";
    $result = mysqli_query($koneksi, $query);

    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($koneksi));
    }

    $anggota = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);

} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
    $anggota = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Data Anggota - Siswa Pramuka Mobile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #2c8030, #4a7c59);
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }
        .app-container {
            width: 100%;
            min-height: 100vh;
            overflow-y: auto;
            background: #ffffff;
            padding-bottom: 80px;
        }
        .header {
            background: #2c8030;
            color: white;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            margin: 0;
        }
        .back-button {
            font-size: 1.5rem;
            color: white;
            text-decoration: none;
        }
        .header-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        .content {
            padding: 1rem;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }
        .member-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #2c8030;
        }
        .member-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .member-info {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        .info-item {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .info-item i {
            margin-right: 0.5rem;
            width: 16px;
            color: #2c8030;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-aktif {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-tidak-aktif {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-purna {
            background-color: #fef3c7;
            color: #92400e;
        }
        .tingkatan-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            background-color: #e5f3ff;
            color: #1e40af;
            margin-top: 0.5rem;
            display: inline-block;
        }
        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }
        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s ease;
        }
        .search-input:focus {
            border-color: #2c8030;
        }
        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100vw;
            background: white;
            padding: 0.75rem 0;
            display: flex;
            justify-content: space-around;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
            margin: 0;
            z-index: 10;
        }
        .nav-item {
            text-align: center;
            color: #6b7280;
            font-size: 0.75rem;
            padding: 0.5rem;
            text-decoration: none;
            flex: 1;
        }
        .nav-item i {
            display: block;
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        .nav-item.active {
            color: #2c8030;
        }
        .nav-item.active i {
            color: #2c8030;
        }
        @media (min-width: 576px) {
            .header, .content {
                max-width: 576px;
                margin: 0 auto;
            }
        }
        @media (max-width: 400px) {
            .member-info {
                flex-direction: column;
                gap: 0.5rem;
            }
            .info-item {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="header">
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="header-title">Data Anggota</div>
            <div style="width: 24px;"></div>
        </div>
        
        <div class="content">
            <!-- Search Box -->
            <div class="search-box">
                <div class="search-icon">
                    <i class="fas fa-search"></i>
                </div>
                <input type="text" class="search-input" id="searchInput" placeholder="Cari nama anggota...">
            </div>
            
            <!-- Error Message -->
            <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>
            
            <!-- Member Cards -->
            <div id="memberList">
                <?php if (empty($anggota)): ?>
                <div class="no-data">
                    <i class="fas fa-users fa-3x mb-4 text-gray-300"></i>
                    <p>Tidak ada data anggota</p>
                </div>
                <?php else: ?>
                    <?php foreach ($anggota as $member): ?>
                    <div class="member-card" data-name="<?php echo strtolower(htmlspecialchars($member['nama'])); ?>">
                        <div class="member-name"><?php echo htmlspecialchars($member['nama']); ?></div>
                        <div class="member-info">
                            <div class="info-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span><?php echo htmlspecialchars($member['kelas']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-medal"></i>
                                <span><?php echo htmlspecialchars($member['tingkatan']); ?></span>
                            </div>
                        </div>
                        <div class="tingkatan-badge">
                            <?php echo htmlspecialchars($member['tingkatan']); ?>
                        </div>
                        <div style="margin-top: 0.75rem;">
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $member['status'])); ?>">
                                <?php echo htmlspecialchars($member['status']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bottom-nav">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Beranda</span>
            </a>
            <a href="attendance.php" class="nav-item active"> <!-- Blok kedua dari kiri ditandai active -->
                <i class="fas fa-user-friends"></i>
                <span>List Anggota</span>
            </a>
            <a href="profile-edit.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span>Profil</span>
            </a>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const memberCards = document.querySelectorAll('.member-card');
            
            memberCards.forEach(card => {
                const memberName = card.getAttribute('data-name');
                if (memberName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Session timeout
        let lastActivity = <?php echo $_SESSION['last_activity'] ?? time(); ?>;
        const sessionTimeout = 1800;
        setInterval(() => {
            if ((Date.now() / 1000) - lastActivity > sessionTimeout) {
                window.location.href = 'logout.php';
            }
        }, 5000);
        
        // Card click animation
        document.querySelectorAll('.member-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 100);
            });
        });
    </script>
</body>
</html>