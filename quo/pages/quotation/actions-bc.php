<?php
// Atur header untuk merespons sebagai JSON
header('Content-Type: application/json');

// Panggil file koneksi database dan mulai sesi
include '../../includes/db.php';

// Keamanan: Pastikan hanya user yang sudah login yang bisa mengakses
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login kembali.']);
    exit();
}

// Ambil data JSON yang dikirim dari JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Periksa apakah ada aksi yang diminta dan aksinya adalah 'save_quotation'
if (isset($data['action']) && $data['action'] === 'save_quotation') {
    
    // Mulai transaksi database untuk memastikan semua query berhasil atau semua dibatalkan
    $conn->begin_transaction();
    
    try {
        // Ambil data utama dari JSON yang dikirim
        $userId = $_SESSION['user_id'];
        $status = $data['status'];
        
        // Validasi dasar: Customer harus ada
        if (empty($data['customer_id'])) {
            throw new Exception("Customer harus dipilih.");
        }

        // Logika Riwayat yang sudah benar:
        // Saat mengedit, `parent_id` adalah ID dari baris yang diedit.
        // Saat membuat baru, `parent_id` adalah NULL.
        $parent_id = !empty($data['quotation_id']) ? (int)$data['quotation_id'] : null;
        
        // Buat kode penawaran unik jika statusnya 'FINAL'
        $quotationCode = null;
        if ($status === 'FINAL') {
            $uniquePart = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $datePart = date('ymd');
            $quotationCode = "QLX" . $uniquePart . $datePart;
        }

        // Tentukan PPN dan tanggal
        $ppnPercentage = ($data['issuer'] === 'CV') ? 11.00 : 0.00;
        $quotationDate = date('Y-m-d');
        
        // Selalu INSERT baris baru untuk menjaga riwayat
        $stmt_q = $conn->prepare(
            "INSERT INTO quotations (parent_quotation_id, quotation_code, customer_id, user_id, issuer, quotation_date, status, notes, overall_discount_type, overall_discount_value, ppn_percentage) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt_q->bind_param("isiisssssdd", $parent_id, $quotationCode, $data['customer_id'], $userId, $data['issuer'], $quotationDate, $status, $data['notes'], $data['overall_discount_type'], $data['overall_discount_value'], $ppnPercentage);
        $stmt_q->execute();
        $newQuotationId = $conn->insert_id;
        
        // Proses untuk memasukkan setiap item barang
        $subtotal = 0;
        if (!empty($data['items'])) {
            $stmt_i = $conn->prepare( "INSERT INTO quotation_items (quotation_id, barang_id, item_name, item_description, quantity, item_price, discount_type, discount_value, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($data['items'] as $item) {
                $qty = (float)($item['quantity'] ?? 0);
                $price = (float)($item['price'] ?? 0);
                $discVal = (float)($item['discount_value'] ?? 0);
                $discType = $item['discount_type'] ?? 'AMOUNT';
                $barang_id = !empty($item['barang_id']) ? $item['barang_id'] : null;
                $item_desc = $item['desc'] ?? '';
                $itemDiscount = ($discType === 'PERCENT') ? ($price * $qty) * ($discVal / 100) : ($discVal * $qty);
                $totalAmount = ($price * $qty) - $itemDiscount;
                $subtotal += $totalAmount;
                $stmt_i->bind_param("iisssdsdd", $newQuotationId, $barang_id, $item['name'], $item_desc, $qty, $price, $discType, $discVal, $totalAmount);
                $stmt_i->execute();
            }
        }

        // Lakukan kalkulasi final di sisi server
        $overallDiscVal = (float)($data['overall_discount_value'] ?? 0);
        $overallDiscType = $data['overall_discount_type'] ?? 'AMOUNT';
        $overallDiscountAmount = ($overallDiscType === 'PERCENT') ? $subtotal * ($overallDiscVal / 100) : $overallDiscVal;
        $totalAfterDiscount = $subtotal - $overallDiscountAmount;
        $ppnAmount = $totalAfterDiscount * ($ppnPercentage / 100);
        $grandTotal = $totalAfterDiscount + $ppnAmount;
        
        $stmt_u = $conn->prepare("UPDATE quotations SET grand_total = ? WHERE id = ?");
        $stmt_u->bind_param("di", $grandTotal, $newQuotationId);
        $stmt_u->execute();

        // Jika semua query berhasil, simpan perubahan secara permanen
        $conn->commit();
        echo json_encode(['success' => true, 'quotation_id' => $newQuotationId, 'quotation_code' => $quotationCode, 'message' => 'Penawaran berhasil disimpan.']);
        
    } catch (Exception $e) {
        // Jika terjadi error di salah satu query, batalkan semua perubahan
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// Jika aksi tidak sesuai, kirim pesan error
echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);