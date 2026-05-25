<?php
// Include koneksi database
require 'conn.php'; // Sesuaikan dengan file koneksi Anda

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // Validasi ID sebagai integer

    // SQL Query untuk soft delete
    $sql = "UPDATE quo_detail SET deleted_at = NOW() WHERE id = ?";

    // Prepare statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id); // Binding parameter ID

        // Eksekusi query
        if ($stmt->execute()) {
            // Ambil URL halaman sebelumnya
            $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'defaultPage.php';

            // Redirect ke halaman sebelumnya dengan pesan sukses
            header("Location: $previousPage");
            exit;
        } else {
            // Redirect dengan pesan error
            header("Location: $previousPage");
            exit;
        }

        $stmt->close();
    } else {
        // Redirect jika terjadi kesalahan pada prepared statement
        header("Location: $previousPage");
        exit;
    }
} else {
    // Redirect jika ID tidak valid
    $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'defaultPage.php';
    header("Location: $previousPage");
    exit;
}
?>