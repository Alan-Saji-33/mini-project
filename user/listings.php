<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeller()) {
    redirect('../auth/login.php');
}

$page_title = "My Listings";
$user_id = $_SESSION['user_id'];

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $car_id = (int)$_GET['delete'];
    
    // Verify car belongs to user
    $sql = "SELECT id FROM cars WHERE id = $car_id AND seller_id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        // Delete car (cascade delete will handle images)
        $sql = "DELETE FROM cars WHERE id = $car_id";
        mysqli_query($conn, $sql);
        $_SESSION['success_message'] = 'Listing deleted successfully';
        redirect('listings.php');
    }
}

// Get user's listings
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM car_images WHERE car_id = c.id) as image_count,
        (SELECT COUNT(*) FROM messages WHERE car_id = c.id) as message_count
        FROM cars c
        WHERE c.seller_id = $user_id
        ORDER BY c.created_at DESC";
$result = mysqli_query($conn, $sql);
$listings = mysqli_fetch_all($result, MYSQLI_ASSOC);

include '../includes/header.php';
?>

<div class="dashboard-grid">
    <div class="dashboard-sidebar">
        <h3>My Account</h3>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li class="active"><a href="listings.php">My Listings</a></li>
            <li><a href="wishlist.php">Wishlist</a></li>
            <li><a href="messages.php">Messages</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </div>
    
    <div class="dashboard-content">
        <h1>My Listings</h1>
        <a href="../cars/add.php" class="btn" style="margin-bottom: 20px;">Add New Listing</a>
        
        <?php if (!empty($listings)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Car</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Images</th>
                        <th>Messages</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $listing): ?>
                        <tr>
                            <td>
                                <a href="../cars/view.php?id=<?php echo $listing['id']; ?>">
                                    <?php echo $listing['year'].' '.$listing['make'].' '.$listing['model']; ?>
                                </a>
                            </td>
                            <td>$<?php echo number_format($listing['price'], 2); ?></td>
                            <td><?php echo $listing['status']; ?></td>
                            <td><?php echo $listing['image_count']; ?></td>
                            <td><?php echo $listing['message_count']; ?></td>
                            <td>
                                <a href="../cars/edit.php?id=<?php echo $listing['id']; ?>" class="btn">Edit</a>
                                <a href="listings.php?delete=<?php echo $listing['id']; ?>" class="btn delete-btn">Delete</a>
                                <?php if ($listing['status'] == 'Available'): ?>
                                    <a href="../cars/mark_sold.php?id=<?php echo $listing['id']; ?>" class="btn">Mark Sold</a>
                                <?php endif
