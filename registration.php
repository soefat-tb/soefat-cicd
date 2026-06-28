<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config/database.php';

$pesan_status = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nama'], $_POST['kelas'], $_POST['nomor_telepon'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $nomor_telepon = mysqli_real_escape_string($koneksi, $_POST['nomor_telepon']);
    $pesan = mysqli_real_escape_string($koneksi, $_POST['pesan'] ?? '');

    if (empty($kelas)) {
        $pesan_status = 'invalid_kelas';
    } elseif (!preg_match('/^08[1-9]\d{8,9}$/', $nomor_telepon)) {
        $pesan_status = 'invalid_telepon';
    } else {
        $stmt = $koneksi->prepare("INSERT INTO pendaftaran (nama, kelas, nomor_telepon, pesan, tanggal_daftar) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $nama, $kelas, $nomor_telepon, $pesan);
        $pesan_status = $stmt->execute() ? 'success' : 'error';
        $stmt->close();
    }
    $koneksi->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pramuka NASA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">

    <style>
        :root {
            --primary-green: #305900; /* Deep olive green */
            --secondary-green: #4CAF50; /* Vibrant green */
            --accent-green: #A8D5A2; /* Soft mint green */
            --light-bg: #F5F8F1; /* Pale green-tinted white */
            --text-dark: #1A3C34; /* Deep forest green */
            --text-muted: #6B7280; /* Neutral gray */
            --error-red: #DC3545; /* Error state */
            --shadow: 0 4px 12px rgba(48, 89, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 16px;
            overflow: hidden; /* Prevent scrolling */
        }

        .bg-overlay {
            position: fixed;
            inset: 0;
            background: repeating-linear-gradient(
                45deg,
                var(--accent-green) 0,
                var(--accent-green) 4px,
                transparent 4px,
                transparent 8px
            );
            opacity: 0.1;
            z-index: -1;
        }

        .container {
            max-width: 500px;
            width: 100%;
            z-index: 10;
        }

        .back-button {
            position: fixed;
            top: 16px;
            left: 16px;
            background: var(--light-bg);
            padding: 8px 16px;
            border-radius: 50px;
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 2px solid var(--accent-green);
            box-shadow: var(--shadow);
            transition: var(--transition);
            animation: bounceIn 0.6s ease-out;
            z-index: 1000;
        }

        .back-button:hover {
            background: var(--primary-green);
            color: var(--light-bg);
            transform: scale(1.1);
            border-color: var(--secondary-green);
        }

        .back-button i {
            transition: transform 0.3s ease;
        }

        .back-button:hover i {
            transform: rotate(-45deg);
        }

        @keyframes bounceIn {
            0% { opacity: 0; transform: scale(0.3); }
            50% { opacity: 1; transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        .registration-form {
            background: var(--light-bg);
            border-radius: 12px;
            padding: 24px;
            box-shadow: var(--shadow);
            animation: slideUp 0.6s ease-out;
            max-height: calc(100vh - 32px); /* Fit within viewport */
            overflow: hidden;
        }

        .registration-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, var(--primary-green), var(--secondary-green));
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-header {
            text-align: center;
            margin-bottom: 16px;
        }

        .logo-circle {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            border-radius: 50%;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--light-bg);
            font-size: 1.5rem;
            box-shadow: 0 4px 8px rgba(48, 89, 0, 0.2);
            transition: var(--transition);
        }

        .logo-circle:hover {
            transform: scale(1.05);
        }

        .form-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 6px;
            font-size: 0.85rem;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 0.9rem;
            background: white;
            transition: var(--transition);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(48, 89, 0, 0.1);
            transform: translateY(-1px);
            outline: none;
        }

        .form-group textarea {
            resize: none;
            height: 80px; /* Reduced height to fit screen */
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        .input-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus + .input-icon,
        .form-group textarea:focus + .input-icon {
            color: var(--primary-green);
            transform: translateY(-50%) scale(1.1);
        }

        .error-message {
            color: var(--error-red);
            font-size: 0.8rem;
            margin-top: 4px;
            display: none;
            line-height: 1.3;
            animation: shake 0.3s ease-in-out;
        }

        .error-message.show {
            display: block;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-3px); }
            75% { transform: translateX(3px); }
        }

        .form-group.error input,
        .form-group.error textarea {
            border-color: var(--error-red);
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: var(--light-bg);
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            background: linear-gradient(135deg, var(--secondary-green), var(--primary-green));
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.4s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .submit-btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            border: 2px solid var(--light-bg);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .progress-bar {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 12px;
        }

        .progress-step {
            width: 20px;
            height: 20px;
            background: #D1D5DB;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .progress-step.active {
            background: var(--primary-green);
            color: var(--light-bg);
        }

        @media (max-width: 640px) {
            .registration-form {
                padding: 20px;
                max-height: calc(100vh - 48px);
            }

            .back-button {
                top: 12px;
                padding: 8px 12px;
                font-size: 0.85rem;
            }

            .form-header h1 {
                font-size: 1.25rem;
            }

            .form-header p {
                font-size: 0.85rem;
            }

            .logo-circle {
                width: 48px;
                height: 48px;
                font-size: 1.2rem;
            }

            .form-group input,
            .form-group textarea {
                padding: 8px 12px;
                font-size: 0.85rem;
            }

            .form-group textarea {
                height: 60px;
            }

            .submit-btn {
                padding: 10px;
                font-size: 0.9rem;
            }

            .progress-step {
                width: 16px;
                height: 16px;
                font-size: 0.65rem;
            }
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --light-bg: #2D3748;
                --text-dark: #F7FAFC;
                --text-muted: #A0AEC0;
            }

            .registration-form {
                background: var(--light-bg);
            }

            .form-group input,
            .form-group textarea {
                background: #4A5568;
                color: var(--text-dark);
                border-color: #718096;
            }

            .back-button {
                background: #4A5568;
                color: var(--accent-green);
            }

            .progress-step {
                background: #718096;
            }
        }
    </style>
</head>
<body>
    <div class="bg-overlay"></div>
    <a href="../../" class="back-button">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    <div class="container">
        <div class="registration-form">
            <div class="form-header">
                <div class="logo-circle">
                    <i class="fas fa-leaf"></i>
                </div>
                <h1>Pendaftaran Pramuka NASA</h1>
                <p>Jelajahi alam bersama kami!</p>
            </div>

            <div class="progress-bar">
                <div class="progress-step active">1</div>
                <div class="progress-step">2</div>
                <div class="progress-step">3</div>
            </div>

            <form id="registrationForm" method="POST">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <div class="input-wrapper">
                        <input type="text" id="nama" name="nama" placeholder="Masukkan nama" required aria-describedby="namaError">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                    <div class="error-message" id="namaError">Nama minimal 3 karakter</div>
                </div>

                <div class="form-group">
                    <label for="kelas">Kelas</label>
                    <div class="input-wrapper">
                        <input type="text" id="kelas" name="kelas" placeholder="Contoh: XII IPA 1" required aria-describedby="kelasError">
                        <i class="fas fa-graduation-cap input-icon"></i>
                    </div>
                    <div class="error-message" id="kelasError">Kelas wajib diisi</div>
                </div>

                <div class="form-group">
                    <label for="nomor_telepon">Nomor Telepon</label>
                    <div class="input-wrapper">
                        <input type="tel" id="nomor_telepon" name="nomor_telepon" placeholder="08xxxxxxxxxx" required aria-describedby="teleponError">
                        <i class="fas fa-phone input-icon"></i>
                    </div>
                    <div class="error-message" id="teleponError">Nomor harus 11-12 digit, diawali 08</div>
                </div>

                <div class="form-group">
                    <label for="pesan">Motivasi (Opsional)</label>
                    <textarea id="pesan" name="pesan" placeholder="Ceritakan motivasi Anda..."></textarea>
                </div>

                <button type="submit" class="submit-btn">Daftar</button>
            </form>
        </div>
    </div>

    <script>
        const form = document.getElementById('registrationForm');
        const inputs = {
            nama: document.getElementById('nama'),
            kelas: document.getElementById('kelas'),
            telepon: document.getElementById('nomor_telepon'),
            pesan: document.getElementById('pesan')
        };
        const submitBtn = document.querySelector('.submit-btn');
        const progressSteps = document.querySelectorAll('.progress-step');

        function showError(input, errorId, message) {
            const errorElement = document.getElementById(errorId);
            input.closest('.form-group').classList.add('error');
            errorElement.textContent = message;
            errorElement.classList.add('show');
            input.setAttribute('aria-invalid', 'true');
        }

        function clearError(input, errorId) {
            const errorElement = document.getElementById(errorId);
            input.closest('.form-group').classList.remove('error');
            errorElement.classList.remove('show');
            input.setAttribute('aria-invalid', 'false');
        }

        function validateNama() {
            const value = inputs.nama.value.trim();
            if (value.length < 3) {
                showError(inputs.nama, 'namaError', 'Nama minimal 3 karakter');
                return false;
            }
            clearError(inputs.nama, 'namaError');
            updateProgress(1);
            return true;
        }

        function validateKelas() {
            const value = inputs.kelas.value.trim();
            if (!value) {
                showError(inputs.kelas, 'kelasError', 'Kelas wajib diisi');
                return false;
            }
            clearError(inputs.kelas, 'kelasError');
            updateProgress(2);
            return true;
        }

        function validateTelepon() {
            const value = inputs.telepon.value.replace(/\D/g, '');
            const teleponRegex = /^08[1-9]\d{8,9}$/;
            if (!teleponRegex.test(value)) {
                showError(inputs.telepon, 'teleponError', 'Nomor harus 11-12 digit, diawali 08');
                return false;
            }
            clearError(inputs.telepon, 'teleponError');
            updateProgress(3);
            return true;
        }

        function updateProgress(step) {
            progressSteps.forEach((s, index) => {
                s.classList.toggle('active', index < step);
            });
        }

        inputs.telepon.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 12);
            if (this.value && !this.value.startsWith('08')) {
                this.value = '08' + this.value.replace(/^0+/, '');
            }
            validateTelepon();
        });

        Object.values(inputs).forEach(input => {
            input.addEventListener('input', () => {
                switch (input.id) {
                    case 'nama': validateNama(); break;
                    case 'kelas': validateKelas(); break;
                    case 'nomor_telepon': validateTelepon(); break;
                }
            });

            input.addEventListener('focus', () => {
                input.closest('.input-wrapper').style.transform = 'translateY(-1px)';
                input.closest('.form-group').classList.add('focused');
            });

            input.addEventListener('blur', () => {
                input.closest('.input-wrapper').style.transform = 'translateY(0)';
                input.closest('.form-group').classList.remove('focused');
            });
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const isValid = validateNama() && validateKelas() && validateTelepon();

            if (!isValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Lengkap',
                    text: 'Silakan lengkapi semua field dengan benar.',
                    confirmButtonColor: '#F39C12'
                });
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Pendaftaran',
                text: 'Apakah data yang Anda masukkan sudah benar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#305900',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Daftar',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    submitBtn.classList.add('loading');
                    submitBtn.textContent = '';

                    try {
                        await new Promise(resolve => setTimeout(resolve, 1000));
                        form.submit();
                    } catch (error) {
                        submitBtn.classList.remove('loading');
                        submitBtn.textContent = 'Daftar';
                        Swal.fire({
                            icon: 'error',
                            title: 'Pendaftaran Gagal',
                            text: 'Terjadi kesalahan. Silakan coba lagi.',
                            confirmButtonColor: '#DC3545'
                        });
                    }
                }
            });
        });

        <?php if ($pesan_status): ?>
            Swal.fire({
                icon: '<?php echo $pesan_status === 'success' ? 'success' : ($pesan_status === 'error' ? 'error' : 'warning'); ?>',
                title: '<?php 
                    echo $pesan_status === 'success' ? 'Pendaftaran Berhasil!' :
                         ($pesan_status === 'error' ? 'Pendaftaran Gagal' :
                         ($pesan_status === 'invalid_kelas' ? 'Kelas Tidak Valid' : 'Nomor Telepon Tidak Valid'));
                ?>',
                html: '<?php 
                    echo $pesan_status === 'success' ? 'Selamat! Anda telah terdaftar sebagai anggota Pramuka NASA.<br>Kami akan menghubungi Anda segera.' :
                         ($pesan_status === 'error' ? 'Terjadi kesalahan saat memproses pendaftaran.' :
                         ($pesan_status === 'invalid_kelas' ? 'Silakan masukkan kelas Anda dengan benar.' :
                         'Nomor telepon harus 11-12 digit dan diawali dengan 08.'));
                ?>',
                confirmButtonColor: '<?php 
                    echo $pesan_status === 'success' ? '#305900' :
                         ($pesan_status === 'error' ? '#DC3545' : '#F39C12');
                ?>',
                timer: <?php echo $pesan_status === 'success' ? '5000' : 'undefined'; ?>
            });
        <?php endif; ?>
    </script>
</body>
</html>
