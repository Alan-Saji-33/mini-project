<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeller()) {
    redirect('../auth/login.php');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('search.php');
}

$car_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$page_title = "Edit Listing";
$error = '';

// Verify car belongs to user
$sql = "SELECT * FROM cars WHERE id = $car_id AND seller_id = $user_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    redirect('search.php');
}

$car = mysqli_fetch_assoc($result);

// Get car images
$sql = "SELECT * FROM car_images WHERE car_id = $car_id";
$images_result = mysqli_query($conn, $sql);
$images = mysqli_fetch_all($images_result, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == '
