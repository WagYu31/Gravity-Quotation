<?php
include "conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_file'])) {
    $type = $_POST['type']; // Jenis file (so, sj, invoice)
    $quoId = $_POST['quo_id'];

    // Direktori berdasarkan tipe file
    $directory = "uploads/progress/$type/";

    if (!empty($_FILES['file']['name'])) {
        // Generate nama file baru
        $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $randomCode = substr(md5(mt_rand()), 0, 7); // 7 karakter random
        $newFileName = "{$type}_{$randomCode}_{$quoId}.{$extension}";

        // Pindahkan file ke direktori
        if (move_uploaded_file($_FILES['file']['tmp_name'], $directory . $newFileName)) {
            // Proses insert atau update database berdasarkan tipe file
            if ($type === 'so') {
                $query = "INSERT INTO progress (quo_id, so) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("is", $quoId, $newFileName);
            } else {
                $query = "UPDATE progress SET $type = ? WHERE quo_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $newFileName, $quoId);
            }

            if ($stmt->execute()) {
                echo "<script>alert('File uploaded successfully!'); window.location = 'quoProgress.php';</script>";
            } else {
                echo "<script>alert('Failed to update database!');</script>";
            }
        } else {
            echo "<script>alert('Failed to upload file!');</script>";
        }
    } else {
        echo "<script>alert('No file selected!');</script>";
    }
}
?>