<?php
include "conn.php"; // Pastikan file koneksi database sudah benar

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mulai session (jika belum dimulai)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil kode kegiatan dan quoID dari session
if (isset($_SESSION['kode_kegiatan']) && isset($_SESSION['quoID'])) {
    $kode_kegiatan = $_SESSION['kode_kegiatan'];
    $quoID = $_SESSION['quoID'];

    // Update tabel quo dengan kode kegiatan
    $update_query = "UPDATE quo SET kegiatan_kode = '$kode_kegiatan' WHERE id = '$quoID'";
    if (mysqli_query($conn, $update_query)) {
        // Hapus data session setelah digunakan
        unset($_SESSION['kode_kegiatan']);
        unset($_SESSION['quoID']);

        // Alihkan ke halaman quoProgress.php
        header('Location: quoProgress.php');
        exit(); // Pastikan tidak ada output sebelum redirect
    } else {
        echo "<p><strong>Gagal update:</strong> " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p>Data tidak ditemukan dalam session.</p>";
}

// Tutup koneksi database
mysqli_close($conn);
?>