<?php
session_start();

// Koneksi ke Database texcer1
$host = "localhost";
$user = "root";
$pass = ""; // Kosong jika pakai XAMPP default
$db   = "texcer1";

$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Fungsi Format Rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>