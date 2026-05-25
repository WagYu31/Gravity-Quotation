<?php
// Include database connection file
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $ket = trim($_POST['ket']);
    $address = trim($_POST['address']);
    $phone_number = trim($_POST['phone_number']);
    $email = trim($_POST['email']);
    
    $phone_number = preg_replace('/\D/', '', $phone_number); // Hapus semua karakter selain angka
        if (strpos($phone_number, '08') !== 0) {
            if (strpos($phone_number, '8') === 0) {
                $phone_number = '0' . $phone_number;
            } elseif (strpos($phone_number, '62') === 0) {
                $phone_number = '0' . substr($phone_number, 2);
            } elseif (strpos($phone_number, '+62') === 0) {
                $phone_number = '0' . substr($phone_number, 3);
            }
        }

    // Validate required fields
    if (empty($name) || empty($address) || empty($phone_number) || empty($email)) {
        echo "All fields are required.";
        exit;
    }

    // Insert data into the database
    $query = "
        INSERT INTO customer (name, address, ket, phone_number, email, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ";

    // Prepare and execute the query
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('sssss', $name, $address, $ket, $phone_number, $email);

        if ($stmt->execute()) {
            // Redirect to customers.php after success
            header('Location: customers.php');
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>
