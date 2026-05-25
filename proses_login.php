<?php
session_start(); // Memulai sesi
// Koneksi ke database
$host = "localhost";
$username = "root";
$password = "";
$database = "quo";

// $host = "localhost";
// $username = "u836263092_rootQuo";
// $password = "Eddie@18";
// $database = "u836263092_quo";

$conn = new mysqli($host, $username, $password, $database);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $usernameOrEmail = isset($_POST['username']) ? trim($_POST['username']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;

    // Validasi input
    if (!$usernameOrEmail || !$password) {
        $_SESSION['error'] = 'Username/Email dan Password harus diisi.';
        header('Location: login.php');
        exit();
    }

    // Query untuk mendapatkan data pengguna
    $sql = "SELECT id, name, alias, email, images, ttd, username, role, password 
            FROM users 
            WHERE (username = ? OR email = ?) AND deleted_at IS NULL";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Error: ' . $conn->error);
    }

    $stmt->bind_param('ss', $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Cek jika email, images, atau ttd kosong/null
        $user['email'] = $user['email'] ?: 'Belum input';
        $user['images'] = $user['images'] ?: 'user-no-image.jpg';
        $user['ttd'] = $user['ttd'] ?: 'ttd.png';

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Simpan data pengguna ke sesi
            $_SESSION['user_id'] = $user['id'];       // ID pengguna
            $_SESSION['user_name'] = $user['name'];   // Nama pengguna
            $_SESSION['user_alias'] = $user['alias']; // Alias pengguna
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_images'] = $user['images'];
            $_SESSION['user_ttd'] = $user['ttd'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect ke halaman listQuotation.php
            header('Location: listQuotation.php');
            exit();
        } else {
            // Jika password salah
            $_SESSION['error'] = 'Password salah.';
            header('Location: login.php');
            exit();
        }
    } else {
        // Jika username/email tidak ditemukan
        $_SESSION['error'] = 'Username atau Email tidak ditemukan.';
        header('Location: login.php');
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Jika bukan metode POST, redirect ke login
    header('Location: login.php');
    exit();
}
?>
