<?php
// Koneksi database
include 'conn.php';

// Periksa apakah ada parameter ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Update kolom deleted_at dengan nilai NOW()
    $sql = "UPDATE barang SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        // Redirect ke halaman sebelumnya dengan pesan sukses
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    // Jika ID tidak valid
    die('ID tidak valid.');
}
?>
