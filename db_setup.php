
<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'used_cars_db');

// Create connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Select database
mysqli_select_db($conn, DB_NAME);

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        role ENUM('buyer','seller','admin') DEFAULT 'buyer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS cars (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        seller_id INT(11) NOT NULL,
        make VARCHAR(50) NOT NULL,
        model VARCHAR(50) NOT NULL,
        year INT(4) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        mileage INT(11) NOT NULL,
        fuel_type ENUM('Petrol','Diesel','Electric','Hybrid') NOT NULL,
        transmission ENUM('Automatic','Manual') NOT NULL,
        location VARCHAR(100) NOT NULL,
        description TEXT,
        status ENUM('Available','Sold') DEFAULT 'Available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES users(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS car_images (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        car_id INT(11) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS messages (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        sender_id INT(11) NOT NULL,
        receiver_id INT(11) NOT NULL,
        car_id INT(11) NOT NULL,
        message TEXT NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id),
        FOREIGN KEY (receiver_id) REFERENCES users(id),
        FOREIGN KEY (car_id) REFERENCES cars(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS wishlist (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        car_id INT(11) NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
        UNIQUE KEY unique_wishlist (user_id, car_id)
    )"
];

foreach ($tables as $sql) {
    if (!mysqli_query($conn, $sql)) {
        echo "Error creating table: " . mysqli_error($conn) . "<br>";
    }
}

// Create admin user if not exists
$password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (name, email, password, role) 
        VALUES ('Admin', 'admin@example.com', '$password', 'admin')";
mysqli_query($conn, $sql);

echo "Database setup completed successfully!";
mysqli_close($conn);
?>
