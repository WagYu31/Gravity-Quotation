<?php
include 'conn.php';

$query = $_GET['q'];

// Mencari data customer yang sesuai
$sql = $conn->prepare("SELECT id, name, phone_number FROM customer WHERE name LIKE ? LIMIT 10");
$searchTerm = '%' . $query . '%';
$sql->bind_param("s", $searchTerm);
$sql->execute();

$result = $sql->get_result();
$customers = [];

while ($row = $result->fetch_assoc()) {
    $customers[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'phone_number' => $row['phone_number']
    ];
}

// Return data dalam format JSON
header('Content-Type: application/json');
echo json_encode($customers);
?>
