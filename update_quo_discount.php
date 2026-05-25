<?php
include "conn.php";

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quonum = $_POST['quonum'] ?? null;
    $discount_value = $_POST['discount_value'] ?? null;
    $discount_type = $_POST['discount_type'] ?? null;

    if ($quonum && $discount_value && $discount_type) {
        try {
            // Update tabel quo berdasarkan quonum
            $sql = "UPDATE quo SET disc_all = ?, disc_type = ?, updated_at = NOW() WHERE quo_num = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('dss', $discount_value, $discount_type, $quonum);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Discount updated successfully.';
            } else {
                $response['message'] = 'No changes were made.';
            }

            $stmt->close();
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Invalid input data.';
    }
}

// Output response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
