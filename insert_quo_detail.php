<?php
require 'conn.php'; // Ganti dengan koneksi database Anda

// Set timezone Jakarta
date_default_timezone_set('Asia/Jakarta');

// Ambil data dari request
$data = json_decode(file_get_contents('php://input'), true);

// Validasi data input
if (empty($data['quo_num']) || empty($data['product_id']) || empty($data['product_name']) ||  empty($data['qty']) || empty($data['price']) || !isset($data['idquo']) || empty($data['discount_type']) || !isset($data['discount_value'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Tentukan $quo_id berdasarkan kondisi idquo
if (!empty($data['idquo'])) {
    // Jika idquo tidak null, gunakan nilai idquo sebagai quo_id
    $quo_id = $data['idquo'];
} else {
    // Jika idquo null, ambil quo_id berdasarkan quo_num
    $quo_num = $data['quo_num'];
    $stmt = $conn->prepare("SELECT id FROM quo WHERE quo_num = ?");
    $stmt->bind_param("s", $quo_num);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid quo number']);
        exit;
    }
    $quo = $result->fetch_assoc();
    $quo_id = $quo['id'];
}

// Lanjutkan proses dengan $quo_id

// Hitung disc_item dan ubah disc_type
$price = (float)$data['price'];
$qty = (int)$data['qty'];
$discount_value = (float)$data['discount_value'];
$disc_type = $data['discount_type'] === 'percent' ? "{$discount_value}%" : ($data['discount_type'] === 'nominal' ? 'n' : null);

if ($disc_type === "{$discount_value}%") {
    // Diskon persen
    $disc_item = ($price * $qty) * ($discount_value / 100);
} elseif ($disc_type === 'n') {
    // Diskon nominal
    $disc_item = $discount_value;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid discount type']);
    exit;
}

// Insert ke tabel quo_detail
$created_at = $updated_at = date('Y-m-d H:i:s');
$stmt = $conn->prepare("INSERT INTO quo_detail (quo_id, barang_id, barang_name, qty, price, disc_type, disc_item, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisidssss", $quo_id, $data['product_id'], $data['product_name'], $qty, $price, $disc_type, $disc_item, $created_at, $updated_at);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Data inserted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database insert failed']);
}

$stmt->close();
$conn->close();
?>
