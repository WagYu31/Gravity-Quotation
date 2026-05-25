<?php
// Koneksi database
include 'conn.php'; // Pastikan file ini benar dan terkoneksi dengan database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $code = $_POST['code']; // Kode produk
    $linkThis = $_POST['linkThis'];
    // ... other variables ...

    // Check if the product code already exists
    $sql_check = "SELECT COUNT(*) FROM barang WHERE code = ? AND deleted_at IS NULL";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('s', $code);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        // Kode barang sudah ada, tampilkan alert dan redirect
        echo "<script>alert('Kode Barang $kategori sudah ada!'); window.location.href = 'add-product.php';</script>";
        exit;
    }

    // Ambil data dari form
    $kategori = $_POST['kategori'];
    $satuan = $_POST['satuan'];
    $price = $_POST['price'];
    $comment = !empty($_POST['comment']) ? $_POST['comment'] : null;
    $link_1 = $_POST['link_1'];
    $name_link_1 = $_POST['name_link_1'];
    $link_2 = $_POST['link_2'];
    $name_link_2 = $_POST['name_link_2'];

    // Handle file upload
    if (isset($_FILES['exampleFormControlFile1']) && $_FILES['exampleFormControlFile1']['error'] === 0) {
        $file = $_FILES['exampleFormControlFile1'];
        $fileTmp = $file['tmp_name'];
        $fileName = $file['name'];
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION); // Dapatkan ekstensi file

        // Ganti spasi dengan underscore pada kode produk
        $sanitizedCode = str_replace(' ', '_', $code);

        // Buat nama file baru
        $randomCode = substr(md5(uniqid(rand(), true)), 0, 5); // 5 karakter random
        $productId = rand(1, 10000); // Contoh ID produk, ini seharusnya dari database jika sudah ada
        $newFileName = "{$sanitizedCode}_{$randomCode}_{$productId}.{$fileExt}";

        // Tentukan lokasi penyimpanan
        $uploadDir = 'uploads/products/'; // Folder tujuan penyimpanan file
        $uploadPath = $uploadDir . $newFileName;

        // Pindahkan file ke folder tujuan
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            // File berhasil diupload
        } else {
            die('Gagal mengupload file.');
        }
    } else {
        $newFileName = null; // Tidak ada file yang diupload
    }

    $sql = "INSERT INTO barang (code, kategori, satuan, `desc`, image, name_link_1, link_1, name_link_2, link_2, price, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssss', $code, $kategori, $satuan, $comment, $newFileName, $name_link_1, $link_1, $name_link_2, $link_2, $price);

    if ($stmt->execute()) {
        header('Location: ' . $linkThis);
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    die('Invalid request method.');
}
?>