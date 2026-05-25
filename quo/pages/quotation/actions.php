<?php
// Panggil file koneksi database
include '../../includes/db.php';

// Fungsi helper untuk mengirim respons JSON dan menghentikan skrip
function send_json_response($data) {
    // Pastikan header ini dipanggil sebelum output lain
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode($data);
    exit();
}

// ===================================================================
// PENANGANAN AKSI DARI SIMPAN/EDIT QUOTATION (DATA JSON)
// ===================================================================
$data_json = json_decode(file_get_contents('php://input'), true);
if ($data_json && isset($data_json['action']) && $data_json['action'] === 'save_quotation') {
    
    // Keamanan: Pastikan user sudah login
    if (!isset($_SESSION['user_id'])) {
        send_json_response(['success' => false, 'message' => 'Akses ditolak. Silakan login kembali.']);
    }

    $conn->begin_transaction();
    try {
        $userId = $_SESSION['user_id'];
        $status = $data_json['status'];
        if (empty($data_json['customer_id'])) { throw new Exception("Customer harus dipilih."); }

        $parent_id = !empty($data_json['quotation_id']) ? (int)$data_json['quotation_id'] : null;
        
        $quotationCode = null;
        if ($status === 'FINAL') {
            $uniquePart = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $datePart = date('ymd');
            $quotationCode = "QLX" . $uniquePart . $datePart;
        }

        $ppnPercentage = ($data_json['issuer'] === 'CV') ? 11.00 : 0.00;
        $quotationDate = date('Y-m-d');
        
        $stmt_q = $conn->prepare("INSERT INTO quotations (parent_quotation_id, quotation_code, customer_id, user_id, issuer, quotation_date, status, notes, overall_discount_type, overall_discount_value, ppn_percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_q->bind_param("isiisssssdd", $parent_id, $quotationCode, $data_json['customer_id'], $userId, $data_json['issuer'], $quotationDate, $status, $data_json['notes'], $data_json['overall_discount_type'], $data_json['overall_discount_value'], $ppnPercentage);
        $stmt_q->execute();
        $newQuotationId = $conn->insert_id;
        
        $subtotal = 0;
        if (!empty($data_json['items'])) {
            $stmt_i = $conn->prepare( "INSERT INTO quotation_items (quotation_id, barang_id, item_name, item_description, quantity, item_price, discount_type, discount_value, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($data_json['items'] as $item) {
                $qty = (float)($item['quantity'] ?? 0); $price = (float)($item['price'] ?? 0); $discVal = (float)($item['discount_value'] ?? 0);
                $discType = $item['discount_type'] ?? 'AMOUNT'; $barang_id = !empty($item['barang_id']) ? $item['barang_id'] : null; $item_desc = $item['desc'] ?? '';
                $itemDiscount = ($discType === 'PERCENT') ? ($price * $qty) * ($discVal / 100) : ($discVal * $qty);
                $totalAmount = ($price * $qty) - $itemDiscount; $subtotal += $totalAmount;
                $stmt_i->bind_param("iisssdsdd", $newQuotationId, $barang_id, $item['name'], $item_desc, $qty, $price, $discType, $discVal, $totalAmount);
                $stmt_i->execute();
            }
        }

        $overallDiscVal = (float)($data_json['overall_discount_value'] ?? 0); $overallDiscType = $data_json['overall_discount_type'] ?? 'AMOUNT';
        $overallDiscountAmount = ($overallDiscType === 'PERCENT') ? $subtotal * ($overallDiscVal / 100) : $overallDiscVal;
        $totalAfterDiscount = $subtotal - $overallDiscountAmount; $ppnAmount = $totalAfterDiscount * ($ppnPercentage / 100);
        $grandTotal = $totalAfterDiscount + $ppnAmount;
        $stmt_u = $conn->prepare("UPDATE quotations SET grand_total = ? WHERE id = ?");
        $stmt_u->bind_param("di", $grandTotal, $newQuotationId);
        $stmt_u->execute();

        $conn->commit();
        send_json_response(['success' => true, 'quotation_id' => $newQuotationId, 'quotation_code' => $quotationCode, 'message' => 'Penawaran berhasil disimpan.']);
    } catch (Exception $e) {
        $conn->rollback();
        send_json_response(['success' => false, 'message' => $e->getMessage()]);
    }
}

// ===================================================================
// PENANGANAN AKSI DARI FORM PROGRESS (METHOD POST BIASA)
// ===================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'update_progress') {
        if (!isset($_SESSION['user_id'])) { send_json_response(['success' => false, 'message' => 'Akses ditolak.']); }
        
        $id = (int)$_POST['id'];
        $step = $_POST['step'];
        $stmt = null;
        
        switch ($step) {
            case 'so':
                $stmt = $conn->prepare("UPDATE quotations SET progress_status='SO', so_number=?, so_start_date=?, so_instalasi_address=?, so_contact_person=? WHERE id=?");
                $stmt->bind_param("ssssi", $_POST['so_number'], $_POST['so_start_date'], $_POST['so_instalasi_address'], $_POST['so_contact_person'], $id);
                break;
            case 'sj':
                $stmt = $conn->prepare("UPDATE quotations SET progress_status='SJ', sj_number=?, sj_date=? WHERE id=?");
                $stmt->bind_param("ssi", $_POST['sj_number'], $_POST['sj_date'], $id);
                break;
            case 'bast':
                $stmt = $conn->prepare("UPDATE quotations SET progress_status='BAST', bast_number=?, bast_date=?, bast_notes=? WHERE id=?");
                $stmt->bind_param("sssi", $_POST['bast_number'], $_POST['bast_date'], $_POST['bast_notes'], $id);
                break;
            case 'invoice':
                $stmt = $conn->prepare("UPDATE quotations SET progress_status='INVOICE', invoice_number=?, invoice_date=? WHERE id=?");
                $stmt->bind_param("ssi", $_POST['invoice_number'], $_POST['invoice_date'], $id);
                break;
            case 'selesai':
                $final_status = $_POST['final_status'];
                $stmt = $conn->prepare("UPDATE quotations SET progress_status=?, status=? WHERE id=?");
                $stmt->bind_param("ssi", $final_status, $final_status, $id);
                break;
            default:
                send_json_response(['success' => false, 'message' => 'Langkah tidak valid.']);
        }

        if ($stmt && $stmt->execute()) {
            send_json_response(['success' => true, 'message' => 'Progress berhasil diperbarui.']);
        } else {
            send_json_response(['success' => false, 'message' => 'Gagal memperbarui progress: ' . ($stmt ? $stmt->error : $conn->error)]);
        }
        $stmt->close();
    }
}

// ===================================================================
// PENANGANAN AKSI DARI LINK (METHOD GET)
// ===================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
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

// Jika tidak ada aksi yang cocok sama sekali
send_json_response(['success' => false, 'message' => 'Aksi tidak valid atau metode request salah.']);
?>