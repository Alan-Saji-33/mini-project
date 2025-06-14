<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$page_title = "Search Cars";
$cars = [];

// Build search query
$where = [];
$params = [];

if (isset($_GET['make']) && !empty($_GET['make'])) {
    $make = sanitizeInput($_GET['make']);
    $where[] = "make LIKE '%$make%'";
}

if (isset($_GET['model']) && !empty($_GET['model'])) {
    $model = sanitizeInput($_GET['model']);
    $where[] = "model LIKE '%$model%'";
}

if (isset($_GET['price_range']) && !empty($_GET['price_range'])) {
    $price_range = explode('-', $_GET['price_range']);
    $min_price = (float)$price_range[0];
    $max_price = (float)$price_range[1];
    $where[] = "price BETWEEN $min_price AND $max_price";
}

if (isset($_GET['year']) && !empty($_GET['year'])) {
    $year = (int)$_GET['year'];
    $where[] = "year = $year";
}

if (isset($_GET['fuel_type']) && !empty($_GET['fuel_type'])) {
    $fuel_type = sanitizeInput($_GET['fuel_type']);
    $where[] = "fuel_type = '$fuel_type'";
}

if (isset($_GET['transmission']) && !empty($_GET['transmission'])) {
    $transmission = sanitizeInput($_GET['transmission']);
    $where[] = "transmission = '$transmission'";
}

// Base query
$sql = "SELECT c.*, u.name as seller_name FROM cars c 
        JOIN users u ON c.seller_id = u.id 
        WHERE c.status = 'Available'";

// Add conditions if any
if (!empty($where)) {
    $sql .= " AND " . implode(" AND ", $where);
}

// Order by
$sql .= " ORDER BY c.created_at DESC";

$result = mysqli_query($conn, $sql);
if ($result) {
    $cars = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

include '../includes/header.php';
?>

<div class="container">
    <h1>Search Cars</h1>
    
    <div class="search-filters">
        <form id="search-form" method="get" action="search.php">
            <div class="form-group">
                <input type="text" name="make" placeholder="Make" value="<?php echo isset($_GET['make']) ? htmlspecialchars($_GET['make']) : ''; ?>">
            </div>
            <div class="form-group">
                <input type="text" name="model" placeholder="Model" value="<?php echo isset($_GET['model']) ? htmlspecialchars($_GET['model']) : ''; ?>">
            </div>
            <div class="form-group">
                <select name="price_range">
                    <option value="">Price Range</option>
                    <option value="0-5000" <?php echo isset($_GET['price_range']) && $_GET['price_range'] == '0-5000' ? 'selected' : ''; ?>>Under $5,000</option>
                    <option value="5000-10000" <?php echo isset($_GET['price_range']) && $_GET['price_range'] == '5000-10000' ? 'selected' : ''; ?>>$5,000 - $10,000</option>
                    <option value="10000-20000" <?php echo isset($_GET['price_range']) && $_GET['price_range'] == '10000-20000' ? 'selected' : ''; ?>>$10,000 - $20,000</option>
                    <option value="20000-50000" <?php echo isset($_GET['price_range']) && $_GET['price_range'] == '20000-50000' ? 'selected' : ''; ?>>$20,000 - $50,000</option>
                    <option value="50000-100000" <?php echo isset($_GET['price_range']) && $_GET['price_range'] == '50000-100000' ? 'selected' : ''; ?>>$50,000 - $100,000</option>
                </select>
            </div>
            <div class="form-group">
                <select name="year">
                    <option value="">Year</option>
                    <?php 
                    $current_year = date('Y');
                    for ($year = $current_year; $year >= 1980; $year--) {
                        echo '<option value="' . $year . '" ' . (isset($_GET['year']) && $_GET['year'] == $year ? 'selected' : '') . '>' . $year . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <select name="fuel_type">
                    <option value="">Fuel Type</option>
                    <option value="Petrol" <?php echo isset($_GET['fuel_type']) && $_GET['fuel_type'] == 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                    <option value="Diesel" <?php echo isset($_GET['fuel_type']) && $_GET['fuel_type'] == 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                    <option value="Electric" <?php echo isset($_GET['fuel_type']) && $_GET['fuel_type'] == 'Electric' ? 'selected' : ''; ?>>Electric</option>
                    <option value="Hybrid" <?php echo isset($_GET['fuel_type']) && $_GET['fuel_type'] == 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                </select>
            </div>
            <div class="form-group">
                <select name="transmission">
                    <option value="">Transmission</option>
                    <option value="Automatic" <?php echo isset($_GET['transmission']) && $_GET['transmission'] == 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                    <option value="Manual" <?php echo isset($_GET['transmission']) && $_GET['transmission'] == 'Manual' ? 'selected' : ''; ?>>Manual</option>
                </select>
            </div>
            <button type="submit" class="btn">Search</button>
            <a href="search.php" class="btn">Reset</a>
        </form>
    </div>
    
    <div class="car-list">
        <?php if (!empty($cars)): ?>
            <?php foreach ($cars as $car): ?>
                <div class="car-card">
                    <a href="view.php?id=<?php echo $car['id']; ?>">
                        <?php
                        // Get first image for the car
                        $image_sql = "SELECT image_path FROM car_images WHERE car_id = " . $car['id'] . " LIMIT 1";
                        $image_result = mysqli_query($conn, $image_sql);
                        $image = mysqli_fetch_assoc($image_result);
                        
                        if ($image) {
                            echo '<img src="../uploads/' . $image['image_path'] . '" alt="' . $car['make'] . ' ' . $car['model'] . '">';
                        } else {
                            echo '<img src="../assets/images/default-car.jpg" alt="Default Car Image">';
                        }
                        ?>
                        
                        <div class="car-card-content">
                            <h3><?php echo $car['year'] . ' ' . $car['make'] . ' ' . $car['model']; ?></h3>
                            <p class="car-price">$<?php echo number_format($car['price'], 2); ?></p>
                            <div class="car-meta">
                                <span><?php echo number_format($car['mileage']); ?> miles</span>
                                <span><?php echo $car['location']; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No cars found matching your criteria.</p>
        <?php endif; ?>
    </div>
</div>

<?php
include '../includes/footer.php';
mysqli_close($conn);
?>
