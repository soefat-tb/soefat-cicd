<?php
// Aktifkan mode keamanan
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
session_start();
include 'config/database.php';

// Validasi CSRF token
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}

// Fungsi sanitasi input
function sanitizeInput($input) {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

// Query untuk mengambil data pendaftaran (tanpa id dan created_at, tanggal_daftar hanya tanggal)
$query = "SELECT nama, kelas, nomor_telepon, pesan, DATE(tanggal_daftar) as tanggal_daftar FROM pendaftaran ORDER BY id DESC";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die('Error fetching data: ' . mysqli_error($koneksi));
}

// Buat objek Spreadsheet
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header kolom (tanpa Created At)
$headers = ['Nama', 'Kelas', 'Nomor Telepon', 'Pesan', 'Tanggal Daftar'];
$sheet->fromArray([$headers], null, 'A1');

// Styling Header
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
];
$sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

// Isi data dari database
$rowIndex = 2;
while ($row = mysqli_fetch_assoc($result)) {
    $sheet->fromArray(array_values($row), null, "A$rowIndex");
    $rowIndex++;
}

// Tambahkan border ke seluruh tabel
$lastRow = $rowIndex - 1;
$borderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$sheet->getStyle("A1:E$lastRow")->applyFromArray($borderStyle);

// Atur auto-size untuk semua kolom
foreach (range('A', 'E') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Set header untuk download file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="pendaftaran_data.xlsx"');
header('Cache-Control: max-age=0');

// Simpan dan kirim ke output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Tutup koneksi database
mysqli_close($koneksi);
exit;
?>