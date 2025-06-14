<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$page_title = "Dashboard";
$user_id = $_SESSION['user_id'];

// Get user details
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Get count of user's listings if seller
$listings_count = 0;
if (isSeller()) {
    $sql = "SELECT COUNT(*) as count FROM cars WHERE seller_id = $user_id";
    $result = mysqli_query($conn, $sql);
    $listings_count = mysqli_fetch_assoc($result)['count'];
}

// Get count of wishlist items
$wishlist_count = 0;
$sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
$wishlist_count = mysqli_fetch_assoc($result)['count'];

// Get count of messages
$messages_count = 0;
$sql = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = $user_id";
$result = mysqli_query($conn, $sql);
$messages_count = mysqli_fetch_assoc($result)['count'];

include '../includes/header.php';
?>

<div class="dashboard-grid">
    <div class="dashboard-sidebar">
        <h3>My Account</h3>
        <ul>
            <li class="active"><a href="dashboard.php">Dashboard</a></li>
            <li><a href="profile.php">Profile</a></li>
            <?php if (isSeller()): ?>
                <li><a href="listings.php">My Listings</a></li>
            <?php endif; ?>
            <li><a href="wishlist.php">Wishlist (<?php echo $wishlist_count; ?>)</a></li>
            <li><a href="messages.php">Messages (<?php echo $messages_count; ?>)</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </div>
    
    <div class="dashboard-content">
        <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
        
        <div class="stats">
            <?php if (isSeller()): ?>
                <div class="stat-card">
                    <h3>My Listings</h3>
                    <p><?php echo $listings_count; ?></p>
                    <a href="listings.php" class="btn">View Listings</a>
                </div>
            <?php endif; ?>
            
            <div class="stat-card">
                <h3>Wishlist</h3>
                <p><?php echo $wishlist_count; ?></p>
                <a href="wishlist.php" class="btn">View Wishlist</a>
            </div>
            
            <div class="stat-card">
                <h3>Messages</h3>
                <p><?php echo $messages_count; ?></p>
                <a href="messages.php" class="btn">View Messages</a>
            </div>
        </div>
        
        <?php if (isSeller()): ?>
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <a href="../cars/add.php" class="btn">Add New Listing</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include '../includes/footer.php';
mysqli_close($conn);
?>
