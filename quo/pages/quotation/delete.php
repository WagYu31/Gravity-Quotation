<?php
// Tambahkan baris ini di paling atas untuk menampilkan pesan error jika ada
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../includes/db.php';

// Keamanan sesi
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Validasi input dari URL, sekarang kita menggunakan 'id'
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: ID tidak valid atau tidak ditemukan.");
}
$any_id_in_thread = (int)$_GET['id'];

$conn->begin_transaction();
try {
    // ===================================================================
    // LANGKAH A: Cari 'akar' (induk paling pertama) dari riwayat
    // ===================================================================
    $query_find_root = "
        WITH RECURSIVE Ancestors AS (
            SELECT id, parent_quotation_id FROM quotations WHERE id = ?
            UNION ALL
            SELECT q.id, q.parent_quotation_id FROM quotations q JOIN Ancestors a ON q.id = a.parent_quotation_id
        )
        SELECT id FROM Ancestors WHERE parent_quotation_id IS NULL;
    ";
    
    $stmt_find_root = $conn->prepare($query_find_root);
    $stmt_find_root->bind_param("i", $any_id_in_thread);
    $stmt_find_root->execute();
    $result_root = $stmt_find_root->get_result();
    $root_row = $result_root->fetch_assoc();
    $stmt_find_root->close();
    
    // Jika tidak ada root (kemungkinan data yatim), gunakan id itu sendiri sebagai root
    $root_id = $root_row ? (int)$root_row['id'] : $any_id_in_thread;

    // ===================================================================
    // LANGKAH B: Ambil semua ID dalam 'thread' berdasarkan 'akar' yang ditemukan
    // ===================================================================
    $query_get_all_ids = "
        WITH RECURSIVE FullThread AS (
            SELECT id FROM quotations WHERE id = ?
            UNION ALL
            SELECT q.id FROM quotations q JOIN FullThread ft ON q.parent_quotation_id = ft.id
        )
        SELECT id FROM FullThread;
    ";
    
    $stmt_get_all = $conn->prepare($query_get_all_ids);
    $stmt_get_all->bind_param("i", $root_id);
    $stmt_get_all->execute();
    $result_all = $stmt_get_all->get_result();
    
    $ids_to_delete = [];
    while ($row = $result_all->fetch_assoc()) {
        $ids_to_delete[] = $row['id'];
    }
    $stmt_get_all->close();
    
    // ===================================================================
    // LANGKAH C: Lakukan soft delete pada semua ID yang ditemukan
    // ===================================================================
    if (!empty($ids_to_delete)) {
        $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
        $types = str_repeat('i', count($ids_to_delete));
        $query_update = "UPDATE quotations SET deleted_at = NOW() WHERE id IN ($placeholders)";
        
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param($types, ...$ids_to_delete);
        $stmt_update->execute();
        $stmt_update->close();
    }
    
    // Jika semua berhasil
    $conn->commit();
    header('Location: ../../dashboard.php?status=deleted_success');
    exit();

} catch (Exception $e) {
    // Jika ada error di mana pun, batalkan semua
    $conn->rollback();
    die("Terjadi kesalahan: " . $e->getMessage());
}

$conn->close();
?>