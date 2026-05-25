<?php
include '../../includes/db.php';

// Cek data yang dikirim via POST (AJAX atau form biasa)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'update_progress') {
        session_start();
        if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'message' => 'Akses ditolak.']); exit(); }
        $id = (int)$_POST['id']; $step = $_POST['step'];
        switch ($step) {
            case 'so':
                $stmt = $conn->prepare("UPDATE quotations SET progress_status='SO', so_number=?, so_start_date=?, so_instalasi_address=?, so_contact_person=? WHERE id=?");
                $stmt->bind_param("ssssi", $_POST['so_number'], $_POST['so_start_date'], $_POST['so_instalasi_address'], $_POST['so_contact_person'], $id); break;
            case 'sj':
                $stmt = $conn->prepare("UPDATE quotations SET progress_status='SJ', sj_number=?, sj_date=? WHERE id=?");
                $stmt->bind_param("ssi", $_POST['sj_number'], $_POST['sj_date'], $id); break;
            case 'bast':
                $stmt = $conn->prepare("UPDATE quotations SET progress_status='BAST', bast_number=?, bast_date=?, bast_notes=? WHERE id=?");
                $stmt->bind_param("sssi", $_POST['bast_number'], $_POST['bast_date'], $_POST['bast_notes'], $id); break;
            case 'invoice':
                $stmt = $conn->prepare("UPDATE quotations SET progress_status='INVOICE', invoice_number=?, invoice_date=? WHERE id=?");
                $stmt->bind_param("ssi", $_POST['invoice_number'], $_POST['invoice_date'], $id); break;
            case 'selesai':
                $final_status = $_POST['final_status'];
                $stmt = $conn->prepare("UPDATE quotations SET progress_status=?, status=? WHERE id=?");
                $stmt->bind_param("ssi", $final_status, $final_status, $id); break;
            default: echo json_encode(['success' => false, 'message' => 'Langkah tidak valid.']); exit();
        }
        if ($stmt->execute()) { echo json_encode(['success' => true, 'message' => 'Progress berhasil diperbarui.']); } 
        else { echo json_encode(['success' => false, 'message' => 'Gagal memperbarui progress.']); }
        $stmt->close(); exit();
    }
}

// Cek data yang dikirim sebagai JSON (untuk simpan/edit penawaran)
$data_post = json_decode(file_get_contents('php://input'), true);
if ($data_post && isset($data_post['action']) && $data_post['action'] === 'save_quotation') {
    if (session_status() == PHP_SESSION_NONE) { session_start(); }
    if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'message' => 'Sesi tidak valid.']); exit(); }
    $conn->begin_transaction();
    try {
        $userId = $_SESSION['user_id']; $status = $data_post['status'];
        if (empty($data_post['customer_id'])) throw new Exception("Customer harus dipilih.");
        $parent_id = !empty($data_post['quotation_id']) ? (int)$data_post['quotation_id'] : null;
        $quotationCode = null;
        if ($status === 'FINAL') { $uniquePart = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT); $datePart = date('ymd'); $quotationCode = "QLX" . $uniquePart . $datePart; }
        $ppnPercentage = ($data_post['issuer'] === 'CV') ? 11.00 : 0.00;
        $quotationDate = date('Y-m-d');
        $stmt_q = $conn->prepare("INSERT INTO quotations (parent_quotation_id, quotation_code, customer_id, user_id, issuer, quotation_date, status, notes, overall_discount_type, overall_discount_value, ppn_percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_q->bind_param("isiisssssdd", $parent_id, $quotationCode, $data_post['customer_id'], $userId, $data_post['issuer'], $quotationDate, $status, $data_post['notes'], $data_post['overall_discount_type'], $data_post['overall_discount_value'], $ppnPercentage);
        $stmt_q->execute(); $newQuotationId = $conn->insert_id;
        $subtotal = 0;
        if (!empty($data_post['items'])) {
            $stmt_i = $conn->prepare( "INSERT INTO quotation_items (quotation_id, barang_id, item_name, item_description, quantity, item_price, discount_type, discount_value, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($data_post['items'] as $item) {
                $qty = (float)($item['quantity']??0); $price = (float)($item['price']??0); $discVal = (float)($item['discount_value']??0);
                $discType = $item['discount_type']??'AMOUNT'; $barang_id = !empty($item['barang_id']) ? $item['barang_id'] : null; $item_desc = $item['desc']??'';
                $itemDiscount = ($discType==='PERCENT')?($price*$qty)*($discVal/100):($discVal*$qty);
                $totalAmount = ($price*$qty) - $itemDiscount; $subtotal += $totalAmount;
                $stmt_i->bind_param("iisssdsdd", $newQuotationId, $barang_id, $item['name'], $item_desc, $qty, $price, $discType, $discVal, $totalAmount);
                $stmt_i->execute();
            }
        }
        $overallDiscVal = (float)($data_post['overall_discount_value']??0); $overallDiscType = $data_post['overall_discount_type']??'AMOUNT';
        $overallDiscountAmount = ($overallDiscType==='PERCENT')?$subtotal*($overallDiscVal/100):$overallDiscVal;
        $totalAfterDiscount = $subtotal - $overallDiscountAmount; $ppnAmount = $totalAfterDiscount * ($ppnPercentage/100);
        $grandTotal = $totalAfterDiscount + $ppnAmount;
        $stmt_u = $conn->prepare("UPDATE quotations SET grand_total = ? WHERE id = ?");
        $stmt_u->bind_param("di", $grandTotal, $newQuotationId);
        $stmt_u->execute();
        $conn->commit();
        echo json_encode(['success' => true, 'quotation_id' => $newQuotationId, 'quotation_code' => $quotationCode, 'message' => 'Berhasil disimpan.']);
    } catch (Exception $e) { $conn->rollback(); echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
    exit();
}

// Cek aksi dari URL GET (Approve)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if (session_status() == PHP_SESSION_NONE) { session_start(); }
    if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit(); }
    if ($_GET['action'] === 'approve' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("UPDATE quotations SET status = 'APPROVED', progress_status = 'APPROVED' WHERE id = ? AND status = 'FINAL'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header('Location: progress.php?id=' . $id);
        exit();
    }
}
?>