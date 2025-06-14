<?php
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$page_title = "Admin Dashboard";

// Get counts for dashboard
$sql = "SELECT COUNT(*) as count FROM users";
$result = mysqli_query($conn, $sql);
$users_count = mysqli_fetch_assoc($result)['count'];

$sql = "SELECT COUNT(*) as count FROM cars";
$result = mysqli_query($conn, $sql);
$cars_count = mysqli_fetch_assoc($result)['count'];

$sql = "SELECT COUNT(*) as count FROM cars WHERE status = 'Sold'";
$result = mysqli_query($conn, $sql);
$sold_cars_count = mysqli_fetch_assoc($result)['count'];

include '../../includes/header.php';
?>

<div class="dashboard-grid">
    <div class="dashboard-sidebar">
        <h3>Admin Panel</h3>
        <ul>
            <li class="active"><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_listings.php">Manage Listings</a></li>
            <li><a href="../../auth/logout.php">Logout</a></li>
        </ul>
    </div>
    
    <div class="dashboard-content">
        <h1>Admin Dashboard</h1>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $users_count; ?></p>
                <a href="manage_users.php" class="btn">View Users</a>
            </div>
            
            <div class="stat-card">
                <h3>Total Listings</h3>
                <p><?php echo $cars_count; ?></p>
                <a href="manage_listings.php" class="btn">View Listings</a>
            </div>
            
            <div class="stat-card">
                <h3>Sold Cars</h3>
                <p><?php echo $sold_cars_count; ?></p>
            </div>
        </div>
        
        <div class="recent-activity">
            <h2>Recent Activity</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT u.name, c.make, c.model, c.created_at 
                            FROM cars c 
                            JOIN users u ON c.seller_id = u.id 
                            ORDER BY c.created_at DESC LIMIT 5";
                    $result = mysqli_query($conn, $sql);
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . date('M j, Y', strtotime($row['created_at'])) . '</td>';
                        echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                        echo '<td>Added new listing: ' . htmlspecialchars($row['make'] . ' ' . htmlspecialchars($row['model']) . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include '../../includes/footer.php';
mysqli_close($conn);
?>
