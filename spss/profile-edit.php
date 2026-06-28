<?php
session_start();

// Include auth-check untuk validasi session
require_once 'auth.php'; // Pastikan file ini ada dan berfungsi

// Konfigurasi database
$host = "sql309.infinityfree.com";
$user = "if0_37650982";
$password = "soefat135767991";
$dbname = "if0_37650982_p";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data siswa berdasarkan nis dari session
$nis = $_SESSION['nis'];
$stmt = $conn->prepare("SELECT * FROM siswa WHERE nis = ?");
$stmt->bind_param("s", $nis);
$stmt->execute();
$result = $stmt->get_result();
$siswa = $result->fetch_assoc();

if (!$siswa) {
    die("Data siswa tidak ditemukan.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_data'])) {
        $fields = [
            'nama', 'kelas', 'nomor_telepon', 'tingkatan', 'email', 'status',
            'jenis_kelamin', 'nisn', 'nik', 'tempat_lahir', 'tanggal_lahir',
            'agama', 'kewarganegaraan', 'berkebutuhan_khusus', 'alamat', 'kode_pos',
            'tempat_tinggal', 'mode_transportasi', 'nomor_kks', 'anak_ke', 'asal_sekolah', 'no_telp_rumah'
        ];
        
        $update_sql = "UPDATE siswa SET ";
        $params = [];
        $types = '';
        
        foreach ($fields as $field) {
            $update_sql .= "$field = ?, ";
            $params[] = $_POST[$field];
            $types .= 's';
        }
        
        $update_sql = rtrim($update_sql, ', ') . " WHERE nis = ?";
        $params[] = $nis;
        $types .= 's';
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Data berhasil diperbarui!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    
    if (isset($_POST['change_password'])) {
        $new_password = $_POST['new_password']; // Ambil password langsung dari input
        
        // Pastikan tabel dan nama kolom sesuai dengan struktur database
        $stmt = $conn->prepare("UPDATE login SET password = ? WHERE nis = ?");
        $stmt->bind_param("ss", $new_password, $nis);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Password berhasil diubah!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - Pramuka</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2c8030;
            --secondary-color: #4a7c59;
            --background: #f5f5f5;
            --card-bg: #ffffff;
            --text-color: #333333;
            --border-color: #dddddd;
        }

        body {
            background: var(--background);
            color: var(--text-color);
            min-height: 100vh;
        }

        .navbar-custom {
            background: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .notification {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            background: #4CAF50;
            color: white;
            animation: fadeIn 0.5s ease-in;
        }

        .card-custom {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }

        .form-custom input:focus, 
        .form-custom select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(44, 128, 48, 0.2);
        }

        .btn-custom {
            background: var(--primary-color);
            color: white;
            transition: all 0.3s ease;
            padding: 0.75rem 1.5rem;
        }

        .btn-custom:hover {
            background: var(--secondary-color);
            color: white;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .password-toggle {
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .password-toggle:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <!-- Navbar Bootstrap -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-graduation-cap fs-4 me-2"></i>
                <span class="fw-bold">SISWA PRAMUKA</span>
            </a>
            
            <button class="navbar-toggler" type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" 
                    aria-expanded="false" 
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center" href="dashboard.php">
                            <i class="fas fa-home me-2"></i>Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if(isset($_SESSION['message'])): ?>
            <div class="notification" id="notification">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Form Data Pribadi -->
        <div class="card-custom">
            <h2 class="section-title"><i class="fas fa-user-circle me-2"></i>Data Pribadi</h2>
            <form method="POST" class="form-custom">
                <div class="row g-3">
                    <?php foreach([
                        // Format: [Label, Name, Icon, Type, Required, Readonly, Disabled]
                        ['Nama Lengkap', 'nama', 'user', 'text', true, false, true],
                        ['NIS', 'nis', 'id-card', 'text', false, true, true],
                        ['Kelas', 'kelas', 'school', 'text', true, false, false],
                        ['Nomor Telepon', 'nomor_telepon', 'phone', 'tel', true, false, false],
                        ['Email', 'email', 'envelope', 'email', true, false, false],
                        ['NISN', 'nisn', 'id-badge', 'text', false, false, false],
                        ['NIK', 'nik', 'address-card', 'text', false, false, false],
                        ['Tempat Lahir', 'tempat_lahir', 'map-marker-alt', 'text', false, false, false],
                        ['Tanggal Lahir', 'tanggal_lahir', 'calendar-day', 'date', false, false, false],
                        ['Agama', 'agama', 'pray', 'text', false, false, false],
                        ['Kewarganegaraan', 'kewarganegaraan', 'globe-asia', 'text', false, false, false],
                        ['Berkebutuhan Khusus', 'berkebutuhan_khusus', 'wheelchair', 'text', false, false, false],
                        ['Alamat', 'alamat', 'house-user', 'text', false, false, false],
                        ['Kode Pos', 'kode_pos', 'mail-bulk', 'text', false, false, false],
                        ['Tempat Tinggal', 'tempat_tinggal', 'building', 'text', false, false, false],
                        ['Mode Transportasi', 'mode_transportasi', 'bus', 'text', false, false, false],
                        ['Nomor KKS', 'nomor_kks', 'credit-card', 'text', false, false, false],
                        ['Anak ke Berapa', 'anak_ke', 'baby', 'number', false, false, false],
                        ['Asal Sekolah', 'asal_sekolah', 'graduation-cap', 'text', false, false, false],
                        ['No Telp Rumah', 'no_telp_rumah', 'phone-volume', 'text', false, false, false]
                    ] as $field): ?>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium">
                            <i class="fas fa-<?= $field[2] ?> me-2 text-primary"></i><?= $field[0] ?>
                        </label>
                        <input type="<?= $field[3] ?>" 
                               name="<?= $field[1] ?>" 
                               class="form-control border-2"
                               value="<?= htmlspecialchars($siswa[$field[1]] ?? '') ?>"
                               <?= $field[4] ? 'required' : '' ?>
                               <?= $field[5] ? 'readonly' : '' ?>
                               <?= $field[6] ? 'disabled' : '' ?>>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Field Select -->
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium"><i class="fas fa-layer-group me-2 text-primary"></i>Tingkatan</label>
                        <select name="tingkatan" class="form-select border-2" required disabled>
                            <option value="Bantara" <?= ($siswa['tingkatan'] == 'Bantara') ? 'selected' : '' ?>>Bantara</option>
                            <option value="Laksana" <?= ($siswa['tingkatan'] == 'Laksana') ? 'selected' : '' ?>>Laksana</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium"><i class="fas fa-user-check me-2 text-primary"></i>Status</label>
                        <select name="status" class="form-select border-2" required disabled>
                            <option value="Aktif" <?= ($siswa['status'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                            <option value="Tidak Aktif" <?= ($siswa['status'] == 'Tidak Aktif') ? 'selected' : '' ?>>Tidak Aktif</option>
                            <option value="Purna" <?= ($siswa['status'] == 'Purna') ? 'selected' : '' ?>>Purna</option>
                        </select>
                    </div>

                    <!-- Tambahkan input hidden untuk field yang disabled -->
                    <input type="hidden" name="nama" value="<?= htmlspecialchars($siswa['nama']) ?>">
                    <input type="hidden" name="nis" value="<?= htmlspecialchars($siswa['nis']) ?>">
                    <input type="hidden" name="tingkatan" value="<?= htmlspecialchars($siswa['tingkatan']) ?>">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($siswa['status']) ?>">

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium"><i class="fas fa-venus-mars me-2 text-primary"></i>Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select border-2" required>
                            <option value="Laki-laki" <?= ($siswa['jenis_kelamin'] == 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= ($siswa['jenis_kelamin'] == 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                </div>
                
                <div class="text-end mt-4">
                    <button type="submit" name="update_data" class="btn btn-custom">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Form Ganti Password -->
        <div class="card-custom mt-4">
            <h2 class="section-title"><i class="fas fa-lock me-2"></i>Ganti Password</h2>
            <form method="POST" class="form-custom">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium">Password Baru</label>
                        <div class="input-group">
                            <input type="password" 
                                   name="new_password" 
                                   id="new_password" 
                                   class="form-control border-2"
                                   required>
                            <button class="btn btn-outline-secondary password-toggle" 
                                    type="button" 
                                    onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="text-end mt-4">
                    <button type="submit" 
                            name="change_password" 
                            class="btn btn-custom">
                        <i class="fas fa-key me-2"></i>Ganti Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const passwordField = document.getElementById('new_password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Notification auto-hide
        const notification = document.getElementById('notification');
        if (notification) {
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>

<?php
    // Tutup koneksi database
    $conn->close();
?>