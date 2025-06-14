<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$page_title = "Used Car Marketplace";
include 'includes/header.php';
?>

<main class="container">
    <section class="hero">
        <h1>Find Your Perfect Used Car</h1>
        <p>Browse thousands of quality used vehicles from trusted sellers</p>
        
        <form id="search-form" class="search-form" method="GET" action="cars/search.php">
            <div class="form-group">
                <input type="text" name="make" placeholder="Make (e.g. Toyota)">
            </div>
            <div class="form-group">
                <input type="text" name="model" placeholder="Model (e.g. Corolla)">
            </div>
            <div class="form-group">
                <select name="price_range">
                    <option value="">Price Range</option>
                    <option value="0-5000">Under $5,000</option>
                    <option value="5000-10000">$5,000 - $10,000</option>
                    <option value="10000-20000">$10,000 - $20,000</option>
                    <option value="20000-50000">$20,000 - $50,000</option>
                    <option value="50000-100000">$50,000 - $100,000</option>
                </select>
            </div>
            <button type="submit" class="btn">Search Cars</button>
        </form>
    </section>

    <section class="featured-cars">
        <h2>Featured Listings</h2>
        <div class="car-list">
            <?php
            $sql = "SELECT c.*, u.name as seller_name FROM cars c 
                    JOIN users u ON c.seller_id = u.id 
                    WHERE c.status = 'Available' 
                    ORDER BY c.created_at DESC LIMIT 6";
            $result = mysqli_query($conn, $sql);
            
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="car-card">';
                    echo '<a href="cars/view.php?id='.$row['id'].'">';
                    
                    // Get first image for the car
                    $image_sql = "SELECT image_path FROM car_images WHERE car_id = ".$row['id']." LIMIT 1";
                    $image_result = mysqli_query($conn, $image_sql);
                    $image = mysqli_fetch_assoc($image_result);
                    
                    if ($image) {
                        echo '<img src="uploads/'.$image['image_path'].'" alt="'.$row['make'].' '.$row['model'].'">';
                    } else {
                        echo '<img src="assets/images/default-car.jpg" alt="Default Car Image">';
                    }
                    
                    echo '<h3>'.$row['year'].' '.$row['make'].' '.$row['model'].'</h3>';
                    echo '<p class="car-price">$'.number_format($row['price'], 2).'</p>';
                    echo '<p>Mileage: '.number_format($row['mileage']).' miles</p>';
                    echo '<p>Location: '.htmlspecialchars($row['location']).'</p>';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>No cars available at the moment.</p>';
            }
            ?>
        </div>
        <div class="text-center">
            <a href="cars/search.php" class="btn">View All Listings</a>
        </div>
    </section>
</main>

<?php
include 'includes/footer.php';
mysqli_close($conn);
?>
