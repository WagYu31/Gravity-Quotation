<?php
// Koneksi ke database
require 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari POST
    $id = intval($_POST['edit_id']);
    $price = floatval($_POST['price']);
    $qty = intval($_POST['qty']);
    $discount_value = floatval($_POST['discount_value']);
    $discount_type = $_POST['discount_type'];

    // Validasi discount_type dan hitung diskon
    $disc_type = $discount_type === 'percent'
        ? "{$discount_value}%"
        : ($discount_type === 'nominal'
            ? 'n'
            : null);

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

    // Validasi data
    if ($price <= 0 || $qty <= 0) {
        echo json_encode(['success' => false, 'message' => 'Price and quantity must be greater than 0']);
        exit;
    }

    // Update data di tabel quo_detail
    $query = "
        UPDATE quo_detail 
        SET 
            price = ?, 
            qty = ?, 
            disc_item = ?, 
            disc_type = ? 
        WHERE id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ddssi", $price, $qty, $disc_item, $disc_type, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update data.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
