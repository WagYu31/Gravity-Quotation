<?php
date_default_timezone_set('Asia/Jakarta');

// Mendapatkan data input JSON dari AJAX
$input = json_decode(file_get_contents('php://input'), true);

// Koneksi ke database
include "conn.php"; // Pastikan koneksi sudah benar

// Ambil nilai input dari AJAX
$product_id = $input['product_id'];
$price = $input['price'];
$qty = $input['qty'];
$discount_value = $input['discount_value'];
$discount_type = $input['discount_type'];
$quo_num = $input['quo_num'] ?? null; // Ambil quo_num yang sudah ada atau null jika tidak ada
$users_id = '1'; // Atur ID pengguna sesuai dengan kebutuhan Anda

// Jika quo_num belum ada, buat kode baru
if (!$quo_num) {
    $quo_num = generateQuoNum(); // Generate quo_num baru jika tidak ada
}

// Generate quo_code (5 kode random)
$quo_code = generateQuoCode(); // Fungsi untuk membuat 5 kode acak

// Mulai transaksi
$conn->begin_transaction();

try {
    // Cek apakah quo_num sudah ada di tabel quo
    $stmt = $conn->prepare("SELECT id FROM quo WHERE quo_num = ? LIMIT 1");
    $stmt->bind_param('s', $quo_num);
    $stmt->execute();
    $result = $stmt->get_result();

    // Jika quo_num sudah ada, ambil ID quo yang terkait
    if ($result->num_rows > 0) {
        $quo = $result->fetch_assoc();
        $quo_id = $quo['id'];

        // Update updated_at pada quo jika sudah ada
        $stmt = $conn->prepare("UPDATE quo SET updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('i', $quo_id);
        $stmt->execute();
    } else {
        // Jika quo_num tidak ada, buat entri baru pada tabel quo
        $stmt = $conn->prepare("INSERT INTO quo (quo_code, quo_num, users_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->bind_param('sss', $quo_code, $quo_num, $users_id);
        $stmt->execute();

        // Ambil ID quo yang baru saja dibuat
        $quo_id = $stmt->insert_id;
    }

    // Insert detail produk ke quo_detail
    $stmt = $conn->prepare("INSERT INTO quo_detail (quo_id, barang_id, qty, price, disc_type, disc_item, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param('iiidss', $quo_id, $product_id, $qty, $price, $discount_type, $discount_value);
    $stmt->execute();

    // Commit transaksi jika semua berhasil
    $conn->commit();

    // Kirim response sukses dan data yang baru disimpan
    echo json_encode(['success' => true, 'quo_num' => $quo_num]);
} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollback();

    // Kirim response error
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    // Tutup koneksi database
    $conn->close();
}

// Fungsi untuk meng-generate quo_num dengan format 7 kode random-7 kode random-7 kode random-7 kode random
function generateQuoNum() {
    $generateRandomCode = function() {
        return strtoupper(substr(bin2hex(random_bytes(4)), 0, 7)); // 7 karakter acak
    };
    return $generateRandomCode() . '-' . $generateRandomCode() . '-' . $generateRandomCode() . '-' . $generateRandomCode();
}

// Fungsi untuk meng-generate quo_code (5 kode random)
function generateQuoCode() {
    return strtoupper(substr(bin2hex(random_bytes(3)), 0, 5)); // 5 karakter acak
}
?>
