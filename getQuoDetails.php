<?php
require 'conn.php'; // Sesuaikan dengan koneksi database Anda

$quo_code = $_GET['quo_code'] ?? '';

$query = "
    SELECT 
        c.name AS customer_name,
        q.id AS quoId,
        q.quo_code,
        q.status,
        q.updated_at,
        q.created_at,
        q.users_id,
        SUM(qd.qty * qd.price) - SUM(qd.disc_item) AS total_amount,
        SUM(qd.qty * qd.price) AS total_sub_amount,
        SUM(qd.disc_item) AS total_discount,
        q.disc_all,
        q.disc_type,
        q.from,
        q.quo_num,
        u.alias
    FROM quo_detail qd
    JOIN quo q ON qd.quo_id = q.id
    JOIN customer c ON q.customer_id = c.id
    JOIN users u ON q.users_id = u.id
    WHERE q.quo_code = ? AND q.deleted_at IS NULL AND qd.deleted_at IS NULL
    GROUP BY q.id, c.name, q.quo_code, q.status, q.updated_at, q.disc_all, q.disc_type, q.from, u.alias
    ORDER BY q.id DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param('s', $quo_code);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
