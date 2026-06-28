<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit();
}

$result = $koneksi->query("SELECT * FROM absensi ORDER BY waktu DESC");

// Filter berdasarkan tanggal jika ada
$filterDate = isset($_GET['date']) ? $_GET['date'] : '';
$whereClause = '';

if (!empty($filterDate)) {
    $filterDateObj = new DateTime($filterDate);
    $filterFormatted = $filterDateObj->format('Y-m-d');
    $whereClause = " WHERE DATE(waktu) = '$filterFormatted'";
    $result = $koneksi->query("SELECT * FROM absensi $whereClause ORDER BY waktu DESC");
}

// Pagination
$itemsPerPage = 10;
$totalItems = $result->num_rows;
$totalPages = ceil($totalItems / $itemsPerPage);
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

$paginatedQuery = "SELECT * FROM absensi" . $whereClause . " ORDER BY waktu DESC LIMIT $offset, $itemsPerPage";
$paginatedResult = $koneksi->query($paginatedQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
:root {
    --primary-color: #2c8030;
    --primary-light: #e7f2e8;
    --primary-hover: #39a540;
    --primary-dark: #216423;
    --text-dark: #2c3e50;
    --text-light: #6c757d;
    --bg-light: #ffffff;
    --border-color: #dee2e6;
    --success-color: #28a745;
    --warning-color: #dd6b20;
    --danger-color: #dc3545;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-card: 0 4px 12px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
    --border-radius: 8px;
    --border-radius-sm: 6px;
}

body {
    background-color: var(--bg-light);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--text-dark);
    line-height: 1.6;
    min-height: 100vh;
    padding-bottom: 70px;
    font-size: 14px;
    overflow-x: hidden;
}

/* Header */
.header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    padding: 0.8rem 1rem;
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
    gap: 0.5rem;
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

/* Bottom Navigation */
.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--bg-light);
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 0.6rem 0;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    border-top: 1px solid var(--border-color);
}

.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: var(--text-light);
    font-size: 0.65rem;
    font-weight: 500;
    transition: all 0.3s;
    padding: 0.4rem;
    border-radius: 8px;
    min-width: 50px;
}

.nav-item.active {
    color: var(--primary-color);
    background: var(--primary-light);
}

.nav-item:hover {
    color: var(--primary-color);
    background: rgba(44, 128, 48, 0.05);
}

.nav-item i {
    font-size: 1.2rem;
    margin-bottom: 0.3rem;
    transition: transform 0.3s;
}

.nav-item.active i {
    transform: scale(1.15);
}

/* Content Styles */
.content-container {
    padding: 1rem;
    max-width: 100%;
    margin: 70px auto 70px;
    width: 100%;
}

.card {
    background: var(--bg-light);
    border-radius: var(--border-radius);
    border-top: 4px solid var(--primary-color);
    box-shadow: var(--shadow-card);
    transition: var(--transition);
    margin-bottom: 1rem;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-width: 100%;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
}

.card-item {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-color);
}

.card-item:last-child {
    border-bottom: none;
}

.card-label {
    font-weight: 600;
    color: var(--text-dark);
    min-width: 60px;
    flex-shrink: 0;
}

.card-value {
    flex-grow: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Filter Controls */
.filter-container {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
    padding: 1rem 0;
    background: var(--bg-light);
    border-bottom: 1px solid var(--border-color);
}

.date-filter {
    padding: 0.5rem 0.8rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-sm);
    font-size: 0.9rem;
    flex-grow: 1;
    max-width: 200px;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    color: var(--text-dark);
}

.date-filter:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(44, 128, 48, 0.25);
    outline: none;
}

.filter-btn, .reset-btn {
    background-color: var(--primary-color);
    color: var(--white);
    border: none;
    border-radius: var(--border-radius-sm);
    padding: 0.5rem 1rem;
    font-weight: 600;
    font-size: 0.9rem;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.4rem;
    box-shadow: var(--shadow-sm);
    text-decoration: none;
}

.filter-btn:hover, .reset-btn:hover {
    background-color: var(--primary-hover);
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.reset-btn {
    background-color: var(--text-light);
}

.reset-btn:hover {
    background-color: #4a5568;
}

/* Status Badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.7rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
}

.status-hadir {
    background-color: rgba(40, 167, 69, 0.12);
    color: var(--success-color);
    border: 1px solid rgba(40, 167, 69, 0.3);
}

.status-izin {
    background-color: rgba(221, 107, 32, 0.12);
    color: var(--warning-color);
    border: 1px solid rgba(221, 107, 32, 0.3);
}

.status-sakit {
    background-color: rgba(220, 53, 69, 0.12);
    color: var(--danger-color);
    border: 1px solid rgba(220, 53, 69, 0.3);
}

/* Map Button */
.map-btn {
    display: inline-flex;
    align-items: center;
    background-color: var(--primary-color);
    color: var(--white);
    border: none;
    border-radius: var(--border-radius-sm);
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 600;
    transition: var(--transition);
    text-decoration: none;
    gap: 0.4rem;
    box-shadow: var(--shadow-sm);
}

.map-btn:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.8rem;
    border-top: 1px solid var(--border-color);
    background: var(--bg-light);
}

.page-info {
    font-size: 0.9rem;
    color: var(--text-light);
    font-weight: 500;
}

.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 0.3rem;
}

.page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    height: 2rem;
    padding: 0 0.6rem;
    border-radius: var(--border-radius-sm);
    color: var(--text-dark);
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
}

.page-link:hover {
    background-color: var(--primary-light);
    color: var(--primary-dark);
    transform: translateY(-2px);
}

.page-item.active .page-link {
    background-color: var(--primary-color);
    color: var(--white);
    border-color: var(--primary-color);
}

.page-item.disabled .page-link {
    color: var(--text-light);
    pointer-events: none;
    opacity: 0.6;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    text-align: center;
}

.empty-icon {
    font-size: 3rem;
    color: var(--text-light);
    margin-bottom: 1rem;
    opacity: 0.6;
}

.empty-text {
    font-size: 1rem;
    color: var(--text-light);
    margin-bottom: 1.5rem;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

/* Responsive Adjustments */
@media (max-width: 576px) {
    .content-container {
        padding: 0.5rem;
    }

    .card {
        padding: 0.8rem;
        margin-bottom: 0.8rem;
    }

    .card-item {
        padding: 0.3rem 0;
    }

    .card-label {
        min-width: 50px;
        font-size: 0.85rem;
    }

    .card-value {
        font-size: 0.85rem;
    }

    .filter-container {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
        padding: 0.8rem 0;
    }

    .date-filter {
        max-width: 100%;
    }

    .filter-btn, .reset-btn {
        width: 100%;
        justify-content: center;
    }

    .status-badge {
        padding: 0.2rem 0.5rem;
        font-size: 0.7rem;
    }

    .map-btn {
        padding: 0.3rem 0.6rem;
        font-size: 0.75rem;
    }

    .pagination-container {
        flex-direction: column;
        gap: 0.5rem;
        padding: 0.5rem;
    }

    .page-info {
        text-align: center;
    }

    .pagination {
        justify-content: center;
    }

    .nav-item {
        font-size: 0.6rem;
    }

    .nav-item i {
        font-size: 1.1rem;
    }
}
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <i class="fas fa-clipboard-check"></i>
            Data Absensi
        </div>
        <div class="actions">
            <div class="user-avatar ripple-container">
                <i class="fas fa-user"></i>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="content-container animate-fade-in">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-list-alt"></i>
                    Daftar Absensi
                </h2>
                <form action="" method="get" style="flex: 1; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <input type="date" name="date" class="date-filter" id="dateFilter" value="<?= $filterDate ?>">
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i>
                        Filter
                    </button>
                    <?php if (!empty($filterDate)): ?>
                    <a href="?<?= http_build_query(array_diff_key($_GET, array('date' => ''))) ?>" class="reset-btn">
                        <i class="fas fa-times"></i>
                        Reset
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if($paginatedResult->num_rows > 0): ?>
            <?php while($row = $paginatedResult->fetch_assoc()): 
                $datetime = new DateTime($row['waktu']);
                $adjustedDateTime = clone $datetime;
                $adjustedDateTime->modify('+11 hours');
            ?>
            <div class="card">
                <div class="card-item">
                    <div class="card-label">#</div>
                    <div class="card-value"><?= ($currentPage - 1) * $itemsPerPage + $paginatedResult->data_seek_count + 1 ?></div>
                </div>
                <div class="card-item">
                    <div class="card-label">Nama</div>
                    <div class="card-value" title="<?= htmlspecialchars($row['nama']) ?>"><?= htmlspecialchars($row['nama']) ?></div>
                </div>
                <div class="card-item">
                    <div class="card-label">Status</div>
                    <div class="card-value">
                        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
                            <i class="<?= getStatusIcon($row['status']) ?> me-1"></i>
                            <?= $row['status'] ?>
                        </span>
                    </div>
                </div>
                <div class="card-item">
                    <div class="card-label">Alasan</div>
                    <div class="card-value" title="<?= !empty($row['alasan']) ? htmlspecialchars($row['alasan']) : '-' ?>">
                        <?= !empty($row['alasan']) ? htmlspecialchars($row['alasan']) : '-' ?>
                    </div>
                </div>
                <div class="card-item">
                    <div class="card-label">Waktu</div>
                    <div class="card-value" title="<?= $datetime->format('d F Y, H:i') ?>">
                        <?= $adjustedDateTime->format('d M Y, H:i') ?>
                    </div>
                </div>
                <div class="card-item">
                    <div class="card-label">Lokasi</div>
                    <div class="card-value">
                        <a href="https://www.google.com/maps?q=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>"
                        target="_blank" class="map-btn" title="Lihat di Google Maps">
                            <i class="fas fa-map-marker-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="far fa-calendar-times"></i>
                </div>
                <p class="empty-text">
                    <?= !empty($filterDate) ? 'Tidak ada data absensi pada tanggal yang dipilih' : 'Tidak ada data absensi' ?>
                </p>
                <?php if (!empty($filterDate)): ?>
                <a href="?<?= http_build_query(array_diff_key($_GET, array('date' => ''))) ?>" class="reset-btn">
                    <i class="fas fa-undo"></i>
                    Tampilkan Semua Data
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if($totalItems > 0): ?>
            <div class="pagination-container">
                <div class="page-info">
                    Menampilkan <?= min($offset + 1, $totalItems) ?>-<?= min($offset + $itemsPerPage, $totalItems) ?> dari <?= $totalItems ?> data
                </div>
                
                <?php if($totalPages > 1): ?>
                <ul class="pagination">
                    <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $currentPage-1 ?><?= !empty($filterDate) ? '&date='.$filterDate : '' ?>" aria-label="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    
                    <?php
                    $startPage = max(1, min($currentPage - 1, $totalPages - 2));
                    $endPage = min($totalPages, max($currentPage + 1, 3));
                    
                    if ($startPage > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=1'.(!empty($filterDate) ? '&date='.$filterDate : '').'">1</a></li>';
                        if ($startPage > 2) {
                            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                        }
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        echo '<li class="page-item '.($i == $currentPage ? 'active' : '').'">
                                <a class="page-link" href="?page='.$i.(!empty($filterDate) ? '&date='.$filterDate : '').'">'.$i.'</a>
                              </li>';
                    }
                    
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?page='.$totalPages.(!empty($filterDate) ? '&date='.$filterDate : '').'">'.$totalPages.'</a></li>';
                    }
                    ?>
                    
                    <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $currentPage+1 ?><?= !empty($filterDate) ? '&date='.$filterDate : '' ?>" aria-label="Next">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

   <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item ripple-container">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="credential-add.php" class="nav-item ripple-container">
            <i class="fas fa-user-plus"></i>
            <span>Data Siswa</span>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ripple effect for nav items
        document.querySelectorAll('.ripple-container').forEach(container => {
            container.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const ripple = document.createElement('span');
                ripple.className = 'ripple';
                const diameter = Math.max(this.clientWidth, this.clientHeight);
                const radius = diameter / 2;
                ripple.style.width = ripple.style.height = `${diameter}px`;
                ripple.style.left = `${e.clientX - rect.left - radius}px`;
                ripple.style.top = `${e.clientY - rect.top - radius}px`;
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Card animation
        document.querySelectorAll('.card').forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = "0";
                card.style.animation = "fadeIn 0.3s ease-out forwards";
                card.style.animationDelay = (index * 0.05) + "s";
            }, 100);
        });

        // Check if date filter is active
        const dateFilter = document.getElementById('dateFilter');
        if (dateFilter.value) {
            const resetBtn = document.querySelector('.reset-btn');
            if (resetBtn) {
                resetBtn.classList.add('animate-fade-in');
            }
        }
    });

    // Ripple effect styles
    const style = document.createElement('style');
    style.innerHTML = `
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
    `;
    document.head.appendChild(style);
    </script>

    <?php
    // Helper function untuk mendapatkan icon berdasarkan status
    function getStatusIcon($status) {
        $status = strtolower($status);
        switch ($status) {
            case 'hadir':
                return 'fas fa-check-circle';
            case 'izin':
                return 'fas fa-calendar-minus';
            case 'sakit':
                return 'fas fa-procedures';
            default:
                return 'fas fa-info-circle';
        }
    }
    ?>
</body>
</html>
<?php $koneksi->close(); ?>