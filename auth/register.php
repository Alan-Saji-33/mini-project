<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$page_title = "Register";
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitizeInput($_POST['phone']);
    $role = isset($_POST['role']) && $_POST['role'] === 'seller' ? 'seller' : 'buyer';

    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if email exists
        $sql = "SELECT id FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $error = 'Email already exists';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (name, email, password, phone, role) 
                    VALUES ('$name', '$email', '$hashed_password', '$phone', '$role')";
            
            if (mysqli_query($conn, $sql)) {
                $_SESSION['success_message'] = 'Registration successful! Please login.';
                redirect('login.php');
            } else {
                $error = 'Error: ' . mysqli_error($conn);
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="form-container">
    <h1>Create an Account</h1>
    <?php displayError($error); ?>
    
    <form method="post" action="register.php">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <div class="form-group">
            <label>Register As:</label>
            <div>
                <input type="radio" id="buyer" name="role" value="buyer" checked>
                <label for="buyer">Buyer</label>
                
                <input type="radio" id="seller" name="role" value="seller">
                <label for="seller">Seller</label>
            </div>
        </div>
        
        <button type="submit" class="btn">Register</button>
    </form>
    
    <p style="margin-top: 1rem;">Already have an account? <a href="login.php">Login here</a></p>
</div>

<?php
include '../includes/footer.php';
mysqli_close($conn);
?>
