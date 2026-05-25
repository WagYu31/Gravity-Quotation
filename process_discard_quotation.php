<?php
session_start();
require 'conn.php'; // Sesuaikan dengan file koneksi database Anda

$data = json_decode(file_get_contents('php://input'), true);
$quoId = $data['quoId'];
$quoNum = $data['quoNum'];

$response = ['success' => false, 'message' => ''];

try {
    // Cek jika $quoId dan $quoNum null
    if (empty($quoId) && empty($quoNum)) {
        $response['message'] = 'Quotation ID dan Quotation Number tidak ditemukan.';
        echo json_encode($response);
        exit;
    }

    // Jika hanya $quoId null, cari ID berdasarkan $quoNum
    if (empty($quoId) && !empty($quoNum)) {
        $stmt = $conn->prepare("SELECT id FROM quo WHERE quo_num = ?");
        $stmt->execute([$quoNum]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $quoId = $result['id'];
        } else {
            $response['message'] = 'Quotation Number tidak ditemukan.';
            echo json_encode($response);
            exit;
        }
    }

    // Hapus data dari tabel quo_detail
    $stmt = $conn->prepare("DELETE FROM quo_detail WHERE quo_id = ?");
    $stmt->execute([$quoId]);

    // Hapus data dari tabel quo
    $stmt = $conn->prepare("DELETE FROM quo WHERE id = ? AND quo_num = ?");
    $stmt->execute([$quoId, $quoNum]);

    $response['success'] = true;
    $response['message'] = 'Quotation berhasil dihapus.';
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>