<?php
// Aktifkan mode keamanan
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

session_start();
include 'config/database.php';

// Fungsi validasi input
function sanitizeInput($input) {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

// Fungsi untuk generate sitemap.xml (statis + dinamis)
function generateSitemap($koneksi) {
    $base_url = "https://soefat-tb.wuaze.com/";
    $sitemap_file = __DIR__ . '/sitemap.xml';

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;
    $xml .= '<!-- created with Free Online Sitemap Generator www.xml-sitemaps.com -->' . PHP_EOL;

    // Bagian statis (halaman utama)
    $main_urls = [
        ['loc' => $base_url, 'lastmod' => '2025-06-07T16:22:36+00:00', 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['loc' => $base_url . 'index.php', 'lastmod' => '2025-06-07T16:22:36+00:00', 'changefreq' => 'weekly', 'priority' => '0.9'],
        ['loc' => $base_url . 'news.php', 'lastmod' => '2025-07-25T03:08:35+00:00', 'changefreq' => 'weekly', 'priority' => '0.9'],
        ['loc' => $base_url . 'spss/dashboard.php', 'lastmod' => '2025-06-07T16:22:36+00:00', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['loc' => $base_url . 'registration.php', 'lastmod' => '2025-06-07T16:22:36+00:00', 'changefreq' => 'monthly', 'priority' => '0.9'],
    ];

    foreach ($main_urls as $url) {
        $xml .= '  <url>' . PHP_EOL;
        $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . PHP_EOL;
        $xml .= '    <lastmod>' . htmlspecialchars($url['lastmod']) . '</lastmod>' . PHP_EOL;
        $xml .= '    <changefreq>' . htmlspecialchars($url['changefreq']) . '</changefreq>' . PHP_EOL;
        $xml .= '    <priority>' . htmlspecialchars($url['priority']) . '</priority>' . PHP_EOL;
        $xml .= '  </url>' . PHP_EOL;
    }

    // Bagian dinamis (berita dan gambar)
    $query = "SELECT id, judul, tanggal, gambar, additional_files, additional_files_1, additional_files_2, additional_files_3, additional_files_4 FROM berita ORDER BY tanggal DESC";
    $result = mysqli_query($koneksi, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $xml .= '  <url>' . PHP_EOL;
        $xml .= '    <loc>' . htmlspecialchars($base_url . 'detail_news.php?id=' . $row['id']) . '</loc>' . PHP_EOL;
        $xml .= '    <lastmod>' . date('c', strtotime($row['tanggal'])) . '</lastmod>' . PHP_EOL;
        $xml .= '    <changefreq>monthly</changefreq>' . PHP_EOL;
        $xml .= '    <priority>0.7</priority>' . PHP_EOL;

        $images = array_filter([
            $row['gambar'],
            $row['additional_files'],
            $row['additional_files_1'],
            $row['additional_files_2'],
            $row['additional_files_3'],
            $row['additional_files_4']
        ]);

        foreach ($images as $image) {
            if (!empty($image)) {
                $xml .= '    <image:image>' . PHP_EOL;
                $xml .= '      <image:loc>' . htmlspecialchars($base_url . $image) . '</image:loc>' . PHP_EOL;
                $xml .= '      <image:title>' . htmlspecialchars($row['judul']) . '</image:title>' . PHP_EOL;
                $xml .= '      <image:caption>' . htmlspecialchars(substr($row['judul'], 0, 100)) . '</image:caption>' . PHP_EOL;
                $xml .= '    </image:image>' . PHP_EOL;
            }
        }

        $xml .= '  </url>' . PHP_EOL;
    }

    $xml .= '</urlset>';

    if (is_writable(dirname($sitemap_file))) {
        file_put_contents($sitemap_file, $xml);
    } else {
        error_log("Gagal menulis sitemap.xml: Direktori tidak writable");
        file_put_contents(sys_get_temp_dir() . '/sitemap.xml', $xml);
        error_log("Sitemap disimpan di: " . sys_get_temp_dir() . '/sitemap.xml');
    }
}

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit();
}

// Fungsi redirect dengan pesan
function redirectWithMessage($message, $type = 'success') {
    $_SESSION[$type] = $message;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fungsi upload file dengan penanganan error
function uploadFile($file) {
    $target_dir = "Uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
        if (!is_writable($target_dir)) {
            error_log("Folder uploads tidak memiliki izin tulis");
            return false;
        }
    }

    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $max_file_size = 5 * 1024 * 1024;

    if (!in_array($file_ext, $allowed_ext)) {
        error_log("Jenis file tidak diizinkan: " . $file['name']);
        return false;
    }

    if ($file['size'] > $max_file_size) {
        error_log("Ukuran file terlalu besar: " . $file['size']);
        return false;
    }

    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        error_log("File bukan gambar: " . $file['name']);
        return false;
    }

    $filename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $target_file;
    }

    error_log("Gagal upload file: " . $file['error']);
    return false;
}

// Proses truncate tabel berita
if (isset($_GET['truncate_berita']) && $_GET['truncate_berita'] === 'yes') {
    $stmt = $koneksi->prepare("TRUNCATE TABLE berita");
    if ($stmt->execute()) {
        $stmt_files = $koneksi->prepare("SELECT gambar, additional_files, additional_files_1, additional_files_2, additional_files_3, additional_files_4 FROM berita");
        $stmt_files->execute();
        $result = $stmt_files->get_result();
        while ($row = $result->fetch_assoc()) {
            $files = array_filter([$row['gambar'], $row['additional_files'], $row['additional_files_1'], $row['additional_files_2'], $row['additional_files_3'], $row['additional_files_4']]);
            foreach ($files as $file) {
                if (!empty($file) && file_exists($file)) {
                    unlink($file);
                }
            }
        }
        generateSitemap($koneksi); // Update sitemap setelah truncate
        redirectWithMessage("Tabel berita berhasil dikosongkan!");
    } else {
        redirectWithMessage("Gagal mengosongkan tabel berita: " . $stmt->error, 'error');
    }
    $stmt->close();
}

// Proses tambah berita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_berita'])) {
    try {
        $judul = sanitizeInput($_POST['judul']);
        $konten = sanitizeInput($_POST['konten']);
        $kategori = sanitizeInput($_POST['kategori']);

        if (empty($judul) || strlen($judul) > 255) {
            throw new Exception("Judul tidak valid");
        }
        if (empty($konten)) {
            throw new Exception("Konten tidak boleh kosong");
        }
        if (empty($kategori)) {
            throw new Exception("Kategori tidak boleh kosong");
        }

        $gambar = $additional_files = $additional_files_1 = $additional_files_2 = $additional_files_3 = $additional_files_4 = '';
        $files = [
            'gambar' => $_FILES['gambar'],
            'additional_files' => $_FILES['additional_files'],
            'additional_files_1' => $_FILES['additional_files_1'],
            'additional_files_2' => $_FILES['additional_files_2'],
            'additional_files_3' => $_FILES['additional_files_3'],
            'additional_files_4' => $_FILES['additional_files_4']
        ];

        foreach ($files as $key => $file) {
            if (!empty($file['name'])) {
                $uploaded = uploadFile($file);
                if ($uploaded !== false) {
                    $$key = $uploaded;
                }
            }
        }

        $stmt = $koneksi->prepare("INSERT INTO berita (judul, konten, kategori, gambar, additional_files, additional_files_1, additional_files_2, additional_files_3, additional_files_4, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssssss", $judul, $konten, $kategori, $gambar, $additional_files, $additional_files_1, $additional_files_2, $additional_files_3, $additional_files_4);
        
        if ($stmt->execute()) {
            generateSitemap($koneksi); // Update sitemap setelah tambah berita
            redirectWithMessage("Berita berhasil ditambahkan!");
        } else {
            throw new Exception("Gagal menambahkan berita: " . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        redirectWithMessage($e->getMessage(), 'error');
    }
}

// Proses edit berita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_berita'])) {
    try {
        $id = intval($_POST['id']);
        $judul = sanitizeInput($_POST['judul']);
        $konten = sanitizeInput($_POST['konten']);
        $kategori = sanitizeInput($_POST['kategori']);

        if (empty($judul) || strlen($judul) > 255) {
            throw new Exception("Judul tidak valid");
        }
        if (empty($konten)) {
            throw new Exception("Konten tidak boleh kosong");
        }
        if (empty($kategori)) {
            throw new Exception("Kategori tidak boleh kosong");
        }

        $gambar = $additional_files = $additional_files_1 = $additional_files_2 = $additional_files_3 = $additional_files_4 = '';
        $files = [
            'gambar' => $_FILES['gambar'],
            'additional_files' => $_FILES['additional_files'],
            'additional_files_1' => $_FILES['additional_files_1'],
            'additional_files_2' => $_FILES['additional_files_2'],
            'additional_files_3' => $_FILES['additional_files_3'],
            'additional_files_4' => $_FILES['additional_files_4']
        ];
        $old_gambar = $old_additional_files = $old_additional_files_1 = $old_additional_files_2 = $old_additional_files_3 = $old_additional_files_4 = '';

        $stmt_old = $koneksi->prepare("SELECT gambar, additional_files, additional_files_1, additional_files_2, additional_files_3, additional_files_4 FROM berita WHERE id = ?");
        $stmt_old->bind_param("i", $id);
        $stmt_old->execute();
        $result = $stmt_old->get_result();
        $row = $result->fetch_assoc();
        $old_gambar = $row['gambar'] ?? '';
        $old_additional_files = $row['additional_files'] ?? '';
        $old_additional_files_1 = $row['additional_files_1'] ?? '';
        $old_additional_files_2 = $row['additional_files_2'] ?? '';
        $old_additional_files_3 = $row['additional_files_3'] ?? '';
        $old_additional_files_4 = $row['additional_files_4'] ?? '';
        $stmt_old->close();

        foreach ($files as $key => $file) {
            if (!empty($file['name'])) {
                $uploaded = uploadFile($file);
                if ($uploaded !== false) {
                    $$key = $uploaded;
                }
            }
        }

        if (empty($gambar) && !empty($old_gambar)) $gambar = $old_gambar;
        if (empty($additional_files) && !empty($old_additional_files)) $additional_files = $old_additional_files;
        if (empty($additional_files_1) && !empty($old_additional_files_1)) $additional_files_1 = $old_additional_files_1;
        if (empty($additional_files_2) && !empty($old_additional_files_2)) $additional_files_2 = $old_additional_files_2;
        if (empty($additional_files_3) && !empty($old_additional_files_3)) $additional_files_3 = $old_additional_files_3;
        if (empty($additional_files_4) && !empty($old_additional_files_4)) $additional_files_4 = $old_additional_files_4;

        $query = "UPDATE berita SET judul = ?, konten = ?, kategori = ?, gambar = ?, additional_files = ?, additional_files_1 = ?, additional_files_2 = ?, additional_files_3 = ?, additional_files_4 = ? WHERE id = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("sssssssssi", $judul, $konten, $kategori, $gambar, $additional_files, $additional_files_1, $additional_files_2, $additional_files_3, $additional_files_4, $id);

        if ($stmt->execute()) {
            $old_files = array_filter([$old_gambar, $old_additional_files, $old_additional_files_1, $old_additional_files_2, $old_additional_files_3, $old_additional_files_4]);
            $new_files = array_filter([$gambar, $additional_files, $additional_files_1, $additional_files_2, $additional_files_3, $additional_files_4]);
            foreach ($old_files as $old_file) {
                if (!empty($old_file) && !in_array($old_file, $new_files) && file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            generateSitemap($koneksi); // Update sitemap setelah edit berita
            redirectWithMessage("Berita berhasil diupdate!");
        } else {
            throw new Exception("Gagal mengupdate berita: " . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        redirectWithMessage($e->getMessage(), 'error');
    }
}

// Menghapus berita
if (isset($_GET['hapus_berita'])) {
    $id = intval($_GET['hapus_berita']);
    $stmt_files = $koneksi->prepare("SELECT gambar, additional_files, additional_files_1, additional_files_2, additional_files_3, additional_files_4 FROM berita WHERE id = ?");
    $stmt_files->bind_param("i", $id);
    $stmt_files->execute();
    $result = $stmt_files->get_result();
    $row = $result->fetch_assoc();
    $files = array_filter([$row['gambar'], $row['additional_files'], $row['additional_files_1'], $row['additional_files_2'], $row['additional_files_3'], $row['additional_files_4']]);

    $stmt = $koneksi->prepare("DELETE FROM berita WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        foreach ($files as $file) {
            if (!empty($file) && file_exists($file)) {
                unlink($file);
            }
        }
        generateSitemap($koneksi); // Update sitemap setelah hapus berita
        redirectWithMessage("Berita berhasil dihapus!");
    } else {
        redirectWithMessage("Gagal menghapus berita: " . $stmt->error, 'error');
    }
    $stmt->close();
}

// Menghapus pendaftaran
if (isset($_GET['hapus_pendaftaran'])) {
    $id = intval($_GET['hapus_pendaftaran']);
    $stmt = $koneksi->prepare("DELETE FROM pendaftaran WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        redirectWithMessage("Pendaftaran berhasil dihapus!");
    } else {
        redirectWithMessage("Gagal menghapus pendaftaran: " . mysqli_error($koneksi), 'error');
    }
    $stmt->close();
}

// Ambil daftar berita
$query_berita = mysqli_query($koneksi, "SELECT * FROM berita ORDER BY tanggal DESC");

// Ambil daftar pendaftaran
$pendaftaran = [];
$result = mysqli_query($koneksi, "SELECT * FROM pendaftaran ORDER BY id DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $pendaftaran[] = $row;
}

// Generate sitemap saat halaman dimuat pertama kali
generateSitemap($koneksi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Berita - Soefat</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
       * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2c8030;
            --primary-dark: #1e5a22;
            --primary-light: #3a9a3e;
            --secondary-color: #4a7c59;
            --accent-color: #78c679;
            --background-light: #f8f9fa;
            --background-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --text-muted: #95a5a6;
            --card-shadow: 0 8px 25px rgba(44, 128, 48, 0.12);
            --card-shadow-hover: 0 15px 35px rgba(44, 128, 48, 0.2);
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            background: var(--background-gradient);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-size: 14px;
            overflow-x: hidden;
            line-height: 1.6;
            font-weight: 400;
        }

        /* Enhanced Header */
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(44, 128, 48, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            min-height: 60px;
            border-bottom: 1px solid var(--glass-border);
        }

        .header .logo {
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            letter-spacing: -0.5px;
        }

        .header .logo i {
            margin-right: 8px;
            font-size: 1.4rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header .actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header .actions .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            border: 2px solid var(--glass-border);
            position: relative;
            overflow: hidden;
        }

        .header .actions .user-avatar::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .header .actions .user-avatar:hover::before {
            left: 100%;
        }

        .header .actions .user-avatar:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .header .actions .user-avatar i {
            font-size: 1.1rem;
            color: var(--primary-color);
        }

        /* Enhanced Main Content */
        .main-content {
            flex: 1;
            padding: 75px 1rem 80px 1rem;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .welcome-section {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--glass-border);
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--primary-color));
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { background-position: 200% 0; }
            50% { background-position: -200% 0; }
        }

        .welcome-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-subtitle {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 400;
        }

        /* Enhanced Card Styling */
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-shadow-hover);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        .card-header {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            color: var(--text-dark);
        }

        .card-header i {
            margin-right: 8px;
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        /* Enhanced Form Styling */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
            font-size: 0.85rem;
            color: var(--text-dark);
            letter-spacing: 0.3px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 0.85rem;
            transition: var(--transition);
            background: white;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(44, 128, 48, 0.1);
            transform: translateY(-2px);
        }

        .form-group input:hover,
        .form-group textarea:hover,
        .form-group select:hover {
            border-color: var(--primary-light);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .form-group input[type="file"] {
            padding: 0.5rem;
            border: 2px dashed #e9ecef;
            background: #fafafa;
            cursor: pointer;
            transition: var(--transition);
        }

        .form-group input[type="file"]:hover {
            border-color: var(--primary-color);
            background: rgba(44, 128, 48, 0.02);
        }

        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        /* Enhanced Button Styling */
        .btn {
            padding: 0.75rem 1.25rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            font-size: 0.85rem;
            position: relative;
            overflow: hidden;
            min-width: 120px;
            gap: 6px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            box-shadow: 0 4px 15px rgba(44, 128, 48, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(44, 128, 48, 0.4);
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #ffcd39);
            color: var(--text-dark);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
            background: linear-gradient(135deg, #e0a800, #ffc107);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #e85667);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
            background: linear-gradient(135deg, #c82333, #dc3545);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #868e96);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            min-width: 80px;
        }

        .btn i {
            font-size: 0.9rem;
        }

        .text-end {
            text-align: right;
        }

        /* Enhanced Card List */
        .card-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .card-item {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 1.25rem;
            box-shadow: var(--card-shadow);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            border: 1px solid var(--glass-border);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .card-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .card-item:hover::before {
            transform: scaleX(1);
        }

        .card-item:hover {
            transform: translateY(-6px);
            box-shadow: var(--card-shadow-hover);
            border-color: rgba(44, 128, 48, 0.2);
        }

        .card-item .title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
            line-height: 1.4;
            margin-bottom: 0.25rem;
        }

        .card-item .meta {
            font-size: 0.8rem;
            color: var(--text-light);
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .card-item .meta .badge {
            padding: 0.4rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(44, 128, 48, 0.3);
        }

        .card-item .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 0.5rem;
        }

        /* Enhanced Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.show {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-lg);
            max-width: 95%;
            width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            border: 1px solid var(--glass-border);
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: scale(1);
        }

        .modal-header {
            padding: 1.25rem;
            background: linear-gradient(135deg, #ffc107, #ffcd39);
            border-top-left-radius: var(--border-radius-lg);
            border-top-right-radius: var(--border-radius-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        .modal-body {
            padding: 1.5rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 1.25rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            border-top: 1px solid #e9ecef;
            background: rgba(248, 249, 250, 0.5);
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-dark);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .btn-close:hover {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            transform: rotate(90deg);
        }

        /* Enhanced Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 0.75rem 0;
            box-shadow: 0 -4px 20px rgba(44, 128, 48, 0.15);
            z-index: 1000;
            border-top: 1px solid var(--glass-border);
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-light);
            font-size: 0.7rem;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 0.75rem;
            border-radius: var(--border-radius);
            min-width: 60px;
            position: relative;
            overflow: hidden;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: var(--border-radius);
        }

        .nav-item.active::before {
            opacity: 0.1;
        }

        .nav-item:hover::before {
            opacity: 0.05;
        }

        .nav-item.active {
            color: var(--primary-color);
            background: rgba(44, 128, 48, 0.1);
            transform: translateY(-2px);
        }

        .nav-item:hover {
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .nav-item i {
            font-size: 1.2rem;
            margin-bottom: 0.3rem;
            transition: var(--transition);
            position: relative;
            z-index: 1;
        }

        .nav-item.active i {
            transform: scale(1.15);
            text-shadow: 0 2px 8px rgba(44, 128, 48, 0.3);
        }

        .nav-item span {
            position: relative;
            z-index: 1;
        }

        /* Enhanced Alerts */
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            font-size: 0.85rem;
            font-weight: 500;
            position: relative;
            border-left: 4px solid;
            animation: slideInDown 0.5s ease;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(44, 128, 48, 0.1), rgba(120, 198, 121, 0.1));
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(232, 86, 103, 0.1));
            color: #dc3545;
            border-left-color: #dc3545;
        }

        /* Enhanced Loading */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(44, 128, 48, 0.1);
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Enhanced Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(241, 241, 241, 0.5);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        }

        /* Enhanced Ripple Effect */
        .ripple-container {
            position: relative;
            overflow: hidden;
        }

        .ripple {
            position: absolute;
            background: rgba(44, 128, 48, 0.4);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple-animation 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            pointer-events: none;
        }

        @keyframes ripple-animation {
            0% {
                transform: scale(0);
                opacity: 1;
            }
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        /* Floating Animation */
        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Pulse Animation */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Enhanced Responsive */
        @media (min-width: 768px) {
            .main-content {
                padding: 85px 2rem 90px 2rem;
            }

            .card-list {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 1.5rem;
            }

            .welcome-title {
                font-size: 1.6rem;
            }

            .welcome-subtitle {
                font-size: 1rem;
            }

            .form-row {
                grid-template-columns: 2fr 1fr;
                gap: 2rem;
            }

            .nav-item {
                font-size: 0.75rem;
                min-width: 70px;
            }

            .nav-item i {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 767px) {
            .main-content {
                padding: 65px 0.75rem 75px 0.75rem;
            }

            .card {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .card-list {
                flex-direction: column;
                gap: 0.75rem;
            }

            .form-row {
                display: block;
            }

            .form-group {
                margin-bottom: 0.75rem;
            }

            .btn {
                padding: 0.6rem 1rem;
                font-size: 0.8rem;
                min-width: 100px;
            }

            .modal-content {
                width: 90%;
                margin: 1rem;
            }

            .modal-body {
                padding: 1rem;
            }

            .welcome-section {
                padding: 1rem;
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 0.75rem;
            }

            .header .logo {
                font-size: 1.1rem;
            }

            .main-content {
                padding: 60px 0.5rem 70px 0.5rem;
            }

            .card {
                padding: 0.875rem;
            }

            .card-item {
                padding: 1rem;
            }

            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 0.625rem;
            }

            .nav-item {
                min-width: 50px;
                font-size: 0.65rem;
            }

            .nav-item i {
                font-size: 1.1rem;
            }
        }

        /* Enhanced Animations */
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-up {
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Focus States */
        .btn:focus,
        .nav-item:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Enhanced File Input */
        .form-group input[type="file"]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(44, 128, 48, 0.1);
        }

        /* Text utilities */
        .text-center {
            text-align: center;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        /* Enhanced card hover effects */
        .card-item:hover .title {
            color: var(--primary-color);
        }

        .card-item:hover .badge {
            transform: scale(1.05);
        }

        /* Smooth transitions for all interactive elements */
        button, a, input, textarea, select {
            transition: var(--transition);
        }

        /* Enhanced focus ring for accessibility */
        *:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <!-- Loading -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="logo">
            <i class="fas fa-shield-alt"></i>
            Soefat Admin
        </div>
        <div class="actions">
            <div class="user-avatar ripple-container">
                <i class="fas fa-user"></i>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Alerts -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-title">Kelola Berita</div>
            <div class="welcome-subtitle">Tambah, edit, atau hapus berita dengan mudah</div>
        </div>

        <!-- Tambah Berita -->
        <div id="taber" class="card">
            <div class="card-header">
                <i class="fas fa-plus-circle"></i> Tambah Berita Baru
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div>
                            <div class="form-group">
                                <label>Judul Berita</label>
                                <input type="text" name="judul" required>
                            </div>
                            <div class="form-group">
                                <label>Konten Berita</label>
                                <textarea name="konten" required></textarea>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="kategori" required>
                                    <option value="Berita">Berita</option>
                                    <option value="Pengumuman">Pengumuman</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Gambar Utama</label>
                                <input type="file" name="gambar" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Gambar Tambahan 1</label>
                                <input type="file" name="additional_files" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Gambar Tambahan 2</label>
                                <input type="file" name="additional_files_1" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Gambar Tambahan 3</label>
                                <input type="file" name="additional_files_2" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Gambar Tambahan 4</label>
                                <input type="file" name="additional_files_3" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Gambar Tambahan 5</label>
                                <input type="file" name="additional_files_4" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" name="tambah_berita" class="btn btn-primary ripple-container">
                            <i class="fas fa-paper-plane"></i> Posting Berita
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Daftar Berita -->
        <div id="dafber" class="card">
            <div class="card-header">
                <i class="fas fa-newspaper"></i> Daftar Berita
            </div>
            <div class="card-body">
                <div class="card-list">
                    <?php if (mysqli_num_rows($query_berita) === 0): ?>
                        <div class="text-center text-muted">Tidak ada berita yang ditemukan.</div>
                    <?php else: ?>
                        <?php while ($berita = mysqli_fetch_assoc($query_berita)): ?>
                            <div class="card-item">
                                <div class="title"><?= htmlspecialchars($berita['judul']) ?></div>
                                <div class="meta">
                                    <span class="badge"><?= htmlspecialchars($berita['kategori']) ?></span>
                                    <span><?= date('d M Y', strtotime($berita['tanggal'])) ?></span>
                                </div>
                                <div class="actions">
                                    <button class="btn btn-warning btn-sm edit-berita ripple-container" 
                                            data-id="<?= $berita['id'] ?>"
                                            data-judul="<?= htmlspecialchars($berita['judul']) ?>"
                                            data-konten="<?= htmlspecialchars($berita['konten']) ?>"
                                            data-kategori="<?= htmlspecialchars($berita['kategori']) ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?hapus_berita=<?= $berita['id'] ?>" class="btn btn-danger btn-sm ripple-container" 
                                       onclick="return confirm('Yakin ingin menghapus berita?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pendaftaran Pramuka -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-plus"></i> Pendaftaran Pramuka
            </div>
            <div class="card-body">
                <div class="card-list">
                    <?php if (empty($pendaftaran)): ?>
                        <div class="text-center text-muted">Tidak ada pendaftaran yang ditemukan.</div>
                    <?php else: ?>
                        <?php foreach ($pendaftaran as $pendaftar): ?>
                            <div class="card-item">
                                <div class="title"><?= htmlspecialchars($pendaftar['nama']) ?></div>
                                <div class="meta">
                                    <span>Kelas: <?= htmlspecialchars($pendaftar['kelas']) ?></span>
                                    <span>Telp: <?= htmlspecialchars($pendaftar['nomor_telepon']) ?></span>
                                </div>
                                <div class="meta">
                                    <span>Pesan: <?= htmlspecialchars($pendaftar['pesan'] ?? '-') ?></span>
                                    <span><?= date('d M Y H:i', strtotime($pendaftar['tanggal_daftar'])) ?></span>
                                </div>
                                <div class="actions">
                                    <a href="?hapus_pendaftaran=<?= $pendaftar['id'] ?>" class="btn btn-danger btn-sm ripple-container" 
                                       onclick="return confirm('Yakin ingin menghapus pendaftaran?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Berita Modal -->
    <div class="modal" id="editBeritaModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Berita</h5>
                <button class="btn-close" onclick="document.getElementById('editBeritaModal').classList.remove('show')">×</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="form-row">
                        <div>
                            <div class="form-group">
                                <label>Judul Berita</label>
                                <input type="text" name="judul" id="edit-judul" required>
                            </div>
                            <div class="form-group">
                                <label>Konten Berita</label>
                                <textarea name="konten" id="edit-konten" required></textarea>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="kategori" id="edit-kategori" required>
                                    <option value="Berita">Berita</option>
                                    <option value="Pengumuman">Pengumuman</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Gambar Utama</label>
                                <input type="file" name="gambar" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Gambar Tambahan 1</label>
                                <input type="file" name="additional_files" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Gambar Tambahan 2</label>
                                <input type="file" name="additional_files_1" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Gambar Tambahan 3</label>
                                <input type="file" name="additional_files_2" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Gambar Tambahan 4</label>
                                <input type="file" name="additional_files_3" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Gambar Tambahan 5</label>
                                <input type="file" name="additional_files_4" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary ripple-container" onclick="document.getElementById('editBeritaModal').classList.remove('show')">Batal</button>
                    <button type="submit" name="edit_berita" class="btn btn-warning ripple-container">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
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
            <span>User</span>
        </a>
        <a href="news-manager.php" class="nav-item active ripple-container">
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

    <script>
   document.addEventListener('DOMContentLoaded', () => {
    // Enhanced loading with progress
    let loading = document.getElementById('loading');
    const showLoading = () => {
        if (loading) {
            loading.style.display = 'flex';
            loading.style.opacity = '0';
            setTimeout(() => {
                if (loading) loading.style.opacity = '1';
            }, 10);
        }
    };

    const hideLoading = () => {
        if (loading) {
            loading.style.opacity = '0';
            setTimeout(() => {
                if (loading) loading.style.display = 'none';
            }, 300);
        }
    };

    // Enhanced modal animations
    let editModal = document.getElementById('editBeritaModal');
    const openModal = (modal) => {
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };

    const closeModal = (modal) => {
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    };

    // Enhanced edit modal functionality
    let editButtons = document.querySelectorAll('.edit-berita');
    if (editButtons) {
        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                let id = button.dataset.id;
                let judul = button.dataset.judul;
                let konten = button.dataset.konten;
                let kategori = button.dataset.kategori;
                
                if (document.getElementById('edit-id')) document.getElementById('edit-id').value = id;
                if (document.getElementById('edit-judul')) document.getElementById('edit-judul').value = judul;
                if (document.getElementById('edit-konten')) document.getElementById('edit-konten').value = konten;
                if (document.getElementById('edit-kategori')) document.getElementById('edit-kategori').value = kategori;
                openModal(editModal);
            });
        });
    }

    // Enhanced modal close functionality
    let closeButtons = document.querySelectorAll('.btn-close');
    if (closeButtons) {
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                closeModal(editModal);
            });
        });
    }

    // Close modal on backdrop click
    if (editModal) {
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) {
                closeModal(editModal);
            }
        });
    }

    // Enhanced form validation with real-time feedback
    let forms = document.querySelectorAll('form');
    if (forms) {
        forms.forEach(form => {
            let inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
            
            if (inputs) {
                inputs.forEach(input => {
                    input.addEventListener('blur', () => {
                        validateField(input);
                    });
                    
                    input.addEventListener('input', () => {
                        clearFieldError(input);
                    });
                });

                form.addEventListener('submit', (e) => {
                    let isValid = true;
                    
                    if (inputs) {
                        inputs.forEach(input => {
                            if (!validateField(input)) {
                                isValid = false;
                            }
                        });
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                        showAlert('Mohon lengkapi semua field yang diperlukan!', 'error');
                    } else {
                        showLoading();
                    }
                });
            }
        });
    }

    // Field validation function
    function validateField(field) {
        if (!field) return true;
        let value = field.value.trim();
        let isValid = true;
        
        if (field.hasAttribute('required') && value === '') {
            isValid = false;
            showFieldError(field, 'Field ini wajib diisi');
        } else if (field.name === 'judul' && value.length > 255) {
            isValid = false;
            showFieldError(field, 'Judul maksimal 255 karakter');
        }
        
        return isValid;
    }

    function showFieldError(field, message) {
        if (!field) return;
        clearFieldError(field);
        field.style.borderColor = '#dc3545';
        field.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
        
        let errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '0.75rem';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }

    function clearFieldError(field) {
        if (!field) return;
        field.style.borderColor = '#e9ecef';
        field.style.boxShadow = 'none';
        
        let errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    // Enhanced alert system
    function showAlert(message, type = 'success') {
        let alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} fade-in`;
        alertDiv.textContent = message;
        
        let mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.insertBefore(alertDiv, mainContent.firstChild);
        }
        
        setTimeout(() => {
            if (alertDiv) {
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 300);
            }
        }, 4000);
    }

    // Enhanced ripple effect with better positioning
    let rippleContainers = document.querySelectorAll('.ripple-container');
    if (rippleContainers) {
        rippleContainers.forEach(container => {
            container.addEventListener('click', (e) => {
                let ripple = document.createElement('span');
                let rect = container.getBoundingClientRect();
                let size = Math.max(rect.width, rect.height) * 1.5;
                let x = e.clientX - rect.left - size / 2;
                let y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = `${size}px`;
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                ripple.classList.add('ripple');
                
                container.appendChild(ripple);
                
                setTimeout(() => {
                    if (ripple.parentNode) {
                        ripple.remove();
                    }
                }, 800);
            });
        });
    }

    // Enhanced card animations on scroll
    let observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    let observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('slide-up');
            }
        });
    }, observerOptions);

    // Observe all cards and card items
    let cards = document.querySelectorAll('.card, .card-item');
    if (cards) {
        cards.forEach(el => {
            observer.observe(el);
        });
    }

    // Enhanced navigation active state
    let navItems = document.querySelectorAll('.nav-item');
    if (navItems) {
        let currentPage = window.location.pathname.split('/').pop();
        navItems.forEach(item => {
            let href = item.getAttribute('href');
            if (href && href.includes(currentPage)) {
                navItems.forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
            }
        });
    }

    // Add floating animation to welcome section
    let welcomeSection = document.querySelector('.welcome-section');
    if (welcomeSection) {
        welcomeSection.classList.add('floating');
    }

    // Enhanced file input with preview
    let fileInputs = document.querySelectorAll('input[type="file"]');
    if (fileInputs) {
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                let file = e.target.files[0];
                if (file) {
                    input.style.borderColor = 'var(--primary-color)';
                    input.style.background = 'rgba(44, 128, 48, 0.05)';
                    
                    let fileName = file.name;
                    if (fileName.length > 30) {
                        input.title = fileName;
                    }
                }
            });
        });
    }

    // Smooth scroll for internal links
    let anchors = document.querySelectorAll('a[href^="#"]');
    if (anchors) {
        anchors.forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                let target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // Auto-hide alerts
    let alerts = document.querySelectorAll('.alert');
    if (alerts) {
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 300);
            }, 5000);
        });
    }

    // Enhanced keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && editModal && editModal.classList.contains('show')) {
            closeModal(editModal);
        }
        
        if (e.key === 'Enter' && document.activeElement.type === 'submit') {
            document.activeElement.click();
        }
    });

    // Add pulse effect to active nav item
    let activeNavItem = document.querySelector('.nav-item.active');
    if (activeNavItem) {
        activeNavItem.classList.add('pulse');
    }

    // Progressive enhancement for older browsers
    if (!CSS.supports('backdrop-filter', 'blur(10px)')) {
        let elements = document.querySelectorAll('.header, .bottom-nav, .modal, .card');
        if (elements) {
            elements.forEach(el => {
                el.style.background = el.style.background.replace(/rgba\([^)]+\)/g, '#ffffff');
            });
        }
    }

    // Performance optimization - debounce scroll events
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        
        scrollTimeout = setTimeout(() => {
            let scrolled = window.pageYOffset;
            let header = document.querySelector('.header');
            
            if (header) {
                if (scrolled > 50) {
                    header.style.boxShadow = '0 8px 30px rgba(44, 128, 48, 0.4)';
                } else {
                    header.style.boxShadow = '0 4px 20px rgba(44, 128, 48, 0.3)';
                }
            }
        }, 10);
    });
});
    </script>
</body>
</html>