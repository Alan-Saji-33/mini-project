<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isSeller() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'seller';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

function uploadImage($file, $upload_dir = 'uploads/') {
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $target_file = $upload_dir . basename($file['name']);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['success' => false, 'error' => 'File is not an image'];
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5000000) {
        return ['success' => false, 'error' => 'File is too large (max 5MB)'];
    }
    
    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        return ['success' => false, 'error' => 'Only JPG, JPEG, PNG & GIF files are allowed'];
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'error' => 'Error uploading file'];
    }
}

function displayError($error) {
    if (!empty($error)) {
        echo '<div class="alert alert-danger">' . $error . '</div>';
    }
}

function displaySuccess($message) {
    if (!empty($message)) {
        echo '<div class="alert alert-success">' . $message . '</div>';
    }
}

function getCarMakeModels() {
    return [
        'Toyota' => ['Corolla', 'Camry', 'Rav4', 'Highlander', 'Prius'],
        'Honda' => ['Civic', 'Accord', 'CR-V', 'Pilot', 'Odyssey'],
        'Ford' => ['F-150', 'Escape', 'Explorer', 'Mustang', 'Focus'],
        'Chevrolet' => ['Silverado', 'Equinox', 'Tahoe', 'Camaro', 'Malibu'],
        'Nissan' => ['Altima', 'Rogue', 'Sentra', 'Pathfinder', 'Maxima']
    ];
}
?>
