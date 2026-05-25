<?php
// Set timezone ke Jakarta
date_default_timezone_set('Asia/Jakarta');

// Koneksi database
$jadwal_host = 'localhost';
$jadwal_user = 'u836263092_rootJadwal';
$jadwal_password = 'Eddie@1819';
$jadwal_database = 'u836263092_jadwal';

$jadwal_conn = mysqli_connect($jadwal_host, $jadwal_user, $jadwal_password, $jadwal_database);
if (!$jadwal_conn) {
    die("Koneksi ke database jadwal gagal: " . mysqli_connect_error());
}

// Ambil ID dari parameter GET
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Query untuk soft delete (update deleted_at)
    $query = "UPDATE kegiatan SET deleted_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($jadwal_conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Jika berhasil, redirect kembali dengan pesan sukses
        header('Location: get_activities.php?status=deleted');
    } else {
        // Jika gagal, redirect dengan pesan error
        header('Location: get_activities.php?status=error');
    }
    exit();
} else {
    // Jika ID tidak valid
    header('Location: get_activities.php?status=invalid');
    exit();
}
?>