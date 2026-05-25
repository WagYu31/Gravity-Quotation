<?php
// Start session
session_start();

// Check if user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert'] = "Please log in first.";
    header('Location: quo/login.php');
    exit();
}
// Ambil data dari sesi
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_alias = $_SESSION['user_alias'];
$user_email = $_SESSION['user_email'];
$user_images = $_SESSION['user_images'];
$user_ttd = $_SESSION['user_ttd'];
$user_role = $_SESSION['user_role'];
?>
