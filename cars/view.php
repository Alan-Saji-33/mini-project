<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('search.php');
}

$car_id = (int)$_GET['id'];
$page_title = "Car Details";

// Get car details
$sql = "SELECT c.*, u.name as seller_name, u.phone as seller_phone, u.email as seller_email 
        FROM cars c 
        JOIN users u ON c.seller_id = u.id 
        WHERE c.id = $car_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    redirect('search.php');
}

$car = mysqli_fetch_assoc($result);

// Get car images
$sql = "SELECT * FROM car_images WHERE car_id = $car_id";
$images_result = mysqli_query($conn, $sql);
$images = mysqli_fetch_all($images_result, MYSQLI_ASSOC);

// Check if car is in user's wishlist
$in_wishlist = false;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT id FROM wishlist WHERE user_id = $user_id AND car_id = $car_id";
    $result = mysqli_query($conn, $sql);
    $in_wishlist = mysqli_num_rows($result) > 0;
}

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn() && isset($_POST['message'])) {
    $message = sanitizeInput($_POST['message']);
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $car['seller_id'];
    
    if (!empty($message)) {
        $sql = "INSERT INTO messages (sender_id, receiver_id, car_id, message)
                VALUES ('$sender_id', '$receiver_id', '$car_id', '$message')";
        mysqli_query($conn, $sql);
        $_SESSION['success_message'] = 'Message sent successfully!';
    }
}

include '../includes/header.php';
?>

<div class="car-details">
    <div class="car-gallery">
        <?php if (!empty($images)): ?>
            <?php foreach ($images as $image): ?>
                <img src="../uploads/<?php echo $image['image_path']; ?>" alt="<?php echo $car['make'] . ' ' . $car['model']; ?>">
            <?php endforeach; ?>
        <?php else: ?>
            <img src="../assets/images/default-car.jpg" alt="Default Car Image">
        <?php endif; ?>
    </div>
    
    <div class="car-info">
        <h1><?php echo $car['year'] . ' ' . $car['make'] . ' ' . $car['model']; ?></h1>
        <div class="price">$<?php echo number_format($car['price'], 2); ?></div>
        
        <div class="car-specs">
            <div>
                <strong>Mileage:</strong>
                <span><?php echo number_format($car['mileage']); ?> miles</span>
            </div>
            <div>
                <strong>Fuel Type:</strong>
                <span><?php echo $car['fuel_type']; ?></span>
            </div>
            <div>
                <strong>Transmission:</strong>
                <span><?php echo $car['transmission']; ?></span>
            </div>
            <div>
                <strong>Location:</strong>
                <span><?php echo htmlspecialchars($car['location']); ?></span>
            </div>
            <div>
                <strong>Status:</strong>
                <span><?php echo $car['status']; ?></span>
            </div>
            <div>
                <strong>Seller:</strong>
                <span><?php echo htmlspecialchars($car['seller_name']); ?></span>
            </div>
        </div>
        
        <div class="description">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
        </div>
        
        <?php if (isLoggedIn()): ?>
            <div class="actions">
                <?php if ($_SESSION['user_id'] != $car['seller_id']): ?>
                    <?php if ($in_wishlist): ?>
                        <a href="../user/wishlist.php?action=remove&car_id=<?php echo $car_id; ?>" class="btn">Remove from Wishlist</a>
                    <?php else: ?>
                        <a href="../user/wishlist.php?action=add&car_id=<?php echo $car_id; ?>" class="btn">Add to Wishlist</a>
                    <?php endif; ?>
                    
                    <h3>Contact Seller</h3>
                    <form method="post" action="view.php?id=<?php echo $car_id; ?>">
                        <div class="form-group">
                            <textarea name="message" placeholder="Your message to the seller" required></textarea>
                        </div>
                        <button type="submit" class="btn">Send Message</button>
                    </form>
                <?php else: ?>
                    <a href="edit.php?id=<?php echo $car_id; ?>" class="btn">Edit Listing</a>
                    <?php if ($car['status'] == 'Available'): ?>
                        <a href="mark_sold.php?id=<?php echo $car_id; ?>" class="btn">Mark as Sold</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p><a href="../auth/login.php">Login</a> to contact seller or save to wishlist</p>
        <?php endif; ?>
    </div>
</div>

<?php
include '../includes/footer.php';
mysqli_close($conn);
?>
