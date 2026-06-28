<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'vendor/autoload.php';
require_once 'config/database.php';

use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\Binary\ByteBuffer;

session_start();
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $selected_nis = trim($_POST['nis'] ?? '');
    error_log("Received NIS for registration: $selected_nis");

    $conn = new mysqli("sql309.infinityfree.com", "if0_37650982", "soefat135767991", "if0_37650982_p");
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
        exit;
    }

    // Prevent SQL Injection
    $stmt = $conn->prepare("SELECT nis FROM login WHERE nis = ?");
    $stmt->bind_param("s", $selected_nis);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result === false || $result->num_rows === 0) {
        error_log("Invalid NIS: $selected_nis");
        $stmt->close();
        $conn->close();
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "NIS tidak valid!"]);
        exit;
    }
    $stmt->close();
    $conn->close();

    $rpId = 'soefat-tb.wuaze.com';
    $rpName = 'SPSS - Soefat Pramuka Smart System';

    $webauthn = new WebAuthn($rpName, $rpId);

    $userId = $selected_nis;
    $userName = $selected_nis . '@pramuka.smart';
    $userDisplayName = $selected_nis;

    try {
        $creationOptions = $webauthn->getCreateArgs(
            $userId,
            $userName,
            $userDisplayName,
            true,
            ['es256'],
            []
        );

        $challengeBuffer = new ByteBuffer($creationOptions->publicKey->challenge);
        $userIdBuffer = new ByteBuffer($creationOptions->publicKey->user->id);

        $creationOptions->publicKey->challenge = rtrim(strtr(base64_encode($challengeBuffer->getBinaryString()), '+/', '-_'), '=');
        $creationOptions->publicKey->user->id = rtrim(strtr(base64_encode($userIdBuffer->getBinaryString()), '+/', '-_'), '=');

        error_log("Challenge generated for NIS: $selected_nis");
        $_SESSION['challenge'] = $creationOptions->publicKey->challenge;
        $_SESSION['selected_nis'] = $selected_nis;

        header('Content-Type: application/json');
        echo json_encode($creationOptions);
        exit;
    } catch (Exception $e) {
        error_log("WebAuthn exception: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "WebAuthn error: " . $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPSS - Register Passkey</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --accent-gradient: linear-gradient(135deg, #4285f4 0%, #34a853 100%);
            --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            --success-gradient: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-primary: #1a202c;
            --text-secondary: #718096;
            --text-muted: #a0aec0;
            --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 20px 60px rgba(0, 0, 0, 0.15);
            --border-radius: 20px;
            --border-radius-lg: 24px;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
        }

        .bg-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            animation: float 8s ease-in-out infinite;
        }

        .shape:nth-child(1) { width: 120px; height: 120px; top: 10%; left: 10%; animation-delay: 0s; }
        .shape:nth-child(2) { width: 80px; height: 80px; top: 70%; left: 80%; animation-delay: 2s; }
        .shape:nth-child(3) { width: 160px; height: 160px; top: 20%; right: 10%; animation-delay: 4s; }
        .shape:nth-child(4) { width: 100px; height: 100px; bottom: 20%; left: 20%; animation-delay: 6s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg) scale(1); opacity: 0.7; }
            50% { transform: translateY(-30px) rotate(180deg) scale(1.1); opacity: 0.9; }
        }

        .main-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(30px);
            border-radius: var(--border-radius-lg);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-soft);
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-gradient);
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: var(--accent-gradient);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            position: relative;
            transition: var(--transition);
            box-shadow: 0 8px 30px rgba(66, 133, 244, 0.3);
        }

        .logo-icon::before {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            background: var(--accent-gradient);
            border-radius: calc(var(--border-radius) + 4px);
            z-index: -1;
            opacity: 0;
            transition: var(--transition);
        }

        .logo-icon:hover::before {
            opacity: 0.3;
            animation: pulse-ring 2s infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 0.3; }
            70% { transform: scale(1.1); opacity: 0; }
            100% { transform: scale(1); opacity: 0; }
        }

        .logo-icon i {
            color: white;
            font-size: 2rem;
            position: relative;
            z-index: 1;
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.6;
            max-width: 320px;
            margin: 0 auto;
            font-weight: 400;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .section-header i {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--accent-gradient);
            color: white;
            border-radius: 8px;
            font-size: 0.75rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .select-wrapper {
            position: relative;
        }

        .nis-select {
            width: 100%;
            padding: 1rem 1.25rem;
            padding-right: 3rem;
            border: 2px solid transparent;
            border-radius: var(--border-radius);
            background: rgba(248, 250, 252, 0.8);
            backdrop-filter: blur(10px);
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-primary);
            transition: var(--transition);
            appearance: none;
            cursor: pointer;
            outline: none;
        }

        .nis-select:focus {
            border-color: #4285f4;
            background: white;
            box-shadow: 0 0 0 4px rgba(66, 133, 244, 0.1);
            transform: translateY(-1px);
        }

        .nis-select:hover {
            background: white;
            transform: translateY(-1px);
        }

        .select-wrapper::after {
            content: '\f0d7';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            pointer-events: none;
            transition: var(--transition);
        }

        .select-wrapper:hover::after {
            color: var(--text-primary);
        }

        .btn {
            width: 100%;
            padding: 1.125rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            outline: none;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--accent-gradient);
            color: white;
            box-shadow: 0 8px 25px rgba(66, 133, 244, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn:disabled:hover {
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
            padding: 1rem 1.25rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            display: none;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .status-message::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            border-radius: 0 4px 4px 0;
        }

        .status-success {
            background: rgba(52, 168, 83, 0.1);
            color: #0f7537;
            border: 1px solid rgba(52, 168, 83, 0.2);
        }

        .status-success::before {
            background: var(--success-gradient);
        }

        .status-error {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .status-error::before {
            background: var(--danger-gradient);
        }

        .status-message i {
            font-size: 1.1rem;
        }

        .footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .powered-by {
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        .tech-badges {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .tech-badge {
            padding: 0.375rem 0.75rem;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-secondary);
            border: 1px solid var(--glass-border);
            transition: var(--transition);
        }

        .tech-badge:hover {
            background: rgba(66, 133, 244, 0.1);
            color: #4285f4;
            transform: translateY(-1px);
        }

        @media (max-width: 640px) {
            body { padding: 0.75rem; }
            .glass-card { padding: 2rem 1.5rem; border-radius: var(--border-radius); }
            .brand-title { font-size: 1.75rem; }
            .subtitle { font-size: 0.9rem; }
            .logo-icon { width: 70px; height: 70px; }
            .logo-icon i { font-size: 1.75rem; }
            .btn { padding: 1rem 1.25rem; font-size: 0.95rem; }
            .nis-select { padding: 0.875rem 1rem; padding-right: 2.5rem; }
            .shape { opacity: 0.5; }
            .shape:nth-child(1) { width: 80px; height: 80px; }
            .shape:nth-child(2) { width: 60px; height: 60px; }
            .shape:nth-child(3) { width: 100px; height: 100px; }
            .shape:nth-child(4) { width: 70px; height: 70px; }
        }

        @media (max-width: 480px) {
            .glass-card { padding: 1.5rem 1rem; }
            .tech-badges { gap: 0.375rem; }
            .tech-badge { padding: 0.25rem 0.5rem; font-size: 0.65rem; }
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --text-primary: #f7fafc;
                --text-secondary: #e2e8f0;
                --text-muted: #a0aec0;
            }
            .glass-card { background: rgba(26, 32, 44, 0.95); border: 1px solid rgba(255, 255, 255, 0.1); }
            .nis-select { background: rgba(45, 55, 72, 0.8); color: var(--text-primary); }
            .nis-select:focus, .nis-select:hover { background: rgba(45, 55, 72, 1); }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            .shape { animation: none; }
        }

        .btn:focus-visible, .nis-select:focus-visible {
            outline: 2px solid #4285f4;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
    </div>

    <div class="main-container">
        <div class="glass-card">
            <div class="header">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                </div>
                <h1 class="brand-title">SPSS Register</h1>
                <p class="subtitle">Daftar dengan Passkey untuk autentikasi aman tanpa password</p>
            </div>

            <div id="statusMessage" class="status-message">
                <i class="fas fa-check-circle"></i>
                <span></span>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-id-card"></i>
                    <span>Pilih NIS Anda</span>
                </div>

                <div class="input-group">
                    <div class="select-wrapper">
                        <select id="nisSelect" class="nis-select" required>
                            <option value="">-- Pilih NIS --</option>
                            <?php
                            $conn = new mysqli("sql309.infinityfree.com", "if0_37650982", "soefat135767991", "if0_37650982_p");
                            if ($conn->connect_error) {
                                echo "<option value=''>Error: Database gagal</option>";
                            } else {
                                $stmt = $conn->prepare("SELECT nis FROM login ORDER BY nis ASC");
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result === false) {
                                    echo "<option value=''>Error: Gagal ambil NIS</option>";
                                } else {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['nis']}'>{$row['nis']}</option>";
                                    }
                                }
                                $stmt->close();
                                $conn->close();
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <button id="registerBtn" class="btn btn-primary" onclick="register()" type="button">
                    <i class="fas fa-shield-alt"></i>
                    <span>Daftar Passkey</span>
                </button>
            </div>

            <div class="footer">
                <div class="powered-by">Powered by</div>
                <div class="tech-badges">
                    <span class="tech-badge">WebAuthn</span>
                    <span class="tech-badge">Passkeys</span>
                    <span class="tech-badge">Biometrics</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showStatus(message, type = 'success') {
            const statusEl = document.getElementById('statusMessage');
            const iconEl = statusEl.querySelector('i');
            const textEl = statusEl.querySelector('span');
            
            statusEl.className = `status-message status-${type}`;
            iconEl.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
            textEl.textContent = message;
            
            statusEl.style.display = 'flex';
            statusEl.style.animation = 'slideIn 0.3s ease-out';
            
            setTimeout(() => {
                statusEl.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => {
                    statusEl.style.display = 'none';
                    statusEl.style.animation = '';
                }, 300);
            }, 5000);
        }

        function setButtonLoading(button, loading) {
            const icon = button.querySelector('i');
            const span = button.querySelector('span');
            
            if (loading) {
                button.disabled = true;
                icon.style.display = 'none';
                span.textContent = 'Mendaftarkan...';
                const spinner = document.createElement('div');
                spinner.className = 'loading-spinner';
                button.insertBefore(spinner, span);
            } else {
                button.disabled = false;
                const spinner = button.querySelector('.loading-spinner');
                if (spinner) spinner.remove();
                icon.style.display = 'inline-block';
                span.textContent = 'Daftar Passkey';
            }
        }

        async function register() {
            const button = document.getElementById('registerBtn');
            const nisSelect = document.getElementById('nisSelect');
            const nis = nisSelect.value.trim();

            if (!nis) {
                showStatus('Silakan pilih NIS terlebih dahulu!', 'error');
                nisSelect.focus();
                return;
            }

            setButtonLoading(button, true);

            try {
                if ('vibrate' in navigator) {
                    navigator.vibrate([50]);
                }

                const formData = new FormData();
                formData.append('action', 'register');
                formData.append('nis', nis);

                const res = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }

                const options = await res.json();
                console.log('Registration options:', options);

                if (options.status === 'error') {
                    throw new Error(options.message || 'Registrasi gagal');
                }

                if (!options.publicKey) {
                    throw new Error('Invalid registration options received');
                }

                const publicKeyOptions = {
                    challenge: base64urlToBuffer(options.publicKey.challenge),
                    rp: {
                        id: options.publicKey.rp.id,
                        name: options.publicKey.rp.name
                    },
                    user: {
                        id: base64urlToBuffer(options.publicKey.user.id),
                        name: options.publicKey.user.name || `${nis}@pramuka.smart`,
                        displayName: options.publicKey.user.displayName || nis
                    },
                    pubKeyCredParams: options.publicKey.pubKeyCredParams,
                    timeout: 60000,
                    attestation: 'direct'
                };

                const credential = await navigator.credentials.create({ 
                    publicKey: publicKeyOptions 
                });

                if (!credential) {
                    throw new Error('Gagal membuat credential');
                }

                const response = {
                    id: credential.id,
                    rawId: arrayBufferToBase64url(credential.rawId),
                    type: credential.type,
                    response: {
                        clientDataJSON: arrayBufferToBase64url(credential.response.clientDataJSON),
                        attestationObject: arrayBufferToBase64url(credential.response.attestationObject)
                    }
                };

                const verify = await fetch('verify-passkey-register.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(response)
                });

                if (!verify.ok) {
                    throw new Error(`Verification failed: HTTP ${verify.status}`);
                }

                const result = await verify.json();

                if (result.status === 'success') {
                    showStatus('🎉 Passkey berhasil didaftarkan! Mengalihkan...', 'success');
                    if ('vibrate' in navigator) {
                        navigator.vibrate([100, 50, 100]);
                    }
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                } else {
                    throw new Error(result.message || 'Verifikasi gagal');
                }
            } catch (error) {
                console.error('Registration error:', error);
                if ('vibrate' in navigator) {
                    navigator.vibrate([200]);
                }

                let errorMessage = 'Registrasi gagal. Silakan coba lagi.';
                if (error.name === 'NotAllowedError') {
                    errorMessage = 'Registrasi dibatalkan. Coba lagi dan izinkan autentikasi.';
                } else if (error.name === 'NotSupportedError') {
                    errorMessage = 'Passkeys tidak didukung di browser atau device ini.';
                } else if (error.name === 'InvalidStateError') {
                    errorMessage = 'Passkey sudah terdaftar untuk NIS ini.';
                } else if (error.name === 'NetworkError') {
                    errorMessage = 'Koneksi bermasalah. Periksa internet Anda.';
                } else if (error.message) {
                    errorMessage = `Registrasi gagal: ${error.message}`;
                }

                showStatus(errorMessage, 'error');
            } finally {
                setButtonLoading(button, false);
            }
        }

        function base64urlToBuffer(base64url) {
            if (!base64url || typeof base64url !== 'string' || base64url.length === 0) {
                return new Uint8Array(0);
            }
            try {
                base64url = base64url.replace(/-/g, '+').replace(/_/g, '/');
                const padding = '='.repeat((4 - base64url.length % 4) % 4);
                const base64 = base64url + padding;
                const raw = atob(base64);
                return Uint8Array.from(raw, c => c.charCodeAt(0));
            } catch (error) {
                console.error('Base64 decode error:', error);
                return new Uint8Array(0);
            }
        }

        function arrayBufferToBase64url(buffer) {
            if (!buffer || buffer.byteLength === 0) {
                return '';
            }
            try {
                const bytes = new Uint8Array(buffer);
                let binary = '';
                for (let i = 0; i < bytes.byteLength; i++) {
                    binary += String.fromCharCode(bytes[i]);
                }
                return btoa(binary)
                    .replace(/\+/g, '-')
                    .replace(/\//g, '_')
                    .replace(/=/g, '');
            } catch (error) {
                console.error('Array buffer conversion error:', error);
                return '';
            }
        }

        document.getElementById('nisSelect').addEventListener('change', function() {
            const button = document.getElementById('registerBtn');
            if (this.value) {
                button.style.opacity = '1';
                button.style.transform = 'scale(1)';
            } else {
                button.style.opacity = '0.7';
                button.style.transform = 'scale(0.98)';
            }
        });

        document.getElementById('nisSelect').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && this.value) {
                register();
            }
        });

        document.getElementById('registerBtn').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                register();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.glass-card').style.opacity = '0';
            document.querySelector('.glass-card').style.transform = 'translateY(20px)';
            setTimeout(() => {
                document.querySelector('.glass-card').style.transition = 'all 0.6s ease-out';
                document.querySelector('.glass-card').style.opacity = '1';
                document.querySelector('.glass-card').style.transform = 'translateY(0)';
            }, 100);
            document.getElementById('nisSelect').focus();
        });

        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes slideOut {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(-10px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>