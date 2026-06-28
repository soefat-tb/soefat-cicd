<?php
// Fungsi untuk mendapatkan IP publik dari pihak ketiga
function getPublicIP() {
    $services = [
        'https://api.ipify.org/',
        'https://ipinfo.io/ip',
        'https://api.ip.sb/ip',
        'https://icanhazip.com/'
    ];
    
    foreach ($services as $service) {
        try {
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'timeout' => 3,
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n"
                ]
            ];
            $context = stream_context_create($opts);
            $ip = @file_get_contents($service, false, $context);
            
            if ($ip && filter_var(trim($ip), FILTER_VALIDATE_IP)) {
                return trim($ip);
            }
        } catch (Exception $e) {
            continue;
        }
    }
    
    // Fallback ke metode standar
    $headers = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    foreach ($headers as $header) {
        if (isset($_SERVER[$header]) && filter_var($_SERVER[$header], FILTER_VALIDATE_IP)) {
            return $_SERVER[$header];
        }
    }
    
    return 'Unknown';
}

// Mendapatkan info perangkat dan browser
function getDeviceInfo() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $device = 'Unknown';
    $browser = 'Unknown';
    $os = 'Unknown';
    
    // Deteksi OS
    if (preg_match('/windows|win32|win64/i', $userAgent)) {
        $os = 'Windows';
    } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
        $os = 'macOS';
    } elseif (preg_match('/android/i', $userAgent)) {
        $os = 'Android';
    } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
        $os = 'iOS';
    } elseif (preg_match('/linux/i', $userAgent)) {
        $os = 'Linux';
    }
    
    // Deteksi Browser
    if (preg_match('/MSIE|Trident/i', $userAgent)) {
        $browser = 'Internet Explorer';
    } elseif (preg_match('/Firefox/i', $userAgent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/Chrome/i', $userAgent) && !preg_match('/Edg/i', $userAgent) && !preg_match('/OPR/i', $userAgent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/Safari/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
        $browser = 'Safari';
    } elseif (preg_match('/Edg/i', $userAgent)) {
        $browser = 'Microsoft Edge';
    } elseif (preg_match('/OPR/i', $userAgent)) {
        $browser = 'Opera';
    }
    
    // Deteksi jenis perangkat
    if (preg_match('/mobile/i', $userAgent)) {
        if (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $device = preg_match('/ipad/i', $userAgent) ? 'iPad' : 'iPhone';
        } else {
            $device = 'Mobile';
        }
    } elseif (preg_match('/tablet/i', $userAgent)) {
        $device = 'Tablet';
    } else {
        $device = 'Desktop/Laptop';
    }
    
    return [
        'device' => $device,
        'os' => $os,
        'browser' => $browser,
        'user_agent' => $userAgent
    ];
}

$publicIP = getPublicIP();
$deviceInfo = getDeviceInfo();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Address Detector</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --success-color: #10b981;
            --error-color: #ef4444;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-color: #334155;
            --text-light: #64748b;
            --border-color: #e2e8f0;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: url('data:image/svg+xml,%3Csvg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5z" fill="%232563eb" fill-opacity="0.05" fill-rule="evenodd"/%3E%3C/svg%3E');
        }

        .container {
            max-width: 900px;
            width: 100%;
            margin: 20px auto;
            padding: 20px;
            flex: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeIn 0.5s ease-in-out;
        }

        .logo {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        h1 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 1rem;
            color: var(--text-light);
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 8px 24px var(--shadow-color);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: slideUp 0.5s ease-in-out;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-icon {
            font-size: 1.5rem;
            margin-right: 0.75rem;
            color: var(--primary-color);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(37, 99, 235, 0.1);
            border-radius: 8px;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .info-item {
            padding: 12px 0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            flex: 0 0 150px;
            font-weight: 600;
            color: var(--text-light);
            display: flex;
            align-items: center;
        }

        .info-label i {
            margin-right: 8px;
            width: 20px;
            text-align: center;
        }

        .info-value {
            flex: 1;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            padding: 4px 10px;
            background-color: #f1f5f9;
            border-radius: 4px;
            font-weight: 500;
        }

        .copy-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            margin-left: 10px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .copy-btn:hover {
            background-color: var(--secondary-color);
        }

        .copy-success {
            background-color: var(--success-color);
        }

        .refresh-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            margin: 1rem auto;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background-color 0.3s;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        }

        .refresh-btn:hover {
            background-color: var(--secondary-color);
        }

        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: var(--primary-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        .browser-info {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed var(--border-color);
        }

        .browser-tag {
            background-color: #e0f2fe;
            color: #0369a1;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        #local-ip-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .local-ip-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background-color: #f1f5f9;
            border-radius: 6px;
            margin-bottom: 5px;
        }

        .no-ip-found {
            color: var(--text-light);
            font-style: italic;
        }

        .footer {
            text-align: center;
            margin-top: 2rem;
            padding: 1rem 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .card {
                padding: 1rem;
            }
            
            .info-label {
                flex: 0 0 120px;
            }
        }

        @media (max-width: 480px) {
            .info-label {
                flex: 0 0 100%;
                margin-bottom: 5px;
            }
            
            .info-value {
                flex: 0 0 100%;
            }
            
            .copy-btn {
                margin-left: 0;
                margin-top: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo"><i class="fas fa-network-wired"></i></div>
            <h1>IP Address Detector</h1>
            <p class="subtitle">Deteksi lengkap alamat IP lokal dan publik perangkat Anda</p>
        </div>
        
        <div class="card" id="public-ip-card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-globe"></i></div>
                <h2 class="card-title">Alamat IP Publik</h2>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-external-link-alt"></i> Publik IP</div>
                <div class="info-value" id="public-ip"><?php echo htmlspecialchars($publicIP); ?></div>
                <button class="copy-btn" data-value="<?php echo htmlspecialchars($publicIP); ?>" onclick="copyToClipboard(this)">
                    <i class="fas fa-copy"></i> Salin
                </button>
            </div>
            <div class="browser-info">
                <div class="browser-tag"><i class="fas fa-laptop"></i> <?php echo htmlspecialchars($deviceInfo['device']); ?></div>
                <div class="browser-tag"><i class="fas fa-desktop"></i> <?php echo htmlspecialchars($deviceInfo['os']); ?></div>
                <div class="browser-tag"><i class="fas fa-browser"></i> <?php echo htmlspecialchars($deviceInfo['browser']); ?></div>
            </div>
        </div>
        
        <div class="card" id="local-ip-card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-network-wired"></i></div>
                <h2 class="card-title">Alamat IP Lokal</h2>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-server"></i> IP Lokal</div>
                <div id="local-ip-list">
                    <div class="no-ip-found">Mencari alamat IP lokal...</div>
                </div>
            </div>
        </div>
        
        <div class="card" id="full-details-card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-info-circle"></i></div>
                <h2 class="card-title">Detail Lengkap</h2>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-map-marker-alt"></i> Server IP</div>
                <div class="info-value"><?php echo htmlspecialchars($_SERVER['SERVER_ADDR'] ?? 'Not available'); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-server"></i> Server Name</div>
                <div class="info-value"><?php echo htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'Not available'); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-link"></i> Port</div>
                <div class="info-value"><?php echo htmlspecialchars($_SERVER['SERVER_PORT'] ?? 'Not available'); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-chrome"></i> User Agent</div>
                <div class="info-value"><?php echo htmlspecialchars($deviceInfo['user_agent']); ?></div>
                <button class="copy-btn" data-value="<?php echo htmlspecialchars($deviceInfo['user_agent']); ?>" onclick="copyToClipboard(this)">
                    <i class="fas fa-copy"></i> Salin
                </button>
            </div>
        </div>
        
        <button class="refresh-btn" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i> Muat Ulang Data
        </button>
        
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Sedang mengambil data IP...</p>
        </div>
        
        <div class="footer">
            &copy; <?php echo date('Y'); ?> IP Address Detector | <i class="fas fa-shield-alt"></i> Data privasi Anda tidak disimpan di server
        </div>
    </div>

    <script>
        // Fungsi untuk mendapatkan semua alamat IP lokal
        function getLocalIPs(callback) {
            const ips = [];
            
            // Kompatibilitas browser
            const RTCPeerConnection = window.RTCPeerConnection ||
                                     window.mozRTCPeerConnection ||
                                     window.webkitRTCPeerConnection;
                                     
            if (!RTCPeerConnection) {
                callback([]);
                return;
            }
            
            const pc = new RTCPeerConnection({
                iceServers: []
            });
            
            // Membuat data channel kosong
            pc.createDataChannel('');
            
            // Membuat dan menetapkan offer
            pc.createOffer()
                .then(offer => pc.setLocalDescription(offer))
                .catch(err => {
                    console.error('Error creating offer:', err);
                    callback([]);
                });
                
            // Fungsi callback untuk event kandidat ICE
            pc.onicecandidate = (event) => {
                if (!event.candidate) return;
                
                // Ekspresi reguler untuk mencari alamat IP dari berbagai jenis jaringan
                let ipRegex = /([0-9]{1,3}(\.[0-9]{1,3}){3})/;
                let match = ipRegex.exec(event.candidate.candidate);
                
                if (match) {
                    let ip = match[1];
                    
                    // Memfilter alamat IPv4 privat
                    if (ip.match(/^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.)/) && ips.indexOf(ip) === -1) {
                        ips.push(ip);
                        updateIPList(ip);
                    }
                }
            };
            
            // Timeout untuk menutup koneksi setelah 5 detik
            setTimeout(() => {
                pc.close();
                if (ips.length === 0) {
                    let noIPElement = document.querySelector('.no-ip-found');
                    if (noIPElement) {
                        noIPElement.textContent = 'Tidak dapat mendeteksi IP lokal. Coba gunakan browser yang berbeda.';
                    }
                }
            }, 5000);
        }
        
        // Menambahkan IP ke daftar
        function updateIPList(ip) {
            const ipListElement = document.getElementById('local-ip-list');
            const noIPElement = document.querySelector('.no-ip-found');
            
            if (noIPElement) {
                noIPElement.remove();
            }
            
            const ipItemDiv = document.createElement('div');
            ipItemDiv.className = 'local-ip-item';
            
            const ipValueSpan = document.createElement('span');
            ipValueSpan.textContent = ip;
            ipItemDiv.appendChild(ipValueSpan);
            
            const copyButton = document.createElement('button');
            copyButton.className = 'copy-btn';
            copyButton.setAttribute('data-value', ip);
            copyButton.innerHTML = '<i class="fas fa-copy"></i> Salin';
            copyButton.onclick = function() {
                copyToClipboard(this);
            };
            ipItemDiv.appendChild(copyButton);
            
            ipListElement.appendChild(ipItemDiv);
        }
        
        // Fungsi untuk menyalin ke clipboard
        function copyToClipboard(element) {
            const value = element.getAttribute('data-value');
            const tempInput = document.createElement('input');
            tempInput.value = value;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            // Efek visual untuk konfirmasi salinan
            element.classList.add('copy-success');
            element.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
            
            setTimeout(() => {
                element.classList.remove('copy-success');
                element.innerHTML = '<i class="fas fa-copy"></i> Salin';
            }, 2000);
        }
        
        // Tambahkan animasi loading
        document.addEventListener('DOMContentLoaded', function() {
            // Mulai deteksi IP lokal
            getLocalIPs();
            
            // Efek animasi kartu muncul secara berurutan
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.2}s`;
            });
        });
        
        // Deteksi mode offline
        window.addEventListener('offline', function() {
            alert('Koneksi internet terputus. Beberapa fitur mungkin tidak berfungsi dengan benar.');
        });
    </script>
</body>
</html>