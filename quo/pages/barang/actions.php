<?php
include '../../includes/db.php';

// Fungsi untuk menangani upload gambar
function handle_image_upload($file_input_name) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
        $target_dir = "../../assets/uploads/barang/";
        // Buat nama file yang unik untuk menghindari penimpaan
        $filename = uniqid() . '_' . basename($_FILES[$file_input_name]["name"]);
        $target_file = $target_dir . $filename;
        
        // Pindahkan file yang di-upload ke direktori tujuan
        if (move_uploaded_file($_FILES[$file_input_name]["tmp_name"], $target_file)) {
            return $filename; // Return nama file jika berhasil
        }
    }
    return null; // Return null jika gagal atau tidak ada file
}

// Cek aksi dari form POST (Create & Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Aksi: Buat barang baru
    if ($_POST['action'] === 'create') {
        $image_filename = handle_image_upload('image');
        $stmt = $conn->prepare("INSERT INTO barang (code, image, `desc`, name_link_1, link_1, name_link_2, link_2, price, satuan, kategori) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssdss", $_POST['code'], $image_filename, $_POST['desc'], $_POST['name_link_1'], $_POST['link_1'], $_POST['name_link_2'], $_POST['link_2'], $_POST['price'], $_POST['satuan'], $_POST['kategori']);
        $stmt->execute();
        header('Location: index.php?status=created');
        exit();
    }

    // Aksi: Update data barang
    if ($_POST['action'] === 'update') {
        $id = (int)$_POST['id'];
        $image_filename = handle_image_upload('image');

        if ($image_filename) {
            // Jika ada gambar baru di-upload, update kolom gambar
            $stmt = $conn->prepare("UPDATE barang SET code=?, image=?, `desc`=?, name_link_1=?, link_1=?, name_link_2=?, link_2=?, price=?, satuan=?, kategori=? WHERE id=?");
            $stmt->bind_param("sssssssdssi", $_POST['code'], $image_filename, $_POST['desc'], $_POST['name_link_1'], $_POST['link_1'], $_POST['name_link_2'], $_POST['link_2'], $_POST['price'], $_POST['satuan'], $_POST['kategori'], $id);
        } else {
            // Jika tidak ada gambar baru, jangan update kolom gambar
            $stmt = $conn->prepare("UPDATE barang SET code=?, `desc`=?, name_link_1=?, link_1=?, name_link_2=?, link_2=?, price=?, satuan=?, kategori=? WHERE id=?");
            $stmt->bind_param("ssssssdssi", $_POST['code'], $_POST['desc'], $_POST['name_link_1'], $_POST['link_1'], $_POST['name_link_2'], $_POST['link_2'], $_POST['price'], $_POST['satuan'], $_POST['kategori'], $id);
        }
        $stmt->execute();
        header('Location: index.php?status=updated');
        exit();
    }
}

// Cek aksi dari URL GET (Delete)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    
    // Aksi: Hapus barang (Soft Delete)
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("UPDATE barang SET deleted_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header('Location: index.php?status=deleted');
        exit();
    }
}

// Jika tidak ada aksi yang cocok, kembali ke halaman utama
header('Location: index.php');
exit();
?>