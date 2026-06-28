<?php
// Koneksi ke database
$servername = "sql309.infinityfree.com";
$username = "if0_37650982";
$password = "soefat135767991";
$dbname = "if0_37650982_p";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
    exit();
}

// Proses form
$nisError = $teleponError = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nis = $_POST['nis'] ?? '';
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $kelas = $_POST['kelas'] ?? '';
    $nomorteleponaktif = $_POST['nomorteleponaktif'] ?? '';
    $emailaktif = $_POST['emailaktif'] ?? '';

    // Validasi NIS
    if (strlen($nis) < 9 || strlen($nis) > 11) {
        $nisError = "NIS harus 9-11 digit";
    }

    // Validasi Nomor Telepon
    if (strlen($nomorteleponaktif) < 10 || strlen($nomorteleponaktif) > 13) {
        $teleponError = "Nomor telepon harus 10-13 digit";
    }

    // Jika validasi lolos, simpan ke database
    if (empty($nisError) && empty($teleponError)) {
        try {
            $stmt = $conn->prepare("INSERT INTO data_siswa (nis, nama_lengkap, kelas, nomorteleponaktif, emailaktif) VALUES (:nis, :nama_lengkap, :kelas, :nomorteleponaktif, :emailaktif)");
            $stmt->bindParam(':nis', $nis);
            $stmt->bindParam(':nama_lengkap', $nama_lengkap);
            $stmt->bindParam(':kelas', $kelas);
            $stmt->bindParam(':nomorteleponaktif', $nomorteleponaktif);
            $stmt->bindParam(':emailaktif', $emailaktif);
            $stmt->execute();
            $success = "Data berhasil disimpan!";
        } catch(PDOException $e) {
            $success = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2c8030; /* Hijau pramuka */
            --primary-dark: #246b28;
            --secondary-color: #4a7c59; /* Hijau lebih gelap */
            --accent-color: #66bb6a; /* Hijau accent yang lebih terang */
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-gradient: linear-gradient(135deg, #2c8030 0%, #4a7c59 100%);
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --border-radius: 0.75rem;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 500px;
            position: relative;
        }

        .form-card {
            background: var(--bg-primary);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-xl);
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--bg-gradient);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-title {
            color: var(--text-primary);
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }

        .form-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1.125rem;
            z-index: 2;
            transition: var(--transition);
        }

        .form-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e5e7eb;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 400;
            color: var(--text-primary);
            background: var(--bg-primary);
            transition: var(--transition);
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgb(44 128 48 / 0.1); /* Menggunakan hijau pramuka untuk focus state */
        }

        .form-input:focus + .input-icon {
            color: var(--primary-color);
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: 0.025em;
        }

        .error-message {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            color: var(--error-color);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .success-message {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: rgb(16 185 129 / 0.1);
            border: 1px solid rgb(16 185 129 / 0.2);
            border-radius: var(--border-radius);
            color: var(--success-color);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            animation: slideIn 0.5s ease-out;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem 2rem;
            background: var(--bg-gradient);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.025em;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: -1;
        }

        .floating-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(44, 128, 48, 0.1); /* Menggunakan hijau pramuka dengan opacity */
            animation: float 6s ease-in-out infinite;
        }

        .floating-circle:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-circle:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 70%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-circle:nth-child(3) {
            width: 40px;
            height: 40px;
            top: 30%;
            right: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .input-counter {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.75rem;
            color: var(--text-secondary);
            background: var(--bg-secondary);
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
        }

        .form-input.error {
            border-color: var(--error-color);
            box-shadow: 0 0 0 3px rgb(239 68 68 / 0.1);
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            .form-card {
                padding: 1.5rem;
                margin: 0.5rem;
            }

            .form-title {
                font-size: 1.5rem;
            }

            .form-input {
                padding: 0.875rem 0.875rem 0.875rem 2.75rem;
            }

            .input-icon {
                left: 0.875rem;
                font-size: 1rem;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --text-primary: #f9fafb;
                --text-secondary: #9ca3af;
                --bg-primary: #1f2937;
                --bg-secondary: #374151;
            }
        }

        /* Accessibility improvements */
        .form-input:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        .submit-btn:focus-visible {
            outline: 2px solid var(--accent-color);
            outline-offset: 2px;
        }

        /* Loading state */
        .submit-btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .submit-btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="floating-elements">
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
    </div>

    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <h1 class="form-title">Pendaftaran Siswa</h1>
                <p class="form-subtitle">Lengkapi data diri Anda dengan benar</p>
            </div>

            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="studentForm">
                <div class="form-group">
                    <label class="form-label" for="nis">Nomor Induk Siswa (NIS)</label>
                    <div class="input-wrapper">
                        <input 
                            type="number" 
                            id="nis" 
                            name="nis" 
                            class="form-input <?php echo !empty($nisError) ? 'error' : ''; ?>" 
                            value="<?php echo isset($_POST['nis']) ? htmlspecialchars($_POST['nis']) : ''; ?>" 
                            required
                            maxlength="11"
                            placeholder="Masukkan NIS (9-11 digit)"
                        >
                        <i class="input-icon fas fa-id-card"></i>
                        <span class="input-counter" id="nisCounter">0/11</span>
                    </div>
                    <?php if (!empty($nisError)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span><?php echo $nisError; ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nama_lengkap">Nama Lengkap</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="nama_lengkap" 
                            name="nama_lengkap" 
                            class="form-input" 
                            value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>" 
                            required
                            placeholder="Masukkan nama lengkap"
                        >
                        <i class="input-icon fas fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="kelas">Kelas</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="kelas" 
                            name="kelas" 
                            class="form-input" 
                            value="<?php echo isset($_POST['kelas']) ? htmlspecialchars($_POST['kelas']) : ''; ?>" 
                            required
                            placeholder="Contoh: X RPL 1"
                        >
                        <i class="input-icon fas fa-graduation-cap"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nomorteleponaktif">Nomor Telepon Aktif</label>
                    <div class="input-wrapper">
                        <input 
                            type="tel" 
                            id="nomorteleponaktif" 
                            name="nomorteleponaktif" 
                            class="form-input <?php echo !empty($teleponError) ? 'error' : ''; ?>" 
                            value="<?php echo isset($_POST['nomorteleponaktif']) ? htmlspecialchars($_POST['nomorteleponaktif']) : ''; ?>" 
                            required
                            maxlength="13"
                            placeholder="Contoh: 081234567890"
                        >
                        <i class="input-icon fas fa-phone"></i>
                        <span class="input-counter" id="phoneCounter">0/13</span>
                    </div>
                    <?php if (!empty($teleponError)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span><?php echo $teleponError; ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="emailaktif">Email Aktif</label>
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            id="emailaktif" 
                            name="emailaktif" 
                            class="form-input" 
                            value="<?php echo isset($_POST['emailaktif']) ? htmlspecialchars($_POST['emailaktif']) : ''; ?>" 
                            required
                            placeholder="Contoh: nama@email.com"
                        >
                        <i class="input-icon fas fa-envelope"></i>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i>
                    Daftarkan Data
                </button>
            </form>
        </div>
    </div>

    <script>
        // Character counter functionality
        const nisInput = document.getElementById('nis');
        const phoneInput = document.getElementById('nomorteleponaktif');
        const nisCounter = document.getElementById('nisCounter');
        const phoneCounter = document.getElementById('phoneCounter');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('studentForm');

        function updateCounter(input, counter, maxLength) {
            const currentLength = input.value.length;
            counter.textContent = `${currentLength}/${maxLength}`;
            
            if (currentLength > maxLength * 0.8) {
                counter.style.color = 'var(--warning-color)';
            } else {
                counter.style.color = 'var(--text-secondary)';
            }
        }

        nisInput.addEventListener('input', () => updateCounter(nisInput, nisCounter, 11));
        phoneInput.addEventListener('input', () => updateCounter(phoneInput, phoneCounter, 13));

        // Form submission with loading state
        form.addEventListener('submit', function(e) {
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 0.5rem;"></i>Memproses...';
        });

        // Real-time validation
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '' && this.required) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });

            input.addEventListener('input', function() {
                if (this.classList.contains('error') && this.value.trim() !== '') {
                    this.classList.remove('error');
                }
            });
        });

        // Initialize counters
        updateCounter(nisInput, nisCounter, 11);
        updateCounter(phoneInput, phoneCounter, 13);

        // Enhanced accessibility
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
                const inputs = Array.from(document.querySelectorAll('.form-input'));
                const currentIndex = inputs.indexOf(e.target);
                if (currentIndex < inputs.length - 1) {
                    e.preventDefault();
                    inputs[currentIndex + 1].focus();
                }
            }
        });
    </script>
</body>
</html>