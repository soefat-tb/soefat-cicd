<?php
session_start();

// Include auth-check for basic session validation
require_once 'auth.php'; // Pastikan file ini ada

// Redirect to login page if not authenticated
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['login'] = true;
    }
}

// Ambil data user dari session atau database
if (!isset($_SESSION['nis']) || !isset($_SESSION['nama_lengkap'])) {
    if (isset($_SESSION['credential_id'])) {
        $host = 'sql309.byetcluster.com';
        $username = 'if0_37650982';
        $password = 'soefat135767991';
        $database = 'if0_37650982_p';

        $koneksi = mysqli_connect($host, $username, $password, $database);
        if (!$koneksi) {
            error_log("Koneksi gagal: " . mysqli_connect_error());
            die("Koneksi gagal: " . mysqli_connect_error());
        }

        $credential_id = mysqli_real_escape_string($koneksi, $_SESSION['credential_id']);
        $query = "SELECT nis FROM credentials WHERE rawId = '$credential_id' LIMIT 1";
        $result = mysqli_query($koneksi, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['nis'] = $user['nis'];

            // Ambil nama dari tabel siswa berdasarkan nis
            $nis = mysqli_real_escape_string($koneksi, $user['nis']);
            $query_siswa = "SELECT nama FROM siswa WHERE nis = '$nis' LIMIT 1";
            $result_siswa = mysqli_query($koneksi, $query_siswa);

            if ($result_siswa && mysqli_num_rows($result_siswa) > 0) {
                $siswa = mysqli_fetch_assoc($result_siswa);
                $_SESSION['nama_lengkap'] = $siswa['nama'];
            } else {
                error_log("Nama tidak ditemukan untuk nis: $nis");
                header("Location: logout.php");
                exit();
            }
        } else {
            error_log("Data user tidak ditemukan untuk credential_id: $credential_id");
            header("Location: logout.php");
            exit();
        }
        mysqli_close($koneksi);
    } else {
        error_log("credential_id tidak ada di session");
        header("Location: logout.php");
        exit();
    }
}

$nis = $_SESSION['nis'];
$nama_lengkap = $_SESSION['nama_lengkap'];

// Database Configuration
$host = 'sql309.byetcluster.com';
$username = 'if0_37650982';
$password = 'soefat135767991';
$database = 'if0_37650982_p';

$koneksi = mysqli_connect($host, $username, $password, $database);
if (!$koneksi) {
    error_log("Koneksi gagal: " . mysqli_connect_error());
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Attendance Form Processing
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST request received: " . print_r($_POST, true));
    $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $alasan = filter_input(INPUT_POST, 'alasan', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
    $longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);
    $waktu = date('Y-m-d H:i:s');
    $today = date('Y-m-d');

    // Log data sebelum validasi
    error_log("Data: nis=$nis, nama=$nama, status=$status, latitude=$latitude, longitude=$longitude");

    // Cek apakah user sudah absen hari ini
    $stmt = $koneksi->prepare("SELECT COUNT(*) FROM absensi WHERE nis = ? AND DATE(waktu) = ?");
    if ($stmt === false) {
        error_log("Prepare gagal: " . $koneksi->error);
        $error = "Error di query cek absensi: " . $koneksi->error;
    } else {
        $stmt->bind_param("ss", $nis, $today);
        if (!$stmt->execute()) {
            error_log("Eksekusi gagal: " . $stmt->error);
            $error = "Error eksekusi query: " . $stmt->error;
        } else {
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $error = "Anda sudah melakukan presensi hari ini!";
            } elseif ($nama !== $nama_lengkap) {
                $error = "Nama tidak valid! Anda hanya bisa absen untuk akun sendiri.";
            } elseif (!empty($nama) && !empty($status) && $latitude !== false && $longitude !== false) {
                $stmt = $koneksi->prepare("INSERT INTO absensi (nis, nama, status, alasan, latitude, longitude, waktu) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt === false) {
                    error_log("Prepare insert gagal: " . $koneksi->error);
                    $error = "Error prepare insert: " . $koneksi->error;
                } else {
                    $stmt->bind_param("ssssdds", $nis, $nama, $status, $alasan, $latitude, $longitude, $waktu);
                    if (!$stmt->execute()) {
                        error_log("Insert gagal: " . $stmt->error);
                        $error = "Error menyimpan data: " . $stmt->error;
                    } else {
                        $_SESSION['success'] = true;
                        header('Location: dashboard.php');
                        exit();
                    }
                    $stmt->close();
                }
            } else {
                $error = "Harap lengkapi semua data dan ambil lokasi!";
            }
        }
    }
}

if (isset($_SESSION['success']) && $_SESSION['success'] === true) {
    $success = "Presensi berhasil disimpan!";
    unset($_SESSION['success']);
}
mysqli_close($koneksi);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Presensi Pramuka - SMKS Taruna Bangsa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #1e8a3e;
            --secondary-color: #135a28;
            --accent-color: #ffc107;
            --text-color: #333;
            --text-light: #6c757d;
            --text-white: #fff;
            --bg-light: #f8f9fa;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.15);
            --card-radius: 16px;
            --btn-radius: 10px;
            --transition: all 0.3s ease;
        }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f0f2f5;
            color: var(--text-color);
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow-x: hidden;
        }
        .app-container {
            max-width: 500px;
            height: 100vh;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow-y: auto;
        }
        .header {
            background: var(--primary-color);
            padding: 1.2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }
        .header .brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .header .brand img {
            width: 36px;
            height: 36px;
        }
        .header .brand-text {
            display: flex;
            flex-direction: column;
        }
        .header .brand-text .app-name {
            font-weight: 700;
            font-size: 1.2rem;
        }
        .header .brand-text .app-subtitle {
            font-size: 0.7rem;
            opacity: 0.9;
        }
        .digital-clock {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
        }
        .digital-clock #time {
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }
        .digital-clock #date {
            font-size: 0.7rem;
            opacity: 0.9;
        }
        .presensi-wrapper {
            flex: 1;
            padding: 0.5rem 1.5rem 2rem;
            overflow-y: auto;
        }
        .form-presensi {
            background: white;
            border-radius: var(--card-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        .form-presensi .form-header {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
            position: relative;
        }
        .form-presensi .form-header .icon-badge {
            width: 64px;
            height: 64px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.5rem;
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            box-shadow: var(--shadow-sm);
        }
        .form-presensi .form-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .form-presensi .form-header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .form-presensi .form-body {
            padding: 1.5rem;
        }
        .form-control {
            border-radius: var(--btn-radius);
            border: 1px solid #dee2e6;
            transition: var(--transition);
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(30, 138, 62, 0.25);
        }
        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }
        .status-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .status-card {
            flex: 1;
            padding: 1rem;
            border-radius: var(--btn-radius);
            background: var(--bg-light);
            cursor: pointer;
            text-align: center;
            transition: var(--transition);
            border: 2px solid transparent;
        }
        .status-card:hover {
            background: #e9ecef;
        }
        .status-card.active {
            border-color: var(--primary-color);
            background: rgba(30, 138, 62, 0.1);
        }
        .status-card .status-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .status-card .status-icon i.fa-check-circle {
            color: var(--primary-color);
        }
        .status-card .status-icon i.fa-times-circle {
            color: #dc3545;
        }
        #alasan-section {
            display: none;
            margin-bottom: 1.5rem;
            opacity: 0;
            transition: var(--transition);
        }
        #alasan-section.show {
            display: block;
            opacity: 1;
        }
        .location-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }
        .location-status i {
            font-size: 1.2rem;
        }
        .location-status.found {
            color: var(--primary-color);
        }
        .location-status.found i {
            color: #28a745;
        }
        .location-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .location-row .btn {
            flex: 1;
            border-radius: var(--btn-radius);
            font-weight: 600;
            transition: var(--transition);
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
        }
        .btn-primary:hover {
            background: var(--secondary-color);
        }
        .btn-dark {
            background: #343a40;
            border: none;
        }
        .btn-dark:hover {
            background: #23272b;
        }
        .btn-dark:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .pulse {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .alert {
            border-radius: var(--btn-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-success {
            background: rgba(30, 138, 62, 0.1);
            border-left: 4px solid #28a745;
            color: #28a745;
        }
        .alert-danger {
            background: rgba(229, 57, 53, 0.1);
            border-left: 4px solid #dc3545;
            color: #dc3545;
        }
        .bottom-nav {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 0.8rem 0;
            display: flex;
            justify-content: space-around;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-light);
            font-size: 0.8rem;
            transition: var(--transition);
        }
        .nav-item i {
            font-size: 1.2rem;
            margin-bottom: 0.2rem;
        }
        .nav-item.active {
            color: var(--primary-color);
        }
        .nav-item:hover {
            color: var(--primary-color);
        }
        @media (max-width: 576px) {
            .app-container {
                width: 100%;
            }
            .presensi-wrapper {
                padding: 0.5rem 1rem 1.5rem;
            }
            .form-presensi .form-header {
                padding: 1rem;
            }
            .form-presensi .form-header h2 {
                font-size: 1.5rem;
            }
            .form-presensi .form-header .icon-badge {
                width: 48px;
                height: 48px;
                font-size: 1.2rem;
                top: 1rem;
                right: 1rem;
            }
            .status-selector {
                flex-direction: column;
            }
            .location-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <div class="header">
            <div class="brand">
                <i class="fas fa-campground"></i>
                <div class="brand-text">
                    <span class="app-name">Form Presensi</span>
                    <span class="app-subtitle">SMKS Taruna Bangsa</span>
                </div>
            </div>
            <div class="digital-clock" id="clock">
                <span id="time"></span>
                <small id="date"></small>
            </div>
        </div>

        <!-- Attendance Content -->
        <div class="presensi-wrapper">
            <div class="form-presensi">
                <div class="form-header">
                    <div class="icon-badge">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h2>Presensi Kegiatan</h2>
                    <p>Silakan isi form presensi untuk kegiatan hari ini</p>
                </div>
                
                <div class="form-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="attendanceForm">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($nama_lengkap) ?>" readonly>
                            <input type="hidden" name="nama" id="nama" value="<?= htmlspecialchars($nama_lengkap) ?>" required>
                        </div>

                        <label class="form-label">Status Kehadiran</label>
                        <div class="status-selector">
                            <div class="status-card present active" onclick="setStatus('Hadir')">
                                <div class="status-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="status-label">Hadir</div>
                            </div>
                            <div class="status-card absent" onclick="setStatus('Tidak Hadir')">
                                <div class="status-icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="status-label">Tidak Hadir</div>
                            </div>
                        </div>

                        <div id="alasan-section">
                            <label for="alasan" class="form-label">Alasan Ketidakhadiran</label>
                            <textarea name="alasan" id="alasan" class="form-control" placeholder="Jelaskan alasan ketidakhadiran Anda..." rows="3"></textarea>
                        </div>

                        <div id="locationStatus" class="location-status">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Lokasi belum diambil</span>
                        </div>

                        <div class="location-row">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-primary" onclick="getLocation()">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Ambil Lokasi
                                </button>
                            </div>
                            <button type="submit" class="btn btn-dark" id="submitBtn" disabled>
                                <i class="fas fa-paper-plane"></i>
                                Kirim Presensi
                            </button>
                        </div>

                        <input type="hidden" name="status" id="status" value="Hadir" required>
                        <input type="hidden" name="latitude" id="latitude" required>
                        <input type="hidden" name="longitude" id="longitude" required>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Beranda</span>
            </a>
            <a href="attendance.php" class="nav-item active">
                <i class="fas fa-calendar-check"></i>
                <span>Presensi</span>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation and submission
        document.getElementById('attendanceForm').addEventListener('submit', function(e) {
            const nama = document.querySelector('input[name="nama"]').value;
            const status = document.getElementById('status').value;
            const latitude = document.getElementById('latitude').value;
            const longitude = document.getElementById('longitude').value;
            
            if (!nama || !status || !latitude || !longitude) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Form Tidak Lengkap',
                    text: 'Silakan lengkapi semua data dan ambil lokasi!',
                    confirmButtonColor: '#1e8a3e'
                });
            } else {
                Swal.fire({
                    title: 'Mengirim...',
                    text: 'Presensi sedang diproses',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
        });

        function getLocation() {
            if (navigator.geolocation) {
                const locationStatus = document.getElementById('locationStatus');
                locationStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Sedang mengambil lokasi...</span>';
                
                Swal.fire({
                    title: 'Memuat...',
                    text: 'Mengambil lokasi Anda',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                const geoOptions = {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                };
                
                navigator.geolocation.getCurrentPosition(
                    position => {
                        Swal.close();
                        document.getElementById('latitude').value = position.coords.latitude;
                        document.getElementById('longitude').value = position.coords.longitude;
                        document.getElementById('submitBtn').disabled = false;
                        document.getElementById('submitBtn').classList.add('pulse');
                        
                        locationStatus.innerHTML = '<i class="fas fa-check-circle"></i><span>Lokasi berhasil diambil</span>';
                        locationStatus.classList.add('found');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Lokasi Ditemukan',
                            text: 'Lokasi Anda berhasil diambil',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error => {
                        Swal.close();
                        let errorMessage = '';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = "Izin lokasi ditolak. Silakan izinkan akses lokasi.";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = "Informasi lokasi tidak tersedia.";
                                break;
                            case error.TIMEOUT:
                                errorMessage = "Permintaan lokasi timeout.";
                                break;
                            default:
                                errorMessage = "Terjadi error tidak diketahui.";
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errorMessage,
                            confirmButtonColor: '#1e8a3e'
                        });
                        
                        locationStatus.innerHTML = `<i class="fas fa-exclamation-circle"></i><span>${errorMessage}</span>`;
                    },
                    geoOptions
                );
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Browser Tidak Mendukung',
                    text: 'Fitur geolokasi tidak tersedia di browser ini',
                    confirmButtonColor: '#1e8a3e'
                });
            }
        }

        function setStatus(status) {
            document.getElementById('status').value = status;
            if (status === 'Hadir') {
                document.querySelector('.status-card.present').classList.add('active');
                document.querySelector('.status-card.absent').classList.remove('active');
                document.getElementById('alasan-section').classList.remove('show');
                setTimeout(() => {
                    document.getElementById('alasan').value = '';
                }, 300);
            } else {
                document.querySelector('.status-card.absent').classList.add('active');
                document.querySelector('.status-card.present').classList.remove('active');
                document.getElementById('alasan-section').classList.add('show');
            }
        }

        function updateClock() {
            const now = new Date();
            const timeOptions = { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
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

        let lastActivity = <?= time() ?>;
        const sessionTimeout = 1800;
        function checkSession() {
            const currentTime = Math.floor(Date.now() / 1000);
            if (currentTime - lastActivity > sessionTimeout) {
                window.location.href = 'logout.php?reason=timeout';
            }
        }
        setInterval(checkSession, 5000);

        ['click', 'mousemove', 'keypress', 'touchstart', 'scroll'].forEach(event => {
            document.addEventListener(event, () => {
                lastActivity = Math.floor(Date.now() / 1000);
            });
        });
    </script>
</body>
</html>