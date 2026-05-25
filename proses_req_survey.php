<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mulai session
session_start();

// Ambil data dari form menggunakan $_POST
$service_type = isset($_POST['service_type']) ? $_POST['service_type'] : '';
$date_time = isset($_POST['date_time']) ? $_POST['date_time'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$namauser = isset($_POST['namauser']) ? $_POST['namauser'] : '';
$custname = isset($_POST['custname']) ? $_POST['custname'] : '';
$custaddress = isset($_POST['custaddress']) ? $_POST['custaddress'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';

// Format nomor telepon
$phone_number = preg_replace('/\D/', '', $phone); // Hapus semua karakter selain angka
if (strpos($phone_number, '08') !== 0) {
    if (strpos($phone_number, '8') === 0) {
        $phone_number = '0' . $phone_number;
    } elseif (strpos($phone_number, '62') === 0) {
        $phone_number = '0' . substr($phone_number, 2);
    } elseif (strpos($phone_number, '+62') === 0) {
        $phone_number = '0' . substr($phone_number, 3);
    }
}

// Koneksi ke database jadwal
$jadwal_host = 'localhost';
$jadwal_user = 'u836263092_rootJadwal';
$jadwal_password = 'Eddie@1819';
$jadwal_database = 'u836263092_jadwal';

$jadwal_conn = mysqli_connect($jadwal_host, $jadwal_user, $jadwal_password, $jadwal_database);
if (!$jadwal_conn) {
    die("Koneksi ke database jadwal gagal: " . mysqli_connect_error());
}

// Query untuk mengambil data customer berdasarkan nomor telepon
$query = "SELECT id FROM customer WHERE telp = '$phone_number'";
$result = mysqli_query($jadwal_conn, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($jadwal_conn));
}

// Cek apakah data customer ditemukan
if (mysqli_num_rows($result) > 0) {
    // Jika data customer sudah ada, ambil id-nya
    $customer_data = mysqli_fetch_assoc($result);
    $customer_id = $customer_data['id'];
} else {
    // Jika data customer tidak ada, insert data baru
    $insert_customer_query = "INSERT INTO customer (nama, telp, alamat) VALUES ('$custname', '$phone_number', '$custaddress')";
    if (mysqli_query($jadwal_conn, $insert_customer_query)) {
        $customer_id = mysqli_insert_id($jadwal_conn); // Ambil id customer yang baru diinsert
    } else {
        die("Gagal insert data customer: " . mysqli_error($jadwal_conn));
    }
}

$kode = 'qqq' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 6);

// Set timezone ke Jakarta
date_default_timezone_set('Asia/Jakarta');
$now = date('Y-m-d H:i:s');

// Query untuk insert data ke tabel kegiatan
$insert_kegiatan_query = "
    INSERT INTO kegiatan (
        customer_id,
        kegiatan,
        jadwal,
        keterangan,
        status,
        request,
        kode,
        created_at,
        updated_at
    ) VALUES (
        '$customer_id',
        '$service_type',
        '$date_time',
        '$description',
        'waiting',
        '$namauser',
        '$kode',
        '$now',
        '$now'
    )
";

if (mysqli_query($jadwal_conn, $insert_kegiatan_query)) {

    // Redirect ke halaman simpan_kode.php
    header('Location: get_activities.php');
    exit(); // Pastikan tidak ada output sebelum redirect
} else {
    echo "Gagal: " . mysqli_error($jadwal_conn);
}

// Tutup koneksi database
mysqli_close($jadwal_conn);
?>