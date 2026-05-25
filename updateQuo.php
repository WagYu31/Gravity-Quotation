<?php
// Pastikan untuk menghubungkan ke database Anda
include('conn.php');

// Ambil data JSON yang dikirim dari client
$data = json_decode(file_get_contents('php://input'), true);

// Ambil nilai dari data
$quoNum = $data['quo_num'];
$quoCode = $data['quo_code'];
$status = $data['status'];

// Validasi dan sanitasi input untuk menghindari SQL injection

// Query untuk mengambil customer_id berdasarkan quo_num
$queryCustomer = "SELECT customer_id FROM quo WHERE quo_num = ?";
$stmtCustomer = $conn->prepare($queryCustomer);
$stmtCustomer->bind_param("s", $quoNum);
$stmtCustomer->execute();
$result = $stmtCustomer->get_result();
$row = $result->fetch_assoc();
$customerId = $row['customer_id'];

// Validasi keberadaan customer_id
if (!$customerId) {
    echo json_encode(['error' => 'Customer not found']);
    exit;
}

// Query untuk mendapatkan nama customer dan update quo_code (dengan pembatasan panjang)
$queryUpdate = "UPDATE quo 
                JOIN customer ON quo.customer_id = customer.id
                SET quo_code = ?, status = ?
                WHERE quo.quo_num = ?";
$stmtUpdate = $conn->prepare($queryUpdate);
$stmtUpdate->bind_param("sss", $quoCode, $status, $quoNum);

// Eksekusi query
if ($stmtUpdate->execute()) {
    // Kirimkan response sukses
    echo json_encode(['success' => true]);
} else {
    // Kirimkan response error
    echo json_encode(['error' => 'Failed to update quo']);
}

// Tutup koneksi
$stmtUpdate->close();
$stmtCustomer->close();
$conn->close();
?>