<?php
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $ket = trim($_POST['ket']);
    $address = trim($_POST['address']);
    $phone_number = trim($_POST['phone_number']);
    $email = trim($_POST['email']);

    $query = "
        UPDATE customer
        SET name = ?, address = ?, ket = ?, phone_number = ?, email = ?, updated_at = NOW()
        WHERE id = ? AND deleted_at IS NULL
    ";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('sssssi', $name, $address, $ket, $phone_number, $email, $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Customer updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update customer.']);
        }

        $stmt->close();
    }
}

$conn->close();
?>
