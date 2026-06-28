<?php
// Mulai session
session_start();

// Handle request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        // Koneksi ke database
        $conn = new mysqli("sql309.infinityfree.com", "if0_37650982", "soefat135767991", "if0_37650982_p");
        if ($conn->connect_error) {
            throw new Exception("Koneksi database gagal: " . $conn->connect_error);
        }

        // Inisiasi login passkey
        if (isset($_POST['action']) && $_POST['action'] === 'login_passkey') {
            $nis = trim($_POST['nis'] ?? '');
            $result = $conn->query("SELECT rawId FROM credentials WHERE nis = '$nis' LIMIT 1");
            $credentials = [];
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $credentials[] = [
                    'id' => $row['rawId'],
                    'type' => 'public-key'
                ];
            }

            // Generate challenge untuk WebAuthn
            $challenge = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            $publicKey = [
                'challenge' => $challenge,
                'allowCredentials' => $credentials,
                'rpId' => $_SERVER['HTTP_HOST'],
                'userVerification' => 'preferred',
                'timeout' => 60000
            ];

            echo json_encode(['publicKey' => $publicKey]);
        } else {
            echo json_encode(["status" => "error", "message" => "Permintaan tidak valid"]);
        }

        $conn->close();
        exit;
    } catch (Exception $e) {
        error_log("Exception in login-ori.php: " . $e->getMessage());
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Set header HTML kalau bukan POST
header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login Passkey | SPSS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #2c8030 0%, #4a7c59 100%), linear-gradient(to top, rgba(0,0,0,0.1), rgba(0,0,0,0.1));
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            margin: 0;
            position: relative;
        }

        /* Floating particles background */
        .bg-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .login-container {
            width: 90%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.2);
        }

        .school-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2c8030, #4a7c59);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 1.5rem;
            animation: pulse 2s infinite ease-in-out;
            box-shadow: 0 8px 25px rgba(44, 128, 48, 0.3);
        }

        .school-logo i {
            color: white;
            font-size: 2rem;
        }

        .login-header h2 {
            color: #2c8030;
            font-weight: 600;
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 0.25rem;
        }

        .login-header p {
            color: #4b5563;
            font-size: 0.875rem;
            text-align: center;
            opacity: 0.8;
            margin-bottom: 2rem;
        }

        .passkey-section {
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-group i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #2c8030;
            font-size: 1rem;
            z-index: 2;
        }

        .nis-select {
            padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            height: 3rem;
            font-size: 0.95rem;
            width: 100%;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%232c8030' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
        }

        .nis-select:focus {
            border-color: #4a7c59;
            box-shadow: 0 0 0 3px rgba(74, 124, 89, 0.1);
            outline: none;
        }

        .passkey-btn {
            background: linear-gradient(135deg, #2c8030, #4a7c59);
            color: white;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.875rem 1.5rem;
            width: 100%;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
            font-size: 1rem;
            box-shadow: 0 8px 25px rgba(44, 128, 48, 0.3);
        }

        .passkey-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .passkey-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(44, 128, 48, 0.4);
        }

        .passkey-btn:hover::before {
            left: 100%;
        }

        .passkey-btn:active {
            transform: translateY(0);
        }

        .passkey-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .status-message {
            padding: 12px 16px;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: none;
            align-items: center;
            gap: 8px;
        }

        .status-success {
            background: rgba(52, 168, 83, 0.1);
            color: #0f7537;
            border: 1px solid rgba(52, 168, 83, 0.2);
        }

        .status-error {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .passkey-info {
            background: rgba(59, 130, 246, 0.1);
            color: #1e40af;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            text-align: center;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .passkey-info i {
            color: #3b82f6;
            margin-right: 0.5rem;
        }

        .login-footer {
            text-align: center;
            color: #4b5563;
            font-size: 0.75rem;
            margin-top: 2rem;
            opacity: 0.8;
        }

        .alternative-login {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(209, 213, 219, 0.3);
        }

        .alternative-login a {
            color: #4a7c59;
            font-size: 0.875rem;
            text-decoration: none;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alternative-login a:hover {
            color: #2c8030;
            text-decoration: underline;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 8px 25px rgba(44, 128, 48, 0.3); }
            50% { transform: scale(1.05); box-shadow: 0 12px 35px rgba(44, 128, 48, 0.4); }
        }

        @media (max-width: 480px) {
            .login-container { 
                padding: 1.5rem; 
                margin: 10px; 
                border-radius: 0.875rem; 
            }
            
            .school-logo {
                width: 60px;
                height: 60px;
            }
            
            .school-logo i {
                font-size: 1.5rem;
            }
            
            .login-header h2 {
                font-size: 1.25rem;
            }
            
            .login-header p {
                font-size: 0.8rem;
            }
            
            .passkey-btn {
                padding: 0.75rem 1.25rem;
                font-size: 0.9rem;
            }
            
            .nis-select {
                font-size: 0.9rem;
                height: 2.75rem;
            }
            
            .login-footer {
                font-size: 0.7rem;
            }
        }

        @media (orientation: landscape) and (max-height: 500px) {
            .login-container {
                padding: 1.25rem;
                max-height: 90vh;
                overflow-y: auto;
            }
            
            .school-logo {
                width: 60px;
                height: 60px;
                margin-bottom: 1rem;
            }
            
            .login-header p {
                margin-bottom: 1.5rem;
            }
            
            .passkey-btn {
                padding: 0.6rem 1rem;
            }
            
            .login-footer {
                margin-top: 1rem;
            }
        }

        @media (prefers-color-scheme: dark) {
            .login-container { 
                background: rgba(17, 24, 39, 0.95); 
                border: 1px solid rgba(255, 255, 255, 0.1); 
            }
            .section-title { color: #f9fafb; }
            .login-header p { color: #9ca3af; }
            .nis-select { 
                background: #374151; 
                border-color: #4b5563; 
                color: #f9fafb; 
            }
        }
    </style>
</head>
<body>
    <div class="bg-particles" id="particles"></div>
    <div class="login-container">
        <div class="login-header">
            <div class="school-logo">
                <i class="fas fa-fingerprint"></i>
            </div>
            <h2>Portal Siswa</h2>
            <p>Login dengan Passkey - SPSS</p>
        </div>

        <div class="passkey-info">
            <i class="fas fa-info-circle"></i>
            Gunakan sidik jari, wajah, atau PIN untuk masuk dengan aman
        </div>

        <div id="statusMessage" class="status-message">
            <i class="fas fa-check-circle"></i>
            <span></span>
        </div>

        <div class="passkey-section">
            <h3 class="section-title">
                <i class="fas fa-user"></i>
                Pilih Akun
            </h3>
            
            <div class="form-group">
                <i class="fas fa-id-card"></i>
                <select id="nisSelect" class="nis-select" required>
                    <option value="">-- Pilih NIS Anda --</option>
                    <?php
                    $conn = new mysqli("sql309.infinityfree.com", "if0_37650982", "soefat135767991", "if0_37650982_p");
                    if ($conn->connect_error) {
                        echo "<option value=''>Error: Database gagal terhubung</option>";
                    } else {
                        $result = $conn->query("SELECT DISTINCT c.nis, s.nama FROM credentials c LEFT JOIN siswa s ON c.nis = s.nis ORDER BY c.nis");
                        if ($result === false) {
                            echo "<option value=''>Error: Gagal mengambil data</option>";
                        } else {
                            while ($row = $result->fetch_assoc()) {
                                $displayName = !empty($row['nama']) ? $row['nis'] . ' - ' . $row['nama'] : $row['nis'];
                                echo "<option value='{$row['nis']}'>{$displayName}</option>";
                            }
                        }
                        $conn->close();
                    }
                    ?>
                </select>
            </div>

            <button id="passkeyBtn" class="passkey-btn" onclick="loginPasskey()">
                <i class="fas fa-fingerprint"></i>
                <span>Masuk dengan Passkey</span>
            </button>
        </div>

        <!--- <div class="alternative-login">
            <a href="login-ori.php">
                <i class="fas fa-key"></i>
                Login dengan Password
            </a>
        </div> -->

        <div class="login-footer">
            <small>© <?= date('Y') ?> SMKS Taruna Bangsa. Secured by WebAuthn Technology</small>
        </div>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            const container = document.getElementById('particles');
            const particleCount = window.innerWidth < 768 ? 20 : 40;
            
            container.innerHTML = ''; // Clear existing particles
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                const size = Math.random() * 8 + 4;
                const left = Math.random() * 100;
                const animationDelay = Math.random() * 6;
                const animationDuration = 6 + Math.random() * 4;
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${left}%`;
                particle.style.top = `${Math.random() * 100}%`;
                particle.style.animationDelay = `${animationDelay}s`;
                particle.style.animationDuration = `${animationDuration}s`;
                
                container.appendChild(particle);
            }
        }

        function showStatus(message, type = 'success') {
            const statusEl = document.getElementById('statusMessage');
            const iconEl = statusEl.querySelector('i');
            const textEl = statusEl.querySelector('span');
            
            statusEl.className = `status-message status-${type}`;
            iconEl.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            textEl.textContent = message;
            statusEl.style.display = 'flex';
            
            setTimeout(() => { 
                statusEl.style.display = 'none'; 
            }, 5000);
        }

        function setButtonLoading(button, loading) {
            const icon = button.querySelector('i');
            const span = button.querySelector('span');
            
            if (loading) {
                button.disabled = true;
                icon.outerHTML = '<div class="loading-spinner"></div>';
                span.textContent = 'Memverifikasi...';
            } else {
                button.disabled = false;
                button.querySelector('.loading-spinner').outerHTML = '<i class="fas fa-fingerprint"></i>';
                span.textContent = 'Masuk dengan Passkey';
            }
        }

        async function loginPasskey() {
            const button = document.getElementById('passkeyBtn');
            const nisSelect = document.getElementById('nisSelect');
            const nis = nisSelect.value;

            if (!nis) {
                showStatus('Pilih NIS terlebih dahulu!', 'error');
                nisSelect.focus();
                return;
            }

            setButtonLoading(button, true);

            try {
                const formData = new FormData();
                formData.append('action', 'login_passkey');
                formData.append('nis', nis);

                const res = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                const options = await res.json();
                console.log('Parsed options (login):', options);

                if (options.status === 'error' || !options.publicKey) {
                    throw new Error(options.message || 'Login Passkey gagal');
                }

                const publicKeyOptions = {
                    challenge: base64urlToBuffer(options.publicKey.challenge),
                    allowCredentials: options.publicKey.allowCredentials?.map(cred => ({
                        id: base64urlToBuffer(cred.id),
                        type: cred.type
                    })) || [],
                    rpId: options.publicKey.rpId,
                    userVerification: options.publicKey.userVerification,
                    timeout: options.publicKey.timeout
                };

                const credential = await navigator.credentials.get({ publicKey: publicKeyOptions });

                const response = {
                    id: credential.id,
                    rawId: arrayBufferToBase64url(credential.rawId),
                    type: credential.type,
                    response: {
                        clientDataJSON: arrayBufferToBase64url(credential.response.clientDataJSON),
                        authenticatorData: arrayBufferToBase64url(credential.response.authenticatorData),
                        signature: arrayBufferToBase64url(credential.response.signature),
                        userHandle: arrayBufferToBase64url(credential.response.userHandle)
                    }
                };

                const verify = await fetch('login-verify.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(response)
                });
                const result = await verify.json();

                if (result.status === 'success') {
                    showStatus('🚀 Selamat datang! Mengarahkan ke dashboard...', 'success');
                    setTimeout(() => { 
                        window.location.href = 'dashboard.php'; 
                    }, 2000);
                } else {
                    throw new Error(result.message || 'Verifikasi gagal');
                }
            } catch (e) {
                console.error('Login error:', e);
                if (e.name === 'NotAllowedError') {
                    showStatus('Autentikasi dibatalkan atau tidak ada Passkey yang terdaftar.', 'error');
                } else if (e.name === 'NotSupportedError') {
                    showStatus('Passkeys tidak didukung di device atau browser ini.', 'error');
                } else {
                    showStatus(`Login gagal: ${e.message}`, 'error');
                }
            } finally {
                setButtonLoading(button, false);
            }
        }

        function base64urlToBuffer(base64url) {
            if (!base64url || typeof base64url !== 'string' || base64url.length === 0) return new Uint8Array(0);
            base64url = base64url.replace(/-/g, '+').replace(/_/g, '/');
            const padding = '='.repeat((4 - base64url.length % 4) % 4);
            const base64 = base64url + padding;
            try {
                const raw = atob(base64);
                return Uint8Array.from(raw, c => c.charCodeAt(0));
            } catch (e) {
                console.error('Base64 decode error:', e);
                return new Uint8Array(0);
            }
        }

        function arrayBufferToBase64url(buffer) {
            if (!buffer || buffer.byteLength === 0) return '';
            const raw = String.fromCharCode.apply(null, new Uint8Array(buffer));
            let base64 = btoa(raw);
            return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
        }

        // Initialize particles and handle window resize
        window.addEventListener('load', createParticles);
        window.addEventListener('resize', createParticles);

        // Handle form submission with Enter key
        document.getElementById('nisSelect').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                loginPasskey();
            }
        });

        // Auto-focus on NIS select when page loads
        window.addEventListener('load', function() {
            document.getElementById('nisSelect').focus();
        });
    </script>
</body>
</html>