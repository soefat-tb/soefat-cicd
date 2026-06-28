<?php
include 'config/database.php';

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
        echo "Sitemap berhasil dihasilkan di " . $sitemap_file;
    } else {
        error_log("Gagal menulis sitemap.xml: Direktori tidak writable");
        file_put_contents(sys_get_temp_dir() . '/sitemap.xml', $xml);
        echo "Gagal menulis sitemap.xml, disimpan di " . sys_get_temp_dir() . '/sitemap.xml';
    }
}

// Panggil fungsi untuk generate sitemap
generateSitemap($koneksi);
?>