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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $make = sanitizeInput($_POST['make']);
    $model = sanitizeInput($_POST['model']);
    $year = (int)$_POST['year'];
    $price = (float)$_POST['price'];
    $mileage = (int)$_POST['mileage'];
    $fuel_type = sanitizeInput($_POST['fuel_type']);
    $transmission = sanitizeInput($_POST['transmission']);
    $location = sanitizeInput($_POST['location']);
    $description = sanitizeInput($_POST['description']);
    
    // Validate inputs
    if (empty($make) || empty($model) || empty($year) || empty($price) || empty($mileage)) {
        $error = 'Please fill all required fields';
    } elseif ($year < 1900 || $year > date('Y') + 1) {
        $error = 'Invalid year';
    } elseif ($price <= 0) {
        $error = 'Price must be greater than 0';
    } elseif ($mileage < 0) {
        $error = 'Mileage cannot be negative';
    } else {
        // Update car
        $sql = "UPDATE cars SET 
                make = '$make',
                model = '$model',
                year = '$year',
                price = '$price',
                mileage = '$mileage',
                fuel_type = '$fuel_type',
                transmission = '$fuel_type',
                location = '$location',
                description = '$description'
                WHERE id = $car_id";
        
        if (mysqli_query($conn, $sql)) {
            // Handle new image uploads
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    $file = [
                        'name' => $_FILES['images']['name'][$key],
                        'tmp_name' => $tmp_name,
                        'size' => $_FILES['images']['size'][$key],
                        'type' => $_FILES['images']['type'][$key]
                    ];
                    
                    $upload_result = uploadImage($file);
                    
                    if ($upload_result['success']) {
                        $image_path = $upload_result['filename'];
                        $sql = "INSERT INTO car_images (car_id, image_path) VALUES ('$car_id', '$image_path')";
                        mysqli_query($conn, $sql);
                    }
                }
            }
            
            // Handle image deletions
            if (!empty($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $image_id) {
                    $image_id = (int)$image_id;
                    // Get image path first
                    $sql = "SELECT image_path FROM car_images WHERE id = $image_id AND car_id = $car_id";
                    $result = mysqli_query($conn, $sql);
                    
                    if (mysqli_num_rows($result) == 1) {
                        $image = mysqli_fetch_assoc($result);
                        $file_path = '../uploads/' . $image['image_path'];
                        
                        // Delete from database
                        $sql = "DELETE FROM car_images WHERE id = $image_id";
                        mysqli_query($conn, $sql);
                        
                        // Delete file
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
            }
            
            $_SESSION['success_message'] = 'Listing updated successfully!';
            redirect('view.php?id=' . $car_id);
        } else {
            $error = 'Error: ' . mysqli_error($conn);
        }
    }
}

include '../includes/header.php';
?>

<div class="form-container">
    <h1>Edit Listing: <?php echo $car['year'].' '.$car['make'].' '.$car['model']; ?></h1>
    <?php displayError($error); ?>
    
    <form method="post" action="edit.php?id=<?php echo $car_id; ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label for="make">Make</label>
            <select id="make" name="make" required>
                <option value="">Select Make</option>
                <?php foreach (array_keys(getCarMakeModels()) as $make): ?>
                    <option value="<?php echo $make; ?>" <?php echo $car['make'] == $make ? 'selected' : ''; ?>>
                        <?php echo $make; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="model">Model</label>
            <select id="model" name="model" required>
                <option value="">Select Model</option>
                <?php 
                $models = getCarMakeModels()[$car['make']];
                foreach ($models as $model) {
                    echo '<option value="' . $model . '" ' . ($car['model'] == $model ? 'selected' : '') . '>' . $model . '</option>';
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="year">Year</label>
            <select id="year" name="year" required>
                <option value="">Select Year</option>
                <?php 
                $current_year = date('Y');
                for ($year = $current_year; $year >= 1900; $year--) {
                    echo '<option value="' . $year . '" ' . ($car['year'] == $year ? 'selected' : '') . '>' . $year . '</option>';
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="price">Price ($)</label>
            <input type="number" id="price" name="price" min="0" step="0.01" required 
                   value="<?php echo htmlspecialchars($car['price']); ?>">
        </div>
        
        <div class="form-group">
            <label for="mileage">Mileage</label>
            <input type="number" id="mileage" name="mileage" min="0" required 
                   value="<?php echo htmlspecialchars($car['mileage']); ?>">
        </div>
        
        <div class="form-group">
            <label for="fuel_type">Fuel Type</label>
            <select id="fuel_type" name="fuel_type" required>
                <option value="">Select Fuel Type</option>
                <option value="Petrol" <?php echo $car['fuel_type'] == 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                <option value="Diesel" <?php echo $car['fuel_type'] == 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                <option value="Electric" <?php echo $car['fuel_type'] == 'Electric' ? 'selected' : ''; ?>>Electric</option>
                <option value="Hybrid" <?php echo $car['fuel_type'] == 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="transmission">Transmission</label>
            <select id="transmission" name="transmission" required>
                <option value="">Select Transmission</option>
                <option value="Automatic" <?php echo $car['transmission'] == 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                <option value="Manual" <?php echo $car['transmission'] == 'Manual' ? 'selected' : ''; ?>>Manual</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" required 
                   value="<?php echo htmlspecialchars($car['location']); ?>">
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($car['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Current Images</label>
            <div class="current-images">
                <?php if (!empty($images)): ?>
                    <?php foreach ($images as $image): ?>
                        <div class="image-container">
                            <img src="../uploads/<?php echo $image['image_path']; ?>" alt="Car Image">
                            <label>
                                <input type="checkbox" name="delete_images[]" value="<?php echo $image['id']; ?>">
                                Delete
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No images uploaded yet.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label for="car-images">Add More Images (Max 5)</label>
            <input type="file" id="car-images" name="images[]" multiple accept="image/*">
            <div id="image-preview" style="margin-top: 10px;"></div>
        </div>
        
        <button type="submit" class="btn">Update Listing</button>
    </form>
</div>

<?php
include '../includes/footer.php';
mysqli_close($conn);
?>
