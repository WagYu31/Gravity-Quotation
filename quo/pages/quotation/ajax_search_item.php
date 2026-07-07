<?php
include '../../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['results' => []]);
    exit();
}

$searchTerm = $_GET['term'] ?? '';

if (trim($searchTerm) === '') {
    echo json_encode(['results' => []]);
    exit();
}

$searchTermWithWildcards = "%{$searchTerm}%";

// Query untuk mencari barang berdasarkan kategori, kode, atau deskripsi
$query = "SELECT id, code, kategori, `desc`, price, satuan FROM barang WHERE (kategori LIKE ? OR code LIKE ? OR `desc` LIKE ?) AND deleted_at IS NULL ORDER BY kategori ASC";

$stmt = $conn->prepare($query);

if ($stmt === false) {
    echo json_encode(['results' => []]);
    exit();
}

$stmt->bind_param("sss", $searchTermWithWildcards, $searchTermWithWildcards, $searchTermWithWildcards);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    // Format data yang akan dikirim kembali sebagai JSON
    // Kita sertakan data tambahan (price, name, etc.) agar bisa digunakan nanti
    $items[] = [
        'id' => $row['id'],
        'text' => htmlspecialchars(($row['kategori'] ?? '') . " (" . ($row['code'] ?? '') . ")"),
        'price' => $row['price'],
        'name' => htmlspecialchars($row['kategori'] ?? ''),
        'desc' => htmlspecialchars($row['desc'] ?? ''),
        'unit' => htmlspecialchars($row['satuan'] ?? '')
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['results' => $items]);
?>