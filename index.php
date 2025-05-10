<?php
session_start();
require_once 'auth_check.php';

// Get user info if logged in
$userInfo = getUserDisplayInfo();
$isLoggedIn = isLoggedIn();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moviemate";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MovieMate - Your Movie Companion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.10.2/lottie.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: #fff;
        }

    #loading-screen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #0a0a0a;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
    }

    .loading-container {
        text-align: center;
        width: 80%;
        max-width: 500px;
    }

    #lottie-animation {
        width: 200px;
        height: 200px;
        margin: 0 auto 20px;
    }

    .loading-container h2 {
        color: white;
        font-size: 2.5rem;
        margin-bottom: 10px;
        font-style: italic;
        letter-spacing: 2px;
    }

    .loading-container p {
        color: #ccc;
        margin-bottom: 20px;
        font-size: 1rem;
    }

    .loading-bar {
        width: 100%;
        height: 6px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        overflow: hidden;
        position: relative;
    }

    .loading-progress {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #ff4d4d, #f9cb28);
        border-radius: 10px;
        transition: width 0.4s ease;
        animation: progressAnimation 2s ease-in-out forwards;
    }

    @keyframes progressAnimation {
        0% { width: 0%; }
        20% { width: 20%; }
        50% { width: 50%; }
        80% { width: 80%; }
        100% { width: 100%; }
    }

    .hidden {
        opacity: 0;
        visibility: hidden;
    }

    /* Navigation */
    /* Logo Styles with Icon and Animation */
    .logo {
        display: flex;
        align-items: center;
        color: white;
        font-size: 1.5rem;
        font-style: italic;
        font-weight: bold;
    }

    .logo-icon {
        font-size: 1.8rem;
        margin-right: 8px;
        animation: iconAnimation 4s infinite ease-in-out;
    }

    .logo-text {
        animation: textAnimation 4s infinite ease-in-out;
    }

    /* Icon Animation with Color Change */
    @keyframes iconAnimation {
        0% {
            color: #ffffff; /* White */
            transform: rotate(0deg) scale(1);
        }
        25% {
            color: #ff4d4d; /* Red */
            transform: rotate(10deg) scale(1.1);
        }
        50% {
            color: #f9cb28; /* Yellow */
            transform: rotate(0deg) scale(1.2);
        }
        75% {
            color: #ff4d4d; /* Red */
            transform: rotate(-10deg) scale(1.1);
        }
        100% {
            color: #ffffff; /* White */
            transform: rotate(0deg) scale(1);
        }
    }

    /* Text Animation with Color Change */
    @keyframes textAnimation {
        0% {
            color: #ffffff; /* White */
            transform: translateY(0);
        }
        25% {
            color: #ff4d4d; /* Red */
            transform: translateY(-2px);
        }
        50% {
            color: #f9cb28; /* Yellow */
            transform: translateY(0);
        }
        75% {
            color: #ff4d4d; /* Red */
            transform: translateY(2px);
        }
        100% {
            color: #ffffff; /* White */
            transform: translateY(0);
        }
    }

    /* Ensure navbar layout remains intact */
    .navbar {
        padding: 1rem 5%;
        background: #000;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        z-index: 1000;
    }

    .nav-links {
        display: flex;
        gap: 2rem;
    }

    .nav-links a {
        color: white;
        text-decoration: none;
        position: relative;
        padding-bottom: 5px; /* Space for the underline */
    }

    /* Underline effect on hover */
    .nav-links a:hover {
        color: #ccc; /* Change text color on hover */
    }

    .nav-links a::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 2px;
        background-color: white; /* White underline */
        bottom: 0;
        left: 0;
        transform: scaleX(0);
        transform-origin: bottom right;
        transition: transform 0.3s ease;
    }

    .nav-links a:hover::after {
        transform: scaleX(1);
        transform-origin: bottom left;
    }

    .user-actions {
        color: white;
    }

    .user-icon {
        color: white;
        text-decoration: none;
        font-size: 1.5rem;
        cursor: pointer;
    }

    /* Optional: Add hover effect to user icon */
    .user-icon:hover {
        color: #ccc;
    }

    .profile-pic {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        margin-right: 10px;
        object-fit: cover;
        /* Add these to handle image loading and errors */
        background-color: #f0f0f0; /* Fallback color while loading */
        border: 1px solid #ddd; /* Optional: adds a border for better visibility */
    }

    /* Mobile Menu Button */
    .menu-toggle {
        display: none; /* Hidden by default */
        flex-direction: column;
        cursor: pointer;
        z-index: 1001;
    }

    .menu-toggle .bar {
        width: 25px;
        height: 3px;
        background: white;
        margin: 4px 0;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    /* Mobile Menu Styles */
    @media (max-width: 768px) {
        .menu-toggle {
            display: flex; /* Show on mobile */
        }

        .nav-links {
            position: absolute;
            top: 60px;
            right: 0;
            background: #000;
            flex-direction: column;
            width: 100%;
            height: 100vh;
            align-items: center;
            justify-content: center;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }

        .nav-links.active {
            transform: translateX(0);
        }

        .nav-links a {
            margin: 15px 0;
        }
    }

        .hero {
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
        }

        .slide.active {
            opacity: 1;
        }

        .slide-content {
            position: relative;
            height: 100%;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            padding: 0 5%;
            overflow: hidden;
        }

        /* Background zoom animation */
        .slide.active .slide-content {
            animation: zoomBackground 20s ease forwards;
        }

        @keyframes zoomBackground {
            0% {
                transform: scale(1);
            }
            100% {
                transform: scale(1.1);
            }
        }

        .slide-1 .slide-content {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url("Images/hero-1.jpeg");
            background-size: cover;
            background-position: center;
        }

        .slide-2 .slide-content {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url("Images/hero-2.jpeg");
            background-size: cover;
            background-position: center;
        }

        .slide-3 .slide-content {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url("Images/hero-3.jpeg");
            background-size: cover;
            background-position: center;
        }

        .hero-content {
            max-width: 600px;
            color: white;
        }

        /* Text animation elements */
        .hero-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .hero-description {
            overflow: hidden;
            margin-bottom: 2rem;
        }

        /* Text line animations */
        .slide .hero-title h1,
        .slide .hero-description p {
            opacity: 0;
            transform: translateY(50px);
        }

        .slide.active .hero-title h1 {
            animation: slideUpFade 0.8s ease forwards;
        }

        .slide.active .hero-description p {
            animation: slideUpFade 0.8s ease forwards 0.3s;
        }

        .slide.active .explore-btn {
            animation: slideUpFade 0.8s ease forwards 0.6s;
            opacity: 0;
        }

        @keyframes slideUpFade {
            0% {
                opacity: 0;
                transform: translateY(50px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Slideshow Navigation */
        .slideshow-nav {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 100;
        }

        .slide-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .slide-dot.active {
            background: white;
        }

        .explore-btn {
            padding: 0.8rem 2rem;
            background: white;
            color: black;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }

        /* Categories Section */
        .categories {
        padding: 4rem 5%;
        text-align: center;
        background: #fff;
    }

    .categories h2 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        font-weight: 900;
    }

    .categories p {
        margin-bottom: 2rem;
        color: #666;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .category-card {
        position: relative;
        height: 400px;
        overflow: hidden;
        cursor: pointer;
    }

    .image-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .category-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .category-card:hover img {
        transform: scale(1.1);
    }

    .category-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 2rem;
        background: linear-gradient(transparent, rgba(0,0,0,0.8));
        color: white;
        text-align: left;
        transform: translateY(0);
        transition: transform 0.3s ease;
    }

    .category-content h3 {
        font-size: 1.8rem;
        margin-bottom: 1rem;
    }

    .shop-now-btn {
        display: inline-block;
        padding: 0.8rem 2rem;
        background: white;
        color: black;
        text-decoration: none;
        border-radius: 5px;
        font-weight: 500;
        transition: background 0.3s ease;
    }

    .shop-now-btn:hover {
        background: #f0f0f0;
    }

    @media (max-width: 768px) {
        .category-grid {
            grid-template-columns: 1fr;
        }
        
        .category-card {
            height: 300px;
        }
    }

        /* New Releases Section */
        .new-releases {
            padding: 4rem 5%;
        }

        .upcoming-movies {
            padding: 4rem 5%;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 2rem;
        }

        .movie-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .movie-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .movie-info {
            padding: 1rem;
        }

        .movie-info h4 {
            margin-bottom: 0.5rem;
        }

        .price {
            color: #666;
        }

        .promotions {
            height: 400px;
            position: relative;
            overflow: hidden;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin: 4rem 0;
        }

        .promotions::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('Images/50.png');
            background-size: cover;
            background-position: center;
            transform: scale(1);
            transition: transform 0.5s ease-out;
            z-index: -1;
        }

        .promotions.zoom-active::before {
            transform: scale(1.1);
        }

        .promotions::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        .promotions-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            padding: 0 20px;
        }

        .promotions h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .promotions p {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        /* Footer */
        footer {
            background: #000;
            color: white;
            padding: 4rem 5% 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-column h3 {
            margin-bottom: 1rem;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column ul li {
            margin-bottom: 0.5rem;
        }

        .footer-column ul li a {
            color: #999;
            text-decoration: none;
        }

        .newsletter input {
            padding: 0.8rem;
            margin-right: 0.5rem;
            border: none;
            border-radius: 5px;
        }

        .newsletter button {
            padding: 0.8rem 1.5rem;
            background: white;
            color: black;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .footer-bottom {
            border-top: 1px solid #333;
            padding-top: 2rem;
            text-align: center;
            color: #999;
        }

        @media (max-width: 768px) {
            .category-grid {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .user-profile {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .profile-pic {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }

        .user-name {
            color: white;
            margin-right: 10px;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
            min-width: 150px;
            z-index: 1000;
        }

        .user-profile:hover .user-dropdown {
            display: block;
        }

        .user-dropdown a {
            color: #333;
            padding: 10px 15px;
            display: block;
            text-decoration: none;
        }

        .user-dropdown a:hover {
            background: #f5f5f5;
        }

        .chat-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #000;
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, background-color 0.3s ease;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-button i {
            font-size: 24px;
        }

        .chat-button:hover {
            transform: scale(1.1);
            background: #333;
        }

        .ai-assistant {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 350px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .ai-assistant.hidden {
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
        }

        .assistant-header {
            background: #000;
            color: white;
            padding: 12px 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .assistant-header h3 {
            margin: 0;
            font-size: 1rem;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 5px;
            font-size: 16px;
        }

        .close-btn:hover {
            opacity: 0.8;
        }

        .assistant-body {
            padding: 20px;
            height: 400px;
            display: flex;
            flex-direction: column;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 15px;
            padding: 10px;
        }

        .user-message, .bot-message {
            margin-bottom: 10px;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .user-message {
            background: #f0f0f0;
            margin-left: auto;
        }

        .bot-message {
            background: #000;
            color: white;
            margin-right: auto;
        }

        .chat-input {
            display: flex;
            gap: 10px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 10px;
        }

        .chat-input input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
        }

        .chat-input button {
            background: #000;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .chat-input button:hover {
            background: #333;
        }

        /* Animation for the chat button */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .chat-button.pulse {
            animation: pulse 2s infinite;
        }

        @media (max-width: 480px) {
            .ai-assistant {
                width: calc(100% - 40px);
                bottom: 90px;
                right: 20px;
            }
            
            .chat-button {
                width: 50px;
                height: 50px;
            }
        }        

        /* Base for animation elements - initially hidden */
        .category-card, .movie-card, .categories h2, .categories p, 
        .new-releases .section-header, .upcoming-movies .section-header,
        .footer-column {
            opacity: 0;
            will-change: transform, opacity;
        }

        /* Animation keyframes */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 50px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translate3d(-50px, 0, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translate3d(50px, 0, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale3d(0.8, 0.8, 0.8);
            }
            to {
                opacity: 1;
                transform: scale3d(1, 1, 1);
            }
        }

        /* Animation classes */
        .animated {
            animation-duration: 0.8s;
            animation-fill-mode: both;
            animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);
        }

        .fadeInUp {
            animation-name: fadeInUp;
        }

        .fadeInLeft {
            animation-name: fadeInLeft;
        }

        .fadeInRight {
            animation-name: fadeInRight;
        }

        .zoomIn {
            animation-name: zoomIn;
        }

        /* Add a smooth parallax effect to the promotions section */
        .promotions {
            background-attachment: fixed;
            transition: all 0.5s ease;
        }

        /* Add a subtle floating animation to the chat button */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0px); }
        }

        .chat-button {
            animation: float 3s ease-in-out infinite;
        }

        /* Add a subtle scale effect to section headers */
        .section-header {
            transition: transform 0.3s ease;
        }

        .section-header:hover {
            transform: scale(1.02);
        }
    </style>
</head>
<body>

<div id="loading-screen">
    <div class="loading-container">
        <div id="lottie-animation"></div>
        <h2>MovieMate</h2>
        <p>Your movie experience is loading...</p>
        <div class="loading-bar">
            <div class="loading-progress"></div>
        </div>
    </div>
</div>

<nav class="navbar">
    <div class="logo">
        <i class="fas fa-film logo-icon"></i>
        <span class="logo-text">MovieMate</span>
    </div>
    <!-- Mobile Menu Button -->
    <div class="menu-toggle" id="mobile-menu">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
    </div>
    <div class="nav-links" id="nav-links">
        <a href="index.php">Home</a>
        <?php if ($isLoggedIn): ?>
            <?php if (isAdmin()): ?>
                <!-- Admin specific links -->
                <a href="admin.php">Admin Dashboard</a>
                <a href="edit-movie.php">Movie Management</a>
                <a href="update-items.php">Merchandise Management</a>
                <a href="update-food.php">Food & Beverage Management</a>
                <a href="about.php">About Us</a>
            <?php elseif (isStaff()): ?>
                <!-- Staff specific links -->
                <a href="staff_scanner.php">Ticket Scanner</a>
                <a href="movie.php">Movie Theater</a>
                <a href="items.php">Film Merchandise</a>
                <a href="food.php">Food and Beverage</a>
                <a href="about.php">About Us</a>
            <?php else: ?>
                <!-- Customer links -->
                <a href="movie.php">Movie Theater</a>
                <a href="items.php">Film Merchandise</a>
                <a href="food.php">Food and Beverage</a>
                <a href="about.php">About Us</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="Login.php" onclick="return requireAuth()">Movie Theater</a>
            <a href="Login.php" onclick="return requireAuth()">Film Merchandise</a>
            <a href="Login.php" onclick="return requireAuth()">Food and Beverage</a>
            <a href="about.php">About Us</a>
        <?php endif; ?>
    </div>
    <div class="user-actions">
        <?php if ($isLoggedIn): ?>
            <div class="user-profile">
                <?php 
                // Use the profile picture from userInfo, fallback to default if not available
                $profilePicture = isset($userInfo['profile_picture']) && !empty($userInfo['profile_picture'])
                    ? $userInfo['profile_picture']
                    : 'Images/default-avatar.jpg';
                ?>
                <img src="<?php echo htmlspecialchars($profilePicture); ?>" 
                    alt="Profile Picture" 
                    class="profile-pic"
                    onerror="this.onerror=null; this.src='Images/default-avatar.jpg'">
                <span class="user-name"><?php echo isset($userInfo['full_name']) ? htmlspecialchars($userInfo['full_name']) : 'User'; ?></span>
                <div class="user-dropdown">
                    <?php if (isAdmin()): ?>
                        <a href="admin.php"><i class="fas fa-user-shield"></i> Admin Dashboard</a>
                    <?php elseif (isStaff()): ?>
                        <a href="staff_scanner.php"><i class="fas fa-ticket-alt"></i> Ticket Scanner</a>
                    <?php else: ?>
                        <a href="customer.php"><i class="fas fa-user"></i> Profile</a>
                    <?php endif; ?>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="Login.php" class="user-icon"><i class="fas fa-user"></i></a>
        <?php endif; ?>
    </div>
</nav>

    <section class="hero">
        <div class="slide slide-1 active">
            <div class="slide-content">
                <div class="hero-content">
                    <div class="hero-title">
                        <h1>Curated Picks for Movie Lovers</h1>
                    </div>
                    <div class="hero-description">
                        <p>Explore our handpicked selection of top-rated films and enjoy seamless booking. Dive into the world of cinema with ease and excitement.</p>
                    </div>
                    <a href="#" class="explore-btn">Explore Now</a>
                </div>
            </div>
        </div>
        
        <div class="slide slide-2">
            <div class="slide-content">
                <div class="hero-content">
                    <div class="hero-title">
                        <h1>Exclusive Movie Premieres</h1>
                    </div>
                    <div class="hero-description">
                        <p>Be the first to watch the most anticipated releases of the year. Join us for special premiere events and unforgettable cinematic experiences.</p>
                    </div>
                    <a href="movie.php" class="explore-btn">Book Premiere</a>
                </div>
            </div>
        </div>
        
        <div class="slide slide-3">
            <div class="slide-content">
                <div class="hero-content">
                    <div class="hero-title">
                        <h1>Premium Cinema Experience</h1>
                    </div>
                    <div class="hero-description">
                        <p>Immerse yourself in state-of-the-art sound and picture quality. Enjoy ultimate comfort with our luxury seating and premium amenities.</p>
                    </div>
                    <a href="#" class="explore-btn">Learn More</a>
                </div>
            </div>
        </div>

        <div class="slideshow-nav">
            <div class="slide-dot active"></div>
            <div class="slide-dot"></div>
            <div class="slide-dot"></div>
        </div>
    </section>

    <!-- Categories Section HTML -->
    <section class="categories">
    <h2>Discover Movie Categories</h2>
    <p>Explore our diverse selection of films and snacks.</p>
    
    <div class="category-grid">
        <div class="category-card">
            <div class="image-container">
                <img src="Images/ca-1.png" alt="Film Merchandise">
            </div>
            <div class="category-content">
                <h3>Film Merchandise</h3>
                <?php if ($isLoggedIn): ?>
                    <a href="items.php" class="shop-now-btn">Shop Now</a>
                <?php else: ?>
                    <a href="Login.php" class="shop-now-btn" onclick="return requireAuth()">Shop Now</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="category-card">
            <div class="image-container">
                <img src="Images/ca-2.png" alt="Food and Beverage">
            </div>
            <div class="category-content">
                <h3>Food and Beverage</h3>
                <?php if ($isLoggedIn): ?>
                    <a href="food.php" class="shop-now-btn">Shop Now</a>
                <?php else: ?>
                    <a href="Login.php" class="shop-now-btn" onclick="return requireAuth()">Shop Now</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="category-card">
            <div class="image-container">
                <img src="Images/ca-3.png" alt="Movie Tickets">
            </div>
            <div class="category-content">
                <h3>Movie Tickets</h3>
                <?php if ($isLoggedIn): ?>
                    <a href="movie.php" class="shop-now-btn">Shop Now</a>
                <?php else: ?>
                    <a href="Login.php" class="shop-now-btn" onclick="return requireAuth()">Shop Now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!--getting mives from the database -->
<section class="new-releases">
    <div class="section-header">
        <div>
            <h2>New Releases</h2>
            <p>Book your tickets for the latest blockbusters now!</p>
        </div>
    </div>

    <div class="movie-grid">
        <?php
        // Query to fetch movies with status 'now_showing'
        $sql = "SELECT * FROM movies WHERE status = 'now_showing' ORDER BY release_date DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $title = $row['title'];
                $thumbnail = $row['thumbnail'];
                $price = number_format($row['price'], 2);
                $id = $row['id'];
                
                echo "<div class='movie-card'>";
                if ($isLoggedIn) {
                    echo "<a href='movie-booking.php?id=$id'>";
                } else {
                    echo "<a href='Login.php' onclick='return requireAuth()'>";
                }
                echo "
                        <img src='$thumbnail' alt='$title'>
                        <div class='movie-info'>
                            <h4>$title</h4>
                            <p class='price'>Rs:$price</p>
                            " . ($isLoggedIn ? "<span class='book-now'>Book Now →</span>" : "<span class='book-now'>Login to Book →</span>") . "
                        </div>
                    </a>
                </div>";
            }
        } else {
            echo "<p>No new releases available at the moment.</p>";
        }
        ?>
    </div>
</section>

<!-- Upcoming Movies Section with Authentication -->
<section class="upcoming-movies">
    <div class="section-header">
        <div>
            <h2>Upcoming Movies</h2>
            <p>Get ready for the latest upcoming releases!</p>
        </div>
    </div>

    <div class="movie-grid">
        <?php
        // Query to fetch movies with status 'upcoming'
        $sql = "SELECT * FROM movies WHERE status = 'upcoming' ORDER BY release_date ASC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $title = $row['title'];
                $thumbnail = $row['thumbnail'];
                $price = number_format($row['price'], 2);
                $release_date = date("F j, Y", strtotime($row['release_date']));
                $id = $row['id'];
                
                echo "<div class='movie-card'>";
                if ($isLoggedIn) {
                    echo "<a href='movie-booking.php?id=$id'>";
                } else {
                    echo "<a href='Login.php' onclick='return requireAuth()'>";
                }
                echo "
                        <img src='$thumbnail' alt='$title'>
                        <div class='movie-info'>
                            <h4>$title</h4>
                            <p class='release-date'>Release Date: $release_date</p>
                            <p class='price'>Rs:$price</p>
                            " . ($isLoggedIn ? "<span class='book-now'>Pre-book Now →</span>" : "<span class='book-now'>Login to Pre-book →</span>") . "
                        </div>
                    </a>
                </div>";
            }
        } else {
            echo "<p>No upcoming movies available at the moment.</p>";
        }
        ?>
    </div>
</section>

<div class="scroll-progress-container">
    <div class="scroll-progress-bar"></div>
</div>

<!-- Add these new styles to your existing CSS -->
<style>
.movie-card {
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.movie-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.book-now {
    display: inline-block;
    margin-top: 10px;
    color: #2c3e50;
    font-weight: 500;
    transition: color 0.3s ease;
}

.movie-card:hover .book-now {
    color: #e74c3c;
}

.movie-info {
    padding: 1rem;
}

.movie-info h4 {
    margin-bottom: 0.5rem;
    color: #2c3e50;
    font-size: 1.1rem;
}

.price {
    color: #666;
    font-weight: 500;
}

.release-date {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

/* Authentication message style */
.auth-message {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
    padding: 0.5rem;
    border-radius: 5px;
    margin-top: 0.5rem;
    font-size: 0.9rem;
    text-align: center;
}

.movie-card a {
    text-decoration: none;
    color: inherit; 
}

.movie-card a:hover {
    text-decoration: none;
}

/* Style for movie card text */
.movie-info h4,
.movie-info .price,
.movie-info .release-date,
.movie-info .book-now {
    text-decoration: none; 
    color: inherit;
}

/* Hover effect for movie cards */
.movie-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.movie-card:hover .book-now {
    color: #e74c3c;
}

.scroll-progress-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    z-index: 9999;
    background: transparent;
}

.scroll-progress-bar {
    height: 100%;
    width: 0;
    background: linear-gradient(90deg, #ff4d4d, #f9cb28);
    transition: width 0.2s ease;
}

/* Enhanced AI Assistant Styles */
.bot-message, .user-message {
    position: relative;
    padding: 12px 18px;
    margin-bottom: 15px;
}

/* Speech bubble pointers */
.bot-message::before {
    content: '';
    position: absolute;
    left: -10px;
    bottom: 10px;
    width: 0;
    height: 0;
    border-right: 10px solid #000;
    border-top: 5px solid transparent;
    border-bottom: 5px solid transparent;
}

.user-message::before {
    content: '';
    position: absolute;
    right: -10px;
    bottom: 10px;
    width: 0;
    height: 0;
    border-left: 10px solid #f0f0f0;
    border-top: 5px solid transparent;
    border-bottom: 5px solid transparent;
}

/* Thinking dots animation */
.thinking-dots {
    display: flex;
    gap: 5px;
    padding: 10px 15px;
    background: #000;
    color: white;
    border-radius: 15px;
    max-width: 80px;
    margin-right: auto;
}

.dot {
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
    animation: bounce 0.6s infinite;
}

.dot:nth-child(2) {
    animation-delay: 0.2s;
}

.dot:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

/* Smooth message appearance */
.bot-message, .user-message {
    animation: messageAppear 0.3s ease-out;
}

@keyframes messageAppear {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .bot-message::before {
        left: -8px;
        border-right-width: 8px;
    }
    
    .user-message::before {
        right: -8px;
        border-left-width: 8px;
    }
    
    .thinking-dots {
        max-width: 60px;
    }
    
    .dot {
        width: 6px;
        height: 6px;
    }
}
</style>



        <!-- Promotions Section HTML -->
    <section class="promotions" id="promotions-banner">
        <div class="promotions-content">
            <h2>Unmissable Movie Discounts</h2>
            <p>Grab our exclusive movie theater deals for the best entertainment experience.</p>
            <p>Limited Time Only!</p>
        </div>
    </section>
    
    <!-- Add this right before the closing </body> tag -->
    <div id="ai-assistant" class="ai-assistant hidden">
        <div class="assistant-header" id="assistant-header">
            <h3>MovieMate Assistant</h3>
            <button class="close-btn" id="close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="assistant-body">
            <div class="chat-messages" id="chat-messages">
                <div class="bot-message">
                    Hi! I'm your MovieMate assistant. How can I help you with movies, tickets, or merchandise today?
                </div>
            </div>
            <div class="chat-input">
                <input type="text" id="user-input" placeholder="Type your message...">
                <button id="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <button id="chat-button" class="chat-button">
        <i class="fas fa-comments"></i>
    </button>

    <footer>
        <div class="footer-grid">
            <div class="footer-column">
                <h3>Explore</h3>
                <ul>
                    <li><a href="#">Latest Releases</a></li>
                    <li><a href="#">Classic Favorites</a></li>
                    <li><a href="#">Movie News</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Shop</h3>
                <ul>
                    <li><a href="#">Film Merchandise</a></li>
                    <li><a href="#">Food and Beverage</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Offers</h3>
                <ul>
                    <li><a href="#">Exclusive Offers</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Stay Updated</h3>
                <div class="newsletter">
                    <input type="email" placeholder="Enter your email">
                    <button>Subscribe</button>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 MovieMate. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Enhanced slideshow functionality
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slide-dot');
        let currentSlide = 0;

        function showSlide(index) {
            // Remove active class from all slides and dots
            slides.forEach(slide => {
                slide.classList.remove('active');
                // Reset animations by removing and re-adding content
                const content = slide.querySelector('.hero-content').innerHTML;
                slide.querySelector('.hero-content').innerHTML = content;
            });
            dots.forEach(dot => dot.classList.remove('active'));
            
            // Add active class to current slide and dot
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            currentSlide = index;
        }

        // Add click events to dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => showSlide(index));
        });

        // Auto-advance slideshow
        function nextSlide() {
            let next = currentSlide + 1;
            if (next >= slides.length) next = 0;
            showSlide(next);
        }

        // Change slides every 6 seconds (increased from 5 to account for longer animations)
        setInterval(nextSlide, 6000);

        //promotion banner
        document.addEventListener('DOMContentLoaded', function() {
    const promotionsBanner = document.getElementById('promotions-banner');
    let isZoomed = false;

    function checkScroll() {
        const rect = promotionsBanner.getBoundingClientRect();
        const windowHeight = window.innerHeight;
        
        // Check if the banner is in view
        const isInView = rect.top < windowHeight && rect.bottom > 0;
        
        // Add or remove zoom class based on visibility
        if (isInView && !isZoomed) {
            promotionsBanner.classList.add('zoom-active');
            isZoomed = true;
        } else if (!isInView && isZoomed) {
            promotionsBanner.classList.remove('zoom-active');
            isZoomed = false;
        }
    }

    // Initial check
    checkScroll();

    // Add scroll event listener with throttling for better performance
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (scrollTimeout) {
            window.cancelAnimationFrame(scrollTimeout);
        }
        scrollTimeout = window.requestAnimationFrame(checkScroll);
    });
});

function requireAuth() {
    alert('Please log in to access this feature.');
    return true;
}

// Existing elements from your design
// AI Assistant JavaScript
const chatButton = document.getElementById('chat-button');
const assistant = document.getElementById('ai-assistant');
const closeBtn = document.getElementById('close-btn');
const chatMessages = document.getElementById('chat-messages');
const userInput = document.getElementById('user-input');
const sendBtn = document.getElementById('send-btn');

const API_KEY = "AIzaSyBfVo8TkGVNwMzH5R525oz0OnfQjZa7flc";
const API_URL = `https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent?key=${API_KEY}`;
const chatHistory = [];

const businessContext = `
You are a helpful assistant for MovieMate, a comprehensive movie ticket booking platform. MovieMate offers the following services:
- **Movie Ticket Booking**: Browse and book tickets for the latest movies. Check showtimes and theater availability. Secure ticket payment options.
- **Meal Ordering Plan**: Order meals to enjoy while watching the movie. Wide selection of movie theater snacks and meals.
- **Merchandise Purchasing**: Purchase movie-themed merchandise. Exclusive limited-edition items from your favorite films.

### Current Movie Listings:
- **Now Showing**:
  - *Avengers: Endgame*
  - *Spider-Man: No Way Home*
  - *Gajaman*
- **Upcoming**:
  - *KGF: Capture 2*
  - *The Batman*

### Food & Beverage Items:
- **Snacks**:
  - Popcorn
  - French Fries
  - Burger
  - Pizza
- **Drinks**:
  - Coca-Cola
  - Pepsi

### Business Contact Information:
- **Address**: 123 Cinema Road, Matara, Sri Lanka
- **Phone Number**: +94 41 222 3333
- **Email**: info@moviemate.lk

### Customer Support:
If you need any assistance or have questions regarding movie bookings, meals, or merchandise, our support team is ready to help. Reach out to us via email or call the number provided for immediate assistance.
`;


// Create message element
const createMessageElement = (message, isUser = false) => {
    const messageDiv = document.createElement('div');
    messageDiv.className = isUser ? 'user-message' : 'bot-message';
    messageDiv.textContent = message;
    return messageDiv;
};

// Create thinking dots element
const createThinkingDots = () => {
    const thinkingDiv = document.createElement('div');
    thinkingDiv.className = 'thinking-dots';
    thinkingDiv.innerHTML = `
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
    `;
    return thinkingDiv;
};

// Add message to chat
const addMessage = (message, isUser = false) => {
    const messageDiv = createMessageElement(message, isUser);
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
};

// Generate bot response
const generateBotResponse = async (userMessage) => {
    chatHistory.push({
        role: "user",
        parts: [{ text: `${businessContext}\n\n${userMessage}` }],
    });

    const requestOptions = {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            contents: chatHistory,
        }),
    };

    try {
        const response = await fetch(API_URL, requestOptions);
        const data = await response.json();
        if (!response.ok) throw new Error(data.error?.message || "API error");

        const botResponse = data.candidates[0].content.parts[0].text.replace(/\*\*(.*?)\*\*/g, "$1").trim();
        
        // Remove thinking dots before adding response
        const thinkingElement = chatMessages.querySelector('.thinking-dots');
        if (thinkingElement) thinkingElement.remove();
        
        addMessage(botResponse);
        chatHistory.push({
            role: "model",
            parts: [{ text: botResponse }],
        });
    } catch (error) {
        const thinkingElement = chatMessages.querySelector('.thinking-dots');
        if (thinkingElement) thinkingElement.remove();
        addMessage("Sorry, I encountered an error. Please try again.", false);
    }
};

// Process user input
const processUserInput = async () => {
    const message = userInput.value.trim();
    if (!message) return;

    addMessage(message, true);
    userInput.value = '';

    // Add thinking dots
    const thinkingDots = createThinkingDots();
    chatMessages.appendChild(thinkingDots);
    chatMessages.scrollTop = chatMessages.scrollHeight;

    await generateBotResponse(message);
};

// Event listeners
sendBtn.addEventListener('click', processUserInput);
userInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') processUserInput();
});

chatButton.addEventListener('click', () => {
    assistant.classList.remove('hidden');
    chatButton.style.display = 'none';
});

closeBtn.addEventListener('click', () => {
    assistant.classList.add('hidden');
    chatButton.style.display = 'flex';
});

document.addEventListener('click', (e) => {
    if (!assistant.contains(e.target) && 
        !chatButton.contains(e.target) && 
        !assistant.classList.contains('hidden')) {
        assistant.classList.add('hidden');
        chatButton.style.display = 'flex';
    }
});

document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lottie animation
        const animation = lottie.loadAnimation({
            container: document.getElementById('lottie-animation'),
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: 'https://assets6.lottiefiles.com/packages/lf20_khzniaya.json' // Cinema/movie themed animation
        });

        // Simulate loading time and update progress
        let progress = 0;
        const progressBar = document.querySelector('.loading-progress');
        const loadingScreen = document.getElementById('loading-screen');
        
        // Hide loading screen when everything is ready
        window.addEventListener('load', function() {
            // Allow animation to complete
            setTimeout(function() {
                loadingScreen.classList.add('hidden');
                // Remove from DOM after transition completes
                setTimeout(function() {
                    loadingScreen.remove();
                    // Start slideshow animation only after loading completes
                    if (slides.length > 0) {
                        showSlide(0);
                    }
                }, 500);
            }, 2500); // Show loading screen for at least 2.5 seconds
        });
        
        // In case window.load already fired
        if (document.readyState === 'complete') {
            setTimeout(function() {
                loadingScreen.classList.add('hidden');
                setTimeout(function() {
                    loadingScreen.remove();
                    if (slides.length > 0) {
                        showSlide(0);
                    }
                }, 500);
            }, 2500);
        }
    });

    // Modify your existing slideshow code to not start until loading is complete
    const originalShowSlide = window.showSlide;
    window.showSlide = function(index) {
        if (document.getElementById('loading-screen') && 
            !document.getElementById('loading-screen').classList.contains('hidden')) {
            return; // Don't start slideshow until loading is complete
        }
        originalShowSlide(index);
    };
    
    // Mobile Menu Toggle
    const mobileMenu = document.getElementById('mobile-menu');
    const navLinks = document.getElementById('nav-links');

    mobileMenu.addEventListener('click', () => {
        navLinks.classList.toggle('active');
        mobileMenu.classList.toggle('active');
    });

    // Add this JavaScript code right before the closing </body> tag

document.addEventListener('DOMContentLoaded', function() {
    // Function to check if an element is in viewport
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.85 &&
            rect.bottom >= 0
        );
    }

    // Elements to animate
    const elementsToAnimate = [
        { selector: '.categories h2', animation: 'fadeInUp' },
        { selector: '.categories p', animation: 'fadeInUp' },
        { selector: '.category-card', animation: 'zoomIn', staggered: true },
        { selector: '.new-releases .section-header', animation: 'fadeInLeft' },
        { selector: '.movie-card', animation: 'fadeInUp', staggered: true },
        { selector: '.upcoming-movies .section-header', animation: 'fadeInRight' },
        { selector: '.footer-column', animation: 'fadeInUp', staggered: true }
    ];

    // Function to add animation class to elements
    function animateOnScroll() {
        elementsToAnimate.forEach(item => {
            const elements = document.querySelectorAll(item.selector);
            
            elements.forEach((element, index) => {
                if (isInViewport(element) && !element.classList.contains('animated')) {
                    // Add a delay for staggered animations
                    if (item.staggered) {
                        setTimeout(() => {
                            element.classList.add('animated', item.animation);
                        }, index * 150); // 150ms delay between each element
                    } else {
                        element.classList.add('animated', item.animation);
                    }
                }
            });
        });
    }

    // Run on scroll
    window.addEventListener('scroll', animateOnScroll);
    
    // Run once on load
    animateOnScroll();
});

// Update scroll progress bar
window.addEventListener('scroll', function() {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        document.querySelector('.scroll-progress-bar').style.width = scrolled + '%';
    });
    </script>

</body>
</html>