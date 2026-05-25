<?php
include 'conn.php'; // Pastikan file koneksi Anda benar

if (!isset($_GET['quonum']) && !isset($_GET['id'])) {
    $_SESSION['error'] = 'Nomor quotation tidak ditemukan.';
    header("Location: listQuotation.php");
    exit();
}

$quoNum = $_GET['quonum'];
$quoId = $_GET['id'];

// Ambil status quo berdasarkan quonum
$sql = "SELECT * FROM quo WHERE quo_num = ? AND id = ? AND deleted_at IS NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $quoNum, $quoId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $quo = $result->fetch_assoc();
    $quoIdLama = $quo['id'];
    $quoStatus = $quo['status'];

    // Jika status bukan 'saved', redirect langsung ke halaman edit
    if ($quoStatus !== 'saved') {
        header("Location: newQuotation.php?quonum={$quoNum}&id={$quoIdLama}");
        exit();
    }

    // Jika status adalah 'saved', mulai proses duplikasi
    $conn->begin_transaction();

    try {
        // Duplikasi data dari quo
        $sqlDuplicateQuo = "INSERT INTO quo (quo_code, quo_num, add_note, `from`, customer_id, users_id, disc_all, disc_type, status, created_at, updated_at) 
                             SELECT quo_code, quo_num, add_note, `from`, customer_id, users_id, disc_all, disc_type, 'edited', NOW(), NOW()
                             FROM quo 
                             WHERE id = ?";
        $stmtDuplicateQuo = $conn->prepare($sqlDuplicateQuo);
        $stmtDuplicateQuo->bind_param('i', $quoIdLama);

        if (!$stmtDuplicateQuo->execute()) {
            throw new Exception("Error inserting into quo: " . $stmtDuplicateQuo->error);
        }

        // Ambil ID quo baru
        $quoIdBaru = $conn->insert_id;

        // Duplikasi data dari quo_detail
        $sqlDuplicateDetail = "INSERT INTO quo_detail (quo_id, barang_id, barang_name, qty, price, disc_type, disc_item, created_at, updated_at) 
                               SELECT ?, barang_id, barang_name, qty, price, disc_type, disc_item, NOW(), NOW() 
                               FROM quo_detail 
                               WHERE quo_id = ? AND deleted_at IS NULL";
        $stmtDuplicateDetail = $conn->prepare($sqlDuplicateDetail);
        $stmtDuplicateDetail->bind_param('ii', $quoIdBaru, $quoIdLama);

        if (!$stmtDuplicateDetail->execute()) {
            throw new Exception("Error inserting into quo_detail: " . $stmtDuplicateDetail->error);
        }

        // Commit transaksi
        $conn->commit();

        // Redirect ke halaman edit quotation baru
        header("Location: newQuotation.php?quonum={$quoNum}&id={$quoIdBaru}");
        exit();
    } catch (Exception $e) {
        // Rollback jika ada error
        $conn->rollback();
        echo 'Terjadi kesalahan saat memproses duplikasi data: ' . $e->getMessage();
        // $_SESSION['error'] = 'Terjadi kesalahan saat memproses duplikasi data: ' . $e->getMessage();
        error_log($e->getMessage()); // Log error ke file log PHP
        // header("Location: listQuotation.php");
        // exit();
    }
} else {
    echo 'Quotation tidak ditemukan.';
    // $_SESSION['error'] = 'Quotation tidak ditemukan.';
    // header("Location: listQuotation.php");
    // exit();
}
?>
