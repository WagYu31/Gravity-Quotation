<?php
header('Content-Type: application/json');
require 'conn.php'; // Sesuaikan koneksi database Anda

if (isset($_GET['quonum'])) {
    $quonum = $_GET['quonum'];

    $query = "SELECT add_note FROM quo WHERE quo_num = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $quonum);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['add_note' => null]);
    }
} else {
    echo json_encode(['error' => 'Quonum tidak ditemukan']);
}
