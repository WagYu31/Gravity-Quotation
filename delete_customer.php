<?php
require 'conn.php'; // Pastikan file koneksi database di-include

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $deletedAt = date('Y-m-d H:i:s');

    // Update the deleted_at field
    $query = "UPDATE customer SET deleted_at = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $deletedAt, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// If invalid request
http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Invalid request']);
exit;
