<?php
include "conn.php";
session_start(); // Memulai sesi jika belum dimulai

if (isset($_GET['quonum']) && isset($_GET['id'])) {
    $quonum = $_GET['quonum'];
    $quoId = $_GET['id'];

    // Validasi input untuk mencegah SQL Injection
    if (!is_numeric($quoId) || empty($quonum)) {
        $_SESSION['error'] = "Data tidak valid.";
        header("Location: listQuotation.php");
        exit();
    }

    // Query soft delete
    $deleteStmt = $conn->prepare("UPDATE quo SET deleted_at = NOW() WHERE quo_num = ? AND id = ?");
    $deleteStmt->bind_param("si", $quonum, $quoId);

    if ($deleteStmt->execute()) {
        $_SESSION['success'] = "Quotation berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Terjadi kesalahan saat menghapus quotation.";
    }

    $deleteStmt->close(); // Tutup statement
    $conn->close(); // Tutup koneksi

    // Redirect kembali ke halaman listQuotation
    header("Location: listQuotation.php");
    exit();
} else {
    // Jika parameter tidak lengkap
    $_SESSION['error'] = "Parameter tidak valid.";
    header("Location: listQuotation.php");
    exit();
}
?>
