<?php
require 'conn.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Query SQL untuk mengambil data dari quo_detail
    $query = "
        SELECT 
            qd.id, 
            qd.qty, 
            qd.price, 
            CASE
                WHEN qd.disc_type = 'n' THEN qd.disc_item
                ELSE REPLACE(qd.disc_type, '%', '') 
            END AS discount_value,
            CASE
                WHEN qd.disc_type = 'n' THEN 'nominal'
                ELSE 'percent'
            END AS discount_type,
            b.code AS product_name
        FROM quo_detail qd
        JOIN barang b ON b.id = qd.barang_id
        WHERE qd.id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        // Kirim data sebagai JSON
        echo json_encode($data);
    } else {
        echo json_encode(["error" => "Data not found"]);
    }
} else {
    echo json_encode(["error" => "Invalid request"]);
}
?>
