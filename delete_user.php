<?php
include 'conn.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $query = "UPDATE users SET deleted_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }

    $stmt->close();
    $conn->close();
}
?>
