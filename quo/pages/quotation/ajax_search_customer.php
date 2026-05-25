<?php
// Pastikan path ke file db.php sudah benar
include '../../includes/db.php';

// Set header sebagai JSON untuk respons
header('Content-Type: application/json');

// Keamanan dasar: pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    // Kirim respons JSON kosong jika belum login
    echo json_encode(['results' => []]);
    exit();
}

// Ambil istilah pencarian dari parameter GET
$searchTerm = $_GET['term'] ?? '';

// PENTING: Jika istilah pencarian kosong atau hanya spasi, jangan lakukan query
if (trim($searchTerm) === '') {
    echo json_encode(['results' => []]);
    exit();
}

// Tambahkan wildcard '%' untuk pencarian 'CONTAINS' (mengandung kata)
$searchTermWithWildcards = "%{$searchTerm}%";

// Query untuk mencari customer berdasarkan nama, telepon, ATAU nama toko
$query = "SELECT id, name, phone_number, store_name FROM customer WHERE (name LIKE ? OR phone_number LIKE ? OR store_name LIKE ?) AND deleted_at IS NULL ORDER BY name ASC";

$stmt = $conn->prepare($query);

if ($stmt === false) {
    // error_log("MySQL prepare error: " . $conn->error);
    echo json_encode(['results' => []]);
    exit();
}

// PERUBAHAN DI SINI: Sesuaikan jumlah parameter dengan jumlah '?' pada query
$stmt->bind_param("sss", $searchTermWithWildcards, $searchTermWithWildcards, $searchTermWithWildcards);

// Eksekusi statement
$stmt->execute();
$result = $stmt->get_result();

$customers = [];
while ($row = $result->fetch_assoc()) {
    // Buat teks yang akan ditampilkan di dropdown, kini termasuk nama toko
    $displayText = htmlspecialchars($row['name']);

    if (!empty($row['store_name'])) {
        $displayText .= " - " . htmlspecialchars($row['store_name']);
    }
    if (!empty($row['phone_number'])) {
        $displayText .= " (" . htmlspecialchars($row['phone_number']) . ")";
    }

    // Format data sesuai yang dibutuhkan Select2
    $customers[] = [
        'id' => $row['id'],
        'text' => $displayText
    ];
}

// Tutup statement dan koneksi
$stmt->close();
$conn->close();

// Kirim hasil akhir dalam format JSON
echo json_encode(['results' => $customers]);
?>