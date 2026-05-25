<?php
// Koneksi ke database
include 'conn.php'; // Sesuaikan dengan file koneksi Anda

// Ambil data dari request JSON
$data = json_decode(file_get_contents('php://input'), true);

$quonum = $data['quonum'] ?? null;
$from = $data['from'] ?? null;

$response = ['success' => false, 'message' => ''];

if ($quonum && $from) {
    // Query untuk update quo.from
    $query = "UPDATE quo SET `from` = ?, updated_at = NOW() WHERE quo_num = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $from, $quonum);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Quotation updated successfully.';
    } else {
        $response['message'] = 'Failed to update quotation.';
    }
    $stmt->close();
} else {
    $response['message'] = 'Invalid input.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
