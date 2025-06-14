<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Used Car Marketplace'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">CarMarket</a>
                <button id="mobile-menu-btn">☰</button>
                <ul id="mobile-menu" class="hidden">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="cars/search.php">Browse Cars</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isSeller()): ?>
                            <li><a href="user/listings.php">My Listings</a></li>
                        <?php endif; ?>
                        <li><a href="user/dashboard.php">Dashboard</a></li>
                        <li><a href="auth/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="auth/login.php">Login</a></li>
                        <li><a href="auth/register.php">Register</a></li>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php
        if (isset($_SESSION['success_message'])) {
            displaySuccess($_SESSION['success_message']);
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            displayError($_SESSION['error_message']);
            unset($_SESSION['error_message']);
        }
        ?>
