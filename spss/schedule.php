<?php
session_start();
if (!isset($_SESSION['login']) || !$_SESSION['login']) {
    header("Location: dashboard.php");
    exit();
}

// Koneksi ke database
include '../config/database.php';

$query = mysqli_query($koneksi, "SELECT * FROM jadwal ORDER BY hari, waktu ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Jadwal - Siswa Pramuka Mobile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c8030;
            --secondary-color: #4a7c59;
            --background: linear-gradient(135deg, #f8f9fa 0%, #e9f5ec 100%);
            --card-bg: rgba(255, 255, 255, 0.98);
            --text-color: #2d3436;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --spacing: 1.5rem;
        }

        body {
            background: var(--background);
            color: var(--text-color);
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        .app-container {
            max-width: 100%;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--card-bg);
            overflow: hidden;
        }

        .header {
            background: var(--primary-color);
            color: white;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }

        .clock-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .digital-clock {
            font-size: 1.1rem;
            font-weight: 500;
        }

        .schedule-container {
            flex: 1;
            padding: var(--spacing);
            overflow-y: auto;
            margin-top: var(--spacing);
        }

        .day-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: var(--spacing);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .day-card:hover {
            transform: translateY(-3px);
        }

        .schedule-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            width: 100%;
            cursor: pointer;
        }

        .schedule-item:last-child {
            border-bottom: none;
        }

        .schedule-time {
            min-width: 110px;
            font-weight: 600;
            color: var(--primary-color);
            padding: 0.5rem;
            border-radius: 8px;
            background: rgba(44, 128, 48, 0.1);
            text-align: center;
            flex-shrink: 0;
        }

        .schedule-details {
            flex: 1;
            min-width: 0;
        }

        .schedule-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .schedule-meta {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .schedule-meta i {
            width: 18px;
            text-align: center;
        }

        .schedule-note {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .bottom-nav {
            background: white;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.8rem 0;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            position: sticky;
            bottom: 0;
            z-index: 50;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
            padding: 0.5rem;
            position: relative;
        }

        .nav-item.active {
            color: var(--primary-color);
        }

        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            width: 5px;
            height: 5px;
            background: var(--primary-color);
            border-radius: 50%;
        }

        @media (max-width: 576px) {
            .schedule-container {
                padding: 0.75rem;
                margin-top: 0.75rem;
            }
            .day-card {
                padding: 1rem;
                margin-bottom: 0.75rem;
            }
            .schedule-item {
                flex-direction: column;
                gap: 0.5rem;
                padding: 1rem 0;
            }
            .schedule-time {
                width: 100%;
                font-size: 0.95rem;
            }
            .schedule-title {
                font-size: 1.05rem;
            }
            .nav-item span {
                font-size: 0.85rem;
            }
        }

        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        .modal-body {
            max-height: 60vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <div class="header">
            <div class="clock-container">
                <i class="fas fa-clock"></i>
                <div class="digital-clock" id="clock">
                    <div id="time"></div>
                    <small id="date"></small>
                </div>
            </div>
            <div class="profile">
                <a href="profile-edit.php"><i class="fas fa-user-circle"></i></a>
            </div>
        </div>

        <!-- Konten Jadwal -->
        <div class="schedule-container">
            <?php
            $current_day = '';
            while ($row = mysqli_fetch_assoc($query)) {
                if ($current_day !== $row['hari']) {
                    if ($current_day !== '') echo '</div>';
                    echo '<div class="day-card">';
                    echo '<h5 class="mb-3 text-primary"><i class="fas fa-calendar-day me-2"></i>' . htmlspecialchars($row['hari']) . '</h5>';
                    $current_day = $row['hari'];
                }
                ?>
                <div class="schedule-item" data-bs-toggle="modal" data-bs-target="#detailModal<?= $row['id'] ?>">
                    <div class="schedule-time"><?= htmlspecialchars($row['waktu']) ?></div>
                    <div class="schedule-details">
                        <div class="schedule-title"><?= htmlspecialchars($row['judul']) ?></div>
                        <div class="schedule-meta">
                            <small class="text-muted"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($row['lokasi']) ?></small>
                            <small class="text-muted"><i class="fas fa-user-tie"></i> Pembina: <?= htmlspecialchars($row['pembina']) ?></small>
                            <small class="text-muted"><i class="fas fa-tshirt"></i> Pakaian: <?= htmlspecialchars($row['pakaian'] ?: 'Tidak ditentukan') ?></small>
                            <small class="text-muted schedule-note"><i class="fas fa-sticky-note"></i> Catatan: <?= htmlspecialchars($row['note'] ?: 'Tidak ada catatan') ?></small>
                        </div>
                    </div>
                </div>

                <!-- Modal untuk Detail -->
                <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="detailModalLabel<?= $row['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailModalLabel<?= $row['id'] ?>"><?= htmlspecialchars($row['judul']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Hari:</strong> <?= htmlspecialchars($row['hari']) ?></p>
                                <p><strong>Waktu:</strong> <?= htmlspecialchars($row['waktu']) ?></p>
                                <p><strong>Lokasi:</strong> <?= htmlspecialchars($row['lokasi']) ?></p>
                                <p><strong>Pembina:</strong> <?= htmlspecialchars($row['pembina']) ?></p>
                                <p><strong>Pakaian:</strong> <?= htmlspecialchars($row['pakaian'] ?: 'Tidak ditentukan') ?></p>
                                <p><strong>Catatan:</strong> <?= nl2br(htmlspecialchars($row['note'] ?: 'Tidak ada catatan')) ?></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            if ($current_day !== '') echo '</div>';
            if (mysqli_num_rows($query) === 0) {
                echo '<div class="day-card text-center text-muted">Tidak ada jadwal tersedia.</div>';
            }
            mysqli_data_seek($query, 0); // Reset query pointer for potential reuse
            ?>
        </div>

        <!-- Navigasi Bawah -->
        <nav class="bottom-nav">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Beranda</span>
            </a>
            <a href="attendance.php" class="nav-item">
                <i class="fas fa-calendar-check"></i>
                <span>Presensi</span>
            </a>
            <a href="schedule.php" class="nav-item active">
                <i class="fas fa-calendar-days"></i>
                <span>Jadwal</span>
            </a>
            <a href="profile-edit.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span>Profil</span>
            </a>
        </nav>
    </div>

    <script>
        // Jam dan Tanggal Real-Time
        function updateClock() {
            const now = new Date();
            const timeOptions = { 
                hour: '2-digit', 
                minute: '2-digit', 
                hour12: false 
            };
            const dateOptions = {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            };
            
            document.getElementById('time').textContent = now.toLocaleTimeString('id-ID', timeOptions);
            document.getElementById('date').textContent = now.toLocaleDateString('id-ID', dateOptions);
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Session Management
        let lastActivity = Date.now() / 1000;
        const sessionTimeout = 1800; // 30 menit

        setInterval(() => {
            if (Date.now() / 1000 - lastActivity > sessionTimeout) {
                window.location.href = 'logout.php';
            }
        }, 5000);

        document.addEventListener('click', () => lastActivity = Date.now() / 1000);

        // Interaksi Item Jadwal
        document.querySelectorAll('.schedule-item').forEach(item => {
            item.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 100);
            });
        });

        // Perbaikan Scroll Behavior
        const scheduleContainer = document.querySelector('.schedule-container');
        scheduleContainer.addEventListener('scroll', () => {
            if (scheduleContainer.scrollTop > 0) {
                scheduleContainer.style.paddingTop = '0.5rem';
            } else {
                scheduleContainer.style.paddingTop = 'var(--spacing)';
            }
        });
    </script>
</body>
</html>