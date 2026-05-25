<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'login') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Ambil data user dari DB
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE username = ? AND deleted_at IS NULL");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Login sukses, simpan data ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                header('Location: dashboard.php');
                exit();
            }
        }
        // Jika login gagal
        header('Location: login.php?error=Username atau password salah');
        exit();
    }

    if ($action === 'signup') {
        // Logika untuk pendaftaran
        $name = $_POST['name'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];

        if ($password !== $password_confirm) {
            header('Location: signup.php?error=Konfirmasi password tidak cocok');
            exit();
        }

        // Hash password sebelum disimpan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, username, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $username, $hashed_password);
        
        if ($stmt->execute()) {
            header('Location: login.php?success=Pendaftaran berhasil, silakan login');
            exit();
        } else {
            header('Location: signup.php?error=Username atau email sudah terdaftar');
            exit();
        }
    }
}
?>