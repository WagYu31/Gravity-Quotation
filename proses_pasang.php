<?php
// Set timezone ke Jakarta
date_default_timezone_set('Asia/Jakarta');

// Include koneksi database
include 'conn.php';

// Ambil data dari URL
$customer_phone = isset($_GET['customer_phone']) ? $_GET['customer_phone'] : '';
$kegiatan = isset($_GET['kegiatan']) ? $_GET['kegiatan'] : '';
$request = isset($_GET['request']) ? $_GET['request'] : '';
$kode = isset($_GET['kode']) ? $_GET['kode'] : '';

// Validasi input
if (empty($customer_phone)) {
    die("Nomor telepon tidak valid");
}

// 1. Cari customer berdasarkan nomor telepon
$query_customer = "SELECT id FROM customer WHERE phone_number = ? AND deleted_at IS NULL LIMIT 1";
$stmt_customer = $conn->prepare($query_customer);
$stmt_customer->bind_param("s", $customer_phone);
$stmt_customer->execute();
$result_customer = $stmt_customer->get_result();

if ($result_customer->num_rows == 0) {
    die("Customer tidak ditemukan $customer_phone");
}

$customer = $result_customer->fetch_assoc();
$customer_id = $customer['id'];

// 2. Generate kode unik
$quo_code = substr(md5(uniqid()), 0, 7);
$quonum = substr(md5(uniqid()), 0, 15);
$now = date('Y-m-d H:i:s');
$status = "temp";

// 3. Insert ke tabel quo
$query_insert = "INSERT INTO quo 
                (quo_code, quo_num, customer_id, status, users_id, kegiatan_kode, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($query_insert);
$stmt_insert->bind_param("ssisisss", $quo_code, $quonum, $customer_id, $status, $user_id, $kode, $now, $now);

if ($stmt_insert->execute()) {
    $idQuo = $stmt_insert->insert_id;
    
    // 4. Redirect ke halaman quotation baru
    header("Location: newQuotation.php?quonum=$quonum&id=$idQuo");
    exit();
} else {
    die("Gagal membuat quotation baru: " . $conn->error);
}

// Tutup koneksi
$stmt_customer->close();
$stmt_insert->close();
$conn->close();
?>