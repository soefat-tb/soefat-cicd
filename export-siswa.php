<?php
require 'vendor/autoload.php'; // Pastikan PhpSpreadsheet sudah diinstal

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Buat koneksi ke database
require_once 'config/database.php';

// Query untuk mengambil semua data siswa
$query = "SELECT nis, nama, kelas, nomor_telepon, email, tingkatan, status FROM siswa";
$result = mysqli_query($koneksi, $query);

// Buat objek Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header kolom
$headers = ['NIS', 'Nama', 'Kelas', 'Nomor Telepon', 'Email', 'Tingkatan', 'Status'];
$sheet->fromArray([$headers], null, 'A1');

// Styling Header (Judul)
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
];

// Terapkan style pada header
$sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

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
$sheet->getStyle("A1:G$lastRow")->applyFromArray($borderStyle);

// Atur auto-size untuk semua kolom
foreach (range('A', 'G') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Set header untuk download file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="siswa_data.xlsx"');
header('Cache-Control: max-age=0');

// Simpan dan kirim ke output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Tutup koneksi database
mysqli_close($koneksi);
exit;
?>
