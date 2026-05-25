<?php
include 'conn.php';

if (isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_GET['id'];  // Mengambil ID dari URL
    $name = $_POST['name'];           // Mengambil data dari form
    $alias = $_POST['alias'];         // Mengambil alias
    $role = $_POST['role'];           // Mengambil role
    $phone_number = $_POST['phone_number'] ?? null;  // Optional
    $email = $_POST['email'] ?? null;  // Optional

    // Query untuk memperbarui data pengguna
    $stmt = $conn->prepare("
        UPDATE users
        SET name = ?, alias = ?, role = ?, telp = ?, email = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param('sssssi', $name, $alias, $role, $phone_number, $email, $id);

    if ($stmt->execute()) {
        // Jika berhasil, lakukan redirect atau kirim pesan sukses
        header("Location: users.php");  // Redirect ke halaman daftar pengguna
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
