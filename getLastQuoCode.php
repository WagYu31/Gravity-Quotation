<?php
// Koneksi ke database
include 'conn.php';

$response = ['success' => false];

try {
    // Ambil tanggal hari ini dalam format YYDDMM
    $today = date('y') . date('d') . date('m'); // Format YYDDMM

    // Cari quo_code terakhir dengan format tanggal hari ini
    $query = "SELECT quo_code FROM quo WHERE quo_code LIKE 'QLX____$today' ORDER BY quo_code DESC LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastQuoCode = $row['quo_code'];

        // Ambil bagian nomor (4 digit setelah 'QLX')
        $lastNumber = (int) substr($lastQuoCode, 3, 4);

        // Tambahkan 1 ke nomor terakhir
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT); // Format menjadi 4 digit
    } else {
        // Jika tidak ada quo_code untuk hari ini, mulai dari 0001
        $newNumber = '0001';
    }

    // Gabungkan bagian prefix, nomor baru, dan tanggal
    $newQuoCode = "QLX" . $newNumber . $today;

    $response['quo_code'] = $newQuoCode;
    $response['success'] = true;
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
