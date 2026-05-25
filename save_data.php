<?php
require_once 'conn.php'; // Hubungkan ke database

header('Content-Type: application/json');

try {
    // Ambil data dari AJAX
    $input = json_decode(file_get_contents('php://input'), true);

    $product_id = $input['product_id'];
    $price = (float)$input['price'];
    $qty = (int)$input['qty'];
    $discount_value = (float)$input['discount_value'];
    $discount_type = $input['discount_type'];
    $quonum = $input['quonum'];

    if (empty($quonum)) {
        throw new Exception('Quo number is required.');
    }

    // Ambil ID `quo` berdasarkan `quonum`
    $query = "SELECT id FROM quo WHERE quo_num = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$quonum]);
    $quo_id = $stmt->fetchColumn();

    if (!$quo_id) {
        throw new Exception('Quo not found for the provided number.');
    }

    // Hitung diskon
    $disc_item = $discount_type === 'p' ? ($price * $discount_value / 100) : $discount_value;

    // Insert ke `quo_detail`
    $query = "INSERT INTO quo_detail (quo_id, barang_id, qty, price, disc_type, disc_item, created_at, updated_at)
              VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $db->prepare($query);
    $stmt->execute([$quo_id, $product_id, $qty, $price, $discount_type, $disc_item]);

    // Ambil data terbaru untuk respon
    $query = "SELECT b.code AS product_code, qd.qty, qd.price, 
                     CONCAT(qd.disc_item, IF(qd.disc_type = 'p', '%', ' Rp')) AS discount, 
                     (qd.qty * qd.price - qd.disc_item) AS amount
              FROM quo_detail qd
              JOIN barang b ON qd.barang_id = b.id
              WHERE qd.quo_id = ?
              ORDER BY qd.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$quo_id]);
    $newData = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'newData' => $newData]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
