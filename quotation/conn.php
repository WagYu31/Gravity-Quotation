<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "quo";

// $host = "localhost";
// $user = "u836263092_rootQuo";
// $password = "Eddie@18";
// $database = "u836263092_quo";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection to the database failed. " . $conn->connect_error);
}


// Set timezone ke Jakarta
date_default_timezone_set('Asia/Jakarta');

// Waktu sekarang
$current_time = date('Y-m-d H:i:s');

include "session.php";
?>