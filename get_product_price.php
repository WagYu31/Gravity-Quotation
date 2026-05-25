<?php
include 'conn.php';

$productId = $_GET['id'];

$stmt = $conn->prepare("SELECT price FROM barang WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    echo json_encode(['price' => $product['price']]);
} else {
    echo json_encode(['price' => 0]); // Harga default jika tidak ditemukan
}
?>
