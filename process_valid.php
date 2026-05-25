<?php
// Koneksi ke database
include 'conn.php'; // Sesuaikan dengan file koneksi Anda

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_valid'])) {
    // Ambil data dari form
    $quoId = $_POST['quo_id'];
    $validStatus = $_POST['valid_status'];

    // Validasi data
    if (!empty($quoId) && ($validStatus === 'diterima' || $validStatus === 'ditolak')) {
        // Update tabel progress
        $query = "UPDATE progress SET valid = ? WHERE quo_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $validStatus, $quoId);

        if ($stmt->execute()) {
            // Redirect dengan pesan sukses
            header("Location: quoProgress.php?message=success");
        } else {
            // Redirect dengan pesan error
            header("Location: quoProgress.php?message=error");
        }
    } else {
        // Redirect dengan pesan error karena data tidak valid
        header("Location: quoProgress.php?message=invalid_data");
    }
} else {
    // Redirect jika tidak ada data POST
    header("Location: quoProgress.php?message=unauthorized");
}
exit;
