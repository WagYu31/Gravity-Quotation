<?php
include 'conn.php';

$id = $_POST['id'];
$code = $_POST['code'];
$kategori = $_POST['kategori'];
$satuan = $_POST['satuan'];
$price = $_POST['price'];
$desc = $_POST['desc'];
$link_1 = $_POST['link_1'];
$name_link_1 = $_POST['name_link_1'];
$link_2 = $_POST['link_2'];
$name_link_2 = $_POST['name_link_2'];

// Upload gambar jika ada file baru
$image = $_FILES['image']['name'];
if ($image) {
    // Ekstrak ekstensi file
    $extension = pathinfo($image, PATHINFO_EXTENSION);
    
    // Buat nama file baru: kodeproduk_5koderandom_idProduk.ekstensi
    $randomCode = substr(md5(uniqid(rand(), true)), 0, 5); // Membuat 5 karakter random
    $newImageName = $code . '_' . $randomCode . '_' . $id . '.' . $extension;

    // Path file tujuan
    $imagePath = 'uploads/products/' . $newImageName;

    // Pindahkan file ke lokasi penyimpanan
    move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);

    // Query update dengan kolom image
    $query = $conn->prepare("UPDATE barang SET code = ?, kategori = ?, satuan = ?, price = ?, `desc` = ?, image = ?, name_link_1 = ?, link_1 = ?, name_link_2 = ?, link_2 = ?, updated_at = NOW() WHERE id = ?");
    $query->bind_param("sssissssssi", $code, $kategori, $satuan, $price, $desc, $newImageName, $name_link_1, $link_1, $name_link_2, $link_2, $id);
} else {
    // Query update tanpa kolom image
    $query = $conn->prepare("UPDATE barang SET code = ?, kategori = ?, satuan = ?, price = ?, `desc` = ?, name_link_1 = ?, link_1 = ?, name_link_2 = ?, link_2 = ?, updated_at = NOW() WHERE id = ?");
    $query->bind_param("sssisssssi", $code, $kategori, $satuan, $price, $desc, $name_link_1, $link_1, $name_link_2, $link_2, $id);
}

// Eksekusi query
if ($query->execute()) {
    header("Location: products.php?status=updated");
} else {
    echo "Error: " . $query->error;
}
?>
