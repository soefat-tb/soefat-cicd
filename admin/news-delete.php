<?php
session_start();
include '../config/database.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit();
}

// Cek ID berita
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    // Hapus gambar jika ada
    $query_gambar = mysqli_query($koneksi, "SELECT gambar FROM berita WHERE id = '$id'");
    $data = mysqli_fetch_assoc($query_gambar);
    if (!empty($data['gambar']) && file_exists($data['gambar'])) {
        unlink($data['gambar']);
    }
    // Hapus berita
    $query = "DELETE FROM berita WHERE id = '$id'";
    if (mysqli_query($koneksi, $query)) {
        header("Location: dashboard.php");
    } else {
        header("Location: dashboard.php?error=Gagal_menghapus_berita");
    }
} else {
    header("Location: dashboard.php");
}
?>
