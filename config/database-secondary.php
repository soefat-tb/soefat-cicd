<?php

$host = 'sql309.infinityfree.com';

$username = 'if0_37650982';

$password = 'soefat135767991';

$database = 'if0_37650982_pramuka';



// Membuat koneksi

$koneksi = mysqli_connect($host, $username, $password, $database);



// Cek koneksi

if (!$koneksi) {

    die("Koneksi gagal: " . mysqli_connect_error());

}

?>