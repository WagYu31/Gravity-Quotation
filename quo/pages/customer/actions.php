<?php
include '../../includes/db.php';

// Cek aksi dari form POST (Create & Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Aksi: Buat customer baru
    if ($_POST['action'] === 'create') {
        $stmt = $conn->prepare("INSERT INTO customer (name, store_name, ket, address, email, phone_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $_POST['name'], $_POST['store_name'], $_POST['ket'], $_POST['address'], $_POST['email'], $_POST['phone_number']);
        $stmt->execute();
        header('Location: index.php?status=created');
        exit();
    }

    // Aksi: Update data customer
    if ($_POST['action'] === 'update') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE customer SET name=?, store_name=?, ket=?, address=?, email=?, phone_number=? WHERE id=?");
        $stmt->bind_param("ssssssi", $_POST['name'], $_POST['store_name'], $_POST['ket'], $_POST['address'], $_POST['email'], $_POST['phone_number'], $id);
        $stmt->execute();
        header('Location: index.php?status=updated');
        exit();
    }
}

// Cek aksi dari URL GET (Delete)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    
    // Aksi: Hapus customer (Soft Delete)
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        // Pastikan tidak ada Quotation yang terkait sebelum menghapus
        // (Ini adalah best practice, namun untuk soft delete bisa diabaikan jika tidak diperlukan)
        $stmt = $conn->prepare("UPDATE customer SET deleted_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header('Location: index.php?status=deleted');
        exit();
    }
}

// Jika tidak ada aksi yang cocok, kembali ke halaman utama
header('Location: index.php');
exit();
?>