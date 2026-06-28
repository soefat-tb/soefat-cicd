<?php
// Mulai sesi
session_start();

// Hancurkan semua sesi
session_unset();
session_destroy();

// Redirect ke halaman login atau halaman lain
header("Location: admin/");
exit();
?>
