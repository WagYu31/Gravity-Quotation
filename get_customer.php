<?php
require_once 'conn.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $query = "SELECT * FROM customer WHERE id = ? AND deleted_at IS NULL";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'success', 'customer' => $result->fetch_assoc()]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Customer not found.']);
        }

        $stmt->close();
    }
}

$conn->close();
?>
