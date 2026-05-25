<?php
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
    $name = $_POST['name']; // Nama yang dipilih dari select
    $username = isset($_POST['username']) ? trim($_POST['username']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;
    $confirmPassword = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : null;

    // Validasi data
    if (!$name) {
        die('Error: Name is required.');
    }
    if (!$username) {
        die('Error: Username is required.');
    }
    if (!$password || $password !== $confirmPassword) {
        die('Error: Passwords do not match or are missing.');
    }

    // Proses file upload untuk foto profil dan tanda tangan
    $profilePhoto = null;
    $signature = null;

    // ID user berdasarkan nama
    $userId = getUserIdByName($conn, $name);

    if (!$userId) {
        die("Error: User not found.");
    }

    if (!empty($_FILES['photo_profile']['name'])) {
        $profilePhoto = uploadFile($_FILES['photo_profile'], 'pp', $userId, 'uploads/profile');
    }
    if (!empty($_FILES['signature']['name'])) {
        $signature = uploadFile($_FILES['signature'], 'sn_ttd', $userId, 'uploads/signature');
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Update data ke tabel `users`
    $sql = "UPDATE users SET 
                username = ?, 
                password = ?, 
                images = ?, 
                ttd = ?,
                updated_at = NOW()
            WHERE name = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Error: ' . $conn->error);
    }

    $stmt->bind_param(
        'sssss',
        $username,
        $hashedPassword,
        $profilePhoto,
        $signature,
        $name
    );

    if ($stmt->execute()) {
        // Setelah berhasil, arahkan ke login.php
        header('Location: login.php');
        exit(); // Hentikan eksekusi setelah pengalihan
    } else {
        echo "Error updating account: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

// Fungsi untuk mengunggah file
function uploadFile($file, $prefix, $userId, $targetDir)
{
    $randomCode = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 5);
    $fileName = $prefix . '_' . $randomCode . '_' . $userId . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $targetFilePath = $targetDir . '/' . $fileName;

    // Buat folder jika belum ada
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Validasi file
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png'];

    if (!in_array($fileType, $allowedTypes)) {
        die("Error: Only JPG, JPEG, and PNG files are allowed for $prefix.");
    }

    if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        die("Error: Failed to upload $prefix.");
    }

    return $fileName; // Kembalikan nama file untuk disimpan di database
}

// Fungsi untuk mendapatkan ID user berdasarkan nama
function getUserIdByName($conn, $name)
{
    $stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }

    return null;
}
?>
