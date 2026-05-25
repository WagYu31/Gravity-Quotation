<?php

include "conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    // Ambil data input
    $name = $_POST['name'];
    $alias = $_POST['alias'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Ambil user_id dari session atau sumber lain
    $user_id = $_SESSION['user_id']; // Pastikan $user_id diambil sesuai sistem Anda

    // Ambil data sebelumnya dari database
    $query = "SELECT images, ttd FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Upload photo profile
    $photo_profile = $user['images']; // Gunakan nilai sebelumnya jika tidak ada file baru
    if (!empty($_FILES['photo_profile']['name'])) {
        $extension = pathinfo($_FILES['photo_profile']['name'], PATHINFO_EXTENSION);
        $random_code = substr(md5(uniqid(rand(), true)), 0, 5);
        $photo_profile = "pp_{$random_code}_{$user_id}." . $extension;
        move_uploaded_file($_FILES['photo_profile']['tmp_name'], "uploads/profile/$photo_profile");
    }

    // Upload signature
    $signature = $user['ttd']; // Gunakan nilai sebelumnya jika tidak ada file baru
    if (!empty($_FILES['signature']['name'])) {
        $extension = pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION);
        $random_code = substr(md5(uniqid(rand(), true)), 0, 5);
        $signature = "snttd_{$random_code}_{$user_id}." . $extension;
        move_uploaded_file($_FILES['signature']['tmp_name'], "uploads/signature/$signature");
    }

    // Query update
    $query = "UPDATE users SET name = ?, alias = ?, username = ?, email = ?, telp = ?, images = ?, ttd = ?" . 
             ($password ? ", password = ?" : "") . " WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($password) {
        $stmt->bind_param("ssssssssi", $name, $alias, $username, $email, $phone, $photo_profile, $signature, $password, $user_id);
    } else {
        $stmt->bind_param("sssssssi", $name, $alias, $username, $email, $phone, $photo_profile, $signature, $user_id);
    }

    // Eksekusi query
    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location = 'profile.php';</script>";
    } else {
        echo "<script>alert('Failed to update profile!');</script>";
    }
}
?>
