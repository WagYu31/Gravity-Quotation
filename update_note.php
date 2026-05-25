<?php
header('Content-Type: application/json');
require 'conn.php'; // Sesuaikan koneksi database Anda

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['quonum']) && isset($data['add_note'])) {
    $quonum = $data['quonum'];
    $add_note = $data['add_note'];
    $updated_at = date('Y-m-d H:i:s');

    $query = "UPDATE quo SET add_note = ?, updated_at = ? WHERE quo_num = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sss', $add_note, $updated_at, $quonum);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Data tidak valid']);
}
