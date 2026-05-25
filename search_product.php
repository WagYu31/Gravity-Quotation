<?php
include 'conn.php';

$query = $_GET['q'];
$searchQuery = "%$query%";

// Cari produk berdasarkan kode atau tipe
$stmt = $conn->prepare("SELECT id, code, kategori, price FROM barang WHERE code LIKE ? OR kategori LIKE ? LIMIT 10");
$stmt->bind_param("ss", $searchQuery, $searchQuery);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);
?>
