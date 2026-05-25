<?php
// Koneksi ke database
include "conn.php";

// Ambil kata kunci pencarian
$search = $_POST['search'];

// Query untuk mencari customer
$sql = "SELECT id, name, phone_number FROM customer 
        WHERE (name LIKE '%$search%' OR phone_number LIKE '%$search%') 
        AND deleted_at IS NULL 
        LIMIT 10"; // Batasi hasil pencarian
$result = $conn->query($sql);

// Format hasil pencarian
$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['id'], // ID customer
            'text' => $row['name'] . ' - ' . $row['phone_number'] // Nama dan nomor telepon
        ];
    }
}

// Keluarkan hasil dalam format JSON
echo json_encode($data);
?>