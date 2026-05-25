<?php
// Ambil nilai $quonum dari URL atau parameter lainnya
$quonum = $_GET['quonum'] ?? '';
$quoId = $_GET['id'] ?? '';

// Query untuk mendapatkan data detail berdasarkan quo_num
$stmt = $conn->prepare("
                                                        SELECT 
                                                            qd.id, 
                                                            b.code AS kode_produk,
                                                            b.kategori,
                                                            b.satuan,
                                                            b.desc AS description,
                                                            b.name_link_1, 
                                                            b.name_link_2,
                                                            b.link_1, 
                                                            b.link_2, 
                                                            b.image,
                                                            qd.qty,
                                                            qd.barang_name,
                                                            qd.price, 
                                                            qd.disc_type, 
                                                            qd.disc_item, 
                                                            (qd.qty * qd.price) AS amount
                                                        FROM quo_detail qd
                                                        JOIN barang b ON qd.barang_id = b.id
                                                        JOIN quo q ON qd.quo_id = q.id
                                                        WHERE q.quo_num = ? AND qd.deleted_at IS NULL AND q.id = ?
                                                    ");
$stmt->bind_param("si", $quonum, $quoId);
$stmt->execute();
$result = $stmt->get_result();
$totalRow = $result->num_rows;

// Query untuk menghitung total discount dan total amount
$totalStmt = $conn->prepare("
                                                SELECT 
                                                    SUM(qd.disc_item) AS total_discount, 
                                                    SUM(qd.qty * qd.price) AS total_sub_amount,
                                                    SUM((qd.qty * qd.price) - qd.disc_item) AS total_amount,
                                                    q.disc_all,
                                                    q.disc_type,
                                                    q.from,
                                                    q.id AS id_quo
                                                FROM quo_detail qd
                                                JOIN quo q ON qd.quo_id = q.id
                                                WHERE q.quo_num = ? AND q.id = ? AND qd.deleted_at IS NULL
                                            ");
$totalStmt->bind_param("si", $quonum, $quoId);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totals = $totalResult->fetch_assoc();

$total_discount = $totals['total_discount'] ?? 0;
$total_amount = $totals['total_amount'] ?? 0;
$total_sub_amount = $totals['total_sub_amount'] ?? 0;
$from = $totals['from'] ?? null;
$disc_all = $totals['disc_all'] ?? 0;
$disc_type = $totals['disc_type'] ?? null;

// Hitung total_non_ppn berdasarkan disc_type
if ($disc_type === 'p') {
    // Jika disc_type adalah persen
    $total_non_ppn = $total_amount - ($total_amount * ($disc_all / 100));
    $disc_all = $total_amount * $disc_all / 100;
} elseif ($disc_type === 'n') {
    // Jika disc_type adalah nominal
    $total_non_ppn = $total_amount - $disc_all;
} else {
    // Jika disc_type tidak diketahui atau null, asumsi tanpa diskon
    $total_non_ppn = $total_amount;
}

// Hitung PPN
$ppn = 0;
if ($from === 'CV') {
    $ppn = $total_amount * 0.11;
}

$total_ppn = $total_non_ppn + $ppn;
