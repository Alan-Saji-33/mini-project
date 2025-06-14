<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$page_title = "My Messages";
$user_id = $_SESSION['user_id'];

// Get all messages for the user
$sql = "SELECT m.*, u.name as sender_name, c.make, c.model, c.year 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        JOIN cars c ON m.car_id = c.id
        WHERE m.receiver_id = $user_id
        ORDER BY m.timestamp DESC";
$result = mysqli_query($conn, $sql);
$messages = mysqli_fetch_all($result, MYSQLI_ASSOC);

include '../includes/header.php';
?>

<div class="dashboard-grid">
    <div class="dashboard-sidebar">
        <h3>My Account</h3>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="profile.php">Profile</a></li>
            <?php if (isSeller()): ?>
                <li><a href="listings.php">My Listings</a></li>
            <?php endif; ?>
            <li><a href="wishlist.php">Wishlist</a></li>
            <li class="active"><a href="messages.php">Messages</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </div>
    
    <div class="dashboard-content">
        <h1>My Messages</h1>
        
        <?php if (!empty($messages)): ?>
            <div class="message-list">
                <?php foreach ($messages as $message): ?>
                    <div class="message-card">
                        <div class="message-header">
                            <h3>Regarding: <?php echo $message['year'].' '.$message['make'].' '.$message['model']; ?></h3>
                            <small><?php echo date('M j, Y g:i a', strtotime($message['timestamp'])); ?></small>
                        </div>
                        <div class="message-body">
                            <p><strong>From:</strong> <?php echo htmlspecialchars($message['sender_name']); ?></p>
                            <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                        </div>
                        <div class="message-actions">
                            <a href="../cars/view.php?id=<?php echo $message['car_id']; ?>" class="btn">View Car</a>
                            <a href="reply.php?car_id=<?php echo $message['car_id']; ?>&sender_id=<?php echo $message['sender_id']; ?>" class="btn">Reply</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>You have no messages yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php
include '../includes/footer.php';
mysqli_close($conn);
?>
