<?php
// fetch_table_data.php
include 'conn.php'; // Pastikan koneksi database sudah benar

$quo_num = $_GET['quonum'];

// Ambil ID quo berdasarkan quo_num
$stmt = $conn->prepare("SELECT id FROM quo WHERE quo_num = ?");
$stmt->bind_param('s', $quo_num);
$stmt->execute();
$result = $stmt->get_result();
$quo = $result->fetch_assoc();

if ($quo) {
    $quo_id = $quo['id'];

    // Ambil detail barang berdasarkan quo_id
    $stmt = $conn->prepare("SELECT qd.*, b.product_code, b.price FROM quo_detail qd JOIN barang b ON qd.barang_id = b.id WHERE qd.quo_id = ?");
    $stmt->bind_param('i', $quo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Kirim data sebagai JSON
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['amount'] = ($row['price'] - $row['disc_item']) * $row['qty']; // Hitung amount
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]);
}

$conn->close();
?>
