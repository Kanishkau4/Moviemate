<!-- index.php -->
<?php
session_start();
//include 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">Admin Dashboard</div>
            <nav>
                <a href="#" class="nav-item active" data-page="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="pages/users.php" class="nav-item" data-page="users">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
                <a href="pages/movies.php" class="nav-item" data-page="movies">
                    <i class="fas fa-film"></i>
                    <span>Movie Management</span>
                </a>
                <a href="#" class="nav-item" data-page="reservations">
                    <i class="fas fa-calendar"></i>
                    <span>Reservation Management</span>
                </a>
                <a href="#" class="nav-item" data-page="stores">
                    <i class="fas fa-store"></i>
                    <span>Store Management</span>
                </a>
                <a href="#" class="nav-item" data-page="booking">
                    <i class="fas fa-book"></i>
                    <span>Booking Management</span>
                </a>
                <a href="#" class="nav-item" data-page="feedbacks">
                    <i class="fas fa-thumbs-up"></i>
                    <span>Feedbacks</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div id="content-area">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="admin.js"></script>
</body>
</html>