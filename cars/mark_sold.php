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

// Verify car belongs to user
$sql = "SELECT id FROM cars WHERE id = $car_id AND seller_id = $user_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {
    // Mark car as sold
    $sql = "UPDATE cars SET status = 'Sold' WHERE id = $car_id";
    mysqli_query($conn, $sql);
    $_SESSION['success_message'] = 'Car marked as sold successfully';
}

redirect('view.php?id=' . $car_id);
?>
