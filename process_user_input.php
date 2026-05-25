<?php
// Include database connection
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $name = $_POST['name'];
    $alias = $_POST['alias'];
    $role = $_POST['role'];
    $phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : null; // Allow null
    $email = isset($_POST['email']) ? $_POST['email'] : null; // Allow null

    // Insert data into the database
    $stmt = $conn->prepare("
        INSERT INTO users (name, alias, role, telp, email, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->bind_param('sssss', $name, $alias, $role, $phone_number, $email);

    if ($stmt->execute()) {
        echo "User successfully added!";
        header("Location: users.php"); // Redirect to user list page
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
