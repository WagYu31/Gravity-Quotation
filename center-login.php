<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "quo";
$conn = new mysqli($host, $username, $password, $database);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Jakarta');

// Proses form login jika tombol login ditekan
$nama = $_GET["nama"];
$login_err = "";

$getNama = "SELECT * FROM users WHERE name = '$nama'";
$resNama = mysqli_query($conn, $getNama);
$rowNama = mysqli_fetch_array($resNama);
$username = $rowNama['username'];
$password = $rowNama['password'];

// Query untuk mencari pengguna dengan username yang cocok
$sql = "SELECT id, name, alias, email, images, ttd, username, role, password FROM users WHERE username = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $param_username);
    $param_username = $username;
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $user_id, $name, $alias, $email, $images, $ttd, $username, $role, $stored_password);
            if (mysqli_stmt_fetch($stmt)) {
                if ($password === $stored_password) {
                    session_start();
                    $_SESSION['user_id'] = $user_id;       // ID pengguna
                    $_SESSION['user_name'] = $name;   // Nama pengguna
                    $_SESSION['user_alias'] = $alias; // Alias pengguna
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_images'] = $images;
                    $_SESSION['user_ttd'] = $ttd;
                    $_SESSION['user_role'] = $role;

                    
                    // Redirect ke halaman listQuotation.php
                    header('Location: quo/dashboard.php');
                    exit();
                } else {
                    // Jika password salah
                    $_SESSION['error'] = 'Password salah.';
                    header('Location: quo/login.php');
                    exit();
                }
            }
        } else {
            // Jika password salah
            $_SESSION['error'] = 'Password salah.';
            header('Location: quo/login.php');
            exit();
        }
    } else {
            // Jika password salah
            $_SESSION['error'] = 'Password salah.';
            header('Location: quo/login.php');
            exit();
    }
    mysqli_stmt_close($stmt);
}

// Tutup koneksi database
mysqli_close($conn);

// Jika terjadi kesalahan saat login, alihkan ke halaman login.php dengan pesan kesalahan
if ($login_err) {
    // Login gagal, alihkan ke halaman login.php dengan pesan kesalahan
    header("location: login.php");
    exit();
}
