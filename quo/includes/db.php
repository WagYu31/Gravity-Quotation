<?php
// Atur zona waktu untuk skrip PHP
date_default_timezone_set('Asia/Jakarta');

// Detail koneksi database
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'quo';

// Buat koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// === PERBAIKAN: ATUR ZONA WAKTU UNTUK KONEKSI DATABASE ===
// Ini akan memastikan fungsi seperti CURRENT_TIMESTAMP() di MySQL menggunakan waktu Jakarta
$conn->query("SET time_zone = '+07:00'");


// Mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>