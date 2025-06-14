<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeller()) {
    redirect('../auth/login.php');
}

$page_title = "Add New Car";
$error = '';
$success = '';

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
    $seller_id = $_SESSION['user_id'];
    
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
        // Insert car
        $sql = "INSERT INTO cars (seller_id, make, model, year, price, mileage, fuel_type, transmission, location, description)
                VALUES ('$seller_id', '$make', '$model', '$year', '$price', '$mileage', '$fuel_type', '$transmission', '$location', '$description')";
        
        if (mysqli_query($conn, $sql)) {
            $car_id = mysqli_insert_id($conn);
            $upload_success = true;
            
            // Handle image uploads
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
                    } else {
                        $upload_success = false;
                        $error = $upload_result['error'];
                        break;
                    }
                }
            }
            
            if ($upload_success) {
                $_SESSION['success_message'] = 'Car added successfully!';
                redirect('view.php?id=' . $car_id);
            }
        } else {
            $error = 'Error: ' . mysqli_error($conn);
        }
    }
}

include '../includes/header.php';
?>

<div class="form-container">
    <h1>Add New Car</h1>
    <?php displayError($error); ?>
    <?php displaySuccess($success); ?>
    
    <form method="post" action="add.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="make">Make</label>
            <select id="make" name="make" required>
                <option value="">Select Make</option>
                <?php foreach (array_keys(getCarMakeModels()) as $make): ?>
                    <option value="<?php echo $make; ?>" <?php echo isset($_POST['make']) && $_POST['make'] == $make ? 'selected' : ''; ?>>
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
                if (isset($_POST['make'])) {
                    $models = getCarMakeModels()[$_POST['make']];
                    foreach ($models as $model) {
                        echo '<option value="' . $model . '" ' . (isset($_POST['model']) && $_POST['model'] == $model ? 'selected' : '') . '>' . $model . '</option>';
                    }
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
                    echo '<option value="' . $year . '" ' . (isset($_POST['year']) && $_POST['year'] == $year ? 'selected' : '') . '>' . $year . '</option>';
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="price">Price ($)</label>
            <input type="number" id="price" name="price" min="0" step="0.01" required 
                   value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="mileage">Mileage</label>
            <input type="number" id="mileage" name="mileage" min="0" required 
                   value="<?php echo isset($_POST['mileage']) ? htmlspecialchars($_POST['mileage']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="fuel_type">Fuel Type</label>
            <select id="fuel_type" name="fuel_type" required>
                <option value="">Select Fuel Type</option>
                <option value="Petrol" <?php echo isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                <option value="Diesel" <?php echo isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                <option value="Electric" <?php echo isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Electric' ? 'selected' : ''; ?>>Electric</option>
                <option value="Hybrid" <?php echo isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="transmission">Transmission</label>
            <select id="transmission" name="transmission" required>
                <option value="">Select Transmission</option>
                <option value="Automatic" <?php echo isset($_POST['transmission']) && $_POST['transmission'] == 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                <option value="Manual" <?php echo isset($_POST['transmission']) && $_POST['transmission'] == 'Manual' ? 'selected' : ''; ?>>Manual</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" required 
                   value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="car-images">Images (Max 5)</label>
            <input type="file" id="car-images" name="images[]" multiple accept="image/*">
            <div id="image-preview" style="margin-top: 10px;"></div>
        </div>
        
        <button type="submit" class="btn">Add Car</button>
    </form>
</div>

<?php
include '../includes/footer.php';
mysqli_close($conn);
?>
