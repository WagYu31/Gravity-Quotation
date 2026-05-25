<?php
include 'conn.php'; // File koneksi database

// Ambil data dari request JSON
$request = json_decode(file_get_contents('php://input'), true);

$customer_id = $request['customer_id'] ?? null;
$quonum = $request['quonum'] ?? null;
$response = ['success' => false];

if ($customer_id) {
    if ($quonum) {
        // Jika quonum sudah ada, lakukan update
        $sql = "UPDATE quo SET customer_id = ?, updated_at = NOW() WHERE quo_num = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $customer_id, $quonum);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['quonum'] = $quonum;
        } else {
            $response['message'] = 'Failed to update quotation.';
        }
        $stmt->close();
    } else {
        // Jika quonum tidak ada, lakukan insert
        $quo_code = substr(md5(uniqid()), 0, 7); // 7 kode random
        $quonum = substr(md5(uniqid()), 0, 55); // 15 kode random

        $sql = "INSERT INTO quo (quo_code, quo_num, customer_id, users_id, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, 'temp', NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssis', $quo_code, $quonum, $customer_id, $user_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['quonum'] = $quonum;
            $idQuo = $conn->insert_id;
            $response['idQuo'] = $idQuo;
        } else {
            $response['message'] = 'Failed to create new quotation.';
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode($response);
?>
