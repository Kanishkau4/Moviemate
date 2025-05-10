<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Database connection (adjust credentials as needed)
$conn = new mysqli("localhost", "root", "", "moviemate");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user data from session
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$profile_picture = $_SESSION['profile_picture'];

// Get counts from database
$movie_count = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE user_id = $user_id")->fetch_assoc()['count'];
$food_count = $conn->query("SELECT COUNT(*) as count FROM food_orders WHERE user_id = $user_id")->fetch_assoc()['count'];
$item_count = $conn->query("SELECT COUNT(*) as count FROM items_orders WHERE user_id = $user_id")->fetch_assoc()['count'];

// Get upcoming movie bookings with movie details
$upcoming_movies_query = "
    SELECT b.*, m.title, m.thumbnail
    FROM bookings b 
    JOIN movies m ON b.movie_id = m.id 
    WHERE b.user_id = $user_id 
    AND DATE(b.booking_date) >= CURDATE()
    ORDER BY b.booking_date, b.show_time";
$upcoming_movies_result = $conn->query($upcoming_movies_query);
$upcoming_movies = [];
while ($row = $upcoming_movies_result->fetch_assoc()) {
    $upcoming_movies[] = $row;
}

// Handle feedback submission (reverted to previous logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_action'])) {
    $response = array();
    
    try {
        $feedback = $_POST['feedback'];
        $rating = $_POST['rating'];
        $user_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("INSERT INTO user_feedback (user_id, feedback_text, rating) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $user_id, $feedback, $rating);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Thank you for your feedback!";
        } else {
            throw new Exception("Error saving feedback");
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = "Error: " . $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get messages count and data
$messages_query = "SELECT * FROM user_messages ORDER BY sent_date DESC";
$messages_result = $conn->query($messages_query);
$messages_count = $messages_result ? $messages_result->num_rows : 0;
$messages = [];
if ($messages_result) {
    while ($row = $messages_result->fetch_assoc()) {
        $messages[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MovieMate - Customer Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #3498db;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --background-color: #f5f6fa;
            --text-color: #2c3e50;
            --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --card-radius: 16px;
            --transition-speed: 0.3s;
            
            /* Dark mode variables */
            --dark-bg: #121212;
            --dark-card-bg: #1e1e1e;
            --dark-secondary-bg: #252525;
            --dark-text: #ffffff;
            --dark-secondary-text: #cccccc;
            --dark-border: #404040;
            --dark-hover: #333333;
        }

        /* Dark mode classes */
        body.dark-mode {
            background-color: var(--dark-bg);
            color: var(--dark-text);
        }

        .dark-mode .navbar {
            background-color: var(--dark-bg);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .dark-mode .profile-card,
        .dark-mode .stat-card,
        .dark-mode .movie-card,
        .dark-mode .notifications-panel,
        .dark-mode .feedback-form {
            background-color: var(--dark-card-bg);
            color: var(--dark-text);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }

        .dark-mode .movie-title,
        .dark-mode .profile-name {
            color: var(--dark-text);
        }

        .dark-mode .notification-item {
            border-bottom-color: var(--dark-border);
        }

        .dark-mode .notification-item:hover {
            background-color: var(--dark-hover);
        }

        .dark-mode .movie-details,
        .dark-mode .notification-message,
        .dark-mode .notification-date {
            color: var(--dark-secondary-text);
        }

        .dark-mode footer {
            background-color: var(--dark-bg);
            box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.2);
        }

        .dark-mode .ticket-hole {
            background: var(--dark-bg);
        }

        .dark-mode .movie-details {
            border-top: 1px dashed var(--dark-border);
        }

        /* Theme toggle button styles */
        .theme-toggle {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 8px;
            font-size: 1.2rem;
            transition: transform var(--transition-speed) ease;
        }

        .theme-toggle:hover {
            transform: rotate(30deg);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            padding-bottom: 70px;
            transition: background-color var(--transition-speed) ease, color var(--transition-speed) ease;
        }

        .navbar {
            background-color: var(--primary-color);
            padding: 1rem 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--card-shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: background-color var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo .fa-film {
            transform: rotate(-15deg);
            color: var(--secondary-color);
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all var(--transition-speed) ease;
            position: relative;
            padding: 8px 0;
            font-size: 0.95rem;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--secondary-color);
            transition: width var(--transition-speed) ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: var(--secondary-color);
        }

        .nav-links a i {
            margin-right: 6px;
        }

        /* Mobile Menu Styles */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
            transition: color var(--transition-speed) ease;
            z-index: 1100;
        }

        .mobile-menu-btn:hover {
            color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }

            .navbar {
                padding: 1rem;
            }

            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                height: 100vh;
                width: 80%;
                max-width: 300px;
                background-color: var(--primary-color);
                flex-direction: column;
                padding: 80px 2rem 2rem;
                transition: right var(--transition-speed) ease;
                z-index: 1000;
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.2);
            }

            .nav-links.active {
                right: 0;
            }

            .nav-links a {
                width: 100%;
                padding: 15px 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .nav-links a:last-child {
                border-bottom: none;
            }

            .nav-links .theme-toggle {
                margin-top: 20px;
            }

            .dark-mode .nav-links {
                background-color: var(--dark-card-bg);
            }

            /* Overlay when menu is open */
            .menu-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
                transition: opacity var(--transition-speed) ease;
                opacity: 0;
            }

            .menu-overlay.active {
                display: block;
                opacity: 1;
            }
        }

        /* Existing mobile media query adjustments */
        @media (max-width: 768px) {
            .logo {
                font-size: 1.5rem;
            }

            .container {
                padding: 0 1rem;
            }
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .profile-card {
            background: white;
            border-radius: var(--card-radius);
            padding: 2.5rem;
            box-shadow: var(--card-shadow);
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 2.5rem;
            align-items: start;
            transition: all var(--transition-speed) ease;
            margin-bottom: 2.5rem;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .profile-image {
            position: relative;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            border: 4px solid white;
            transition: all var(--transition-speed) ease;
        }

        .profile-image:hover {
            transform: scale(1.05);
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .profile-image:hover img {
            transform: scale(1.1);
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        .profile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .profile-name {
            font-size: 2.2rem;
            color: var(--primary-color);
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 0.3rem;
            position: relative;
            display: inline-block;
        }

        .profile-name::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -5px;
            height: 3px;
            width: 60px;
            background: var(--secondary-color);
            border-radius: 10px;
        }

        .edit-profile-btn {
            background-color: transparent;
            color: var(--accent-color);
            border: 2px solid var(--accent-color);
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all var(--transition-speed) ease;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .edit-profile-btn:hover {
            background-color: var(--accent-color);
            color: white;
            transform: translateY(-2px);
        }

        .profile-badge {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(45deg, #FFD700, #FFA500);
            color: #333;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            margin-right: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .profile-badge i {
            margin-right: 5px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .stat-card {
            background: white;
            padding: 1.8rem;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            text-align: center;
            text-decoration: none;
            color: var(--text-color);
            transition: all var(--transition-speed) ease;
            position: relative;
            overflow: hidden;
            border-top: 4px solid transparent;
        }

        .stat-card:nth-child(1) {
            border-top-color: var(--secondary-color);
        }

        .stat-card:nth-child(2) {
            border-top-color: var(--accent-color);
        }

        .stat-card:nth-child(3) {
            border-top-color: var(--success-color);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
            z-index: 1;
        }

        .stat-card i {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
            transition: transform var(--transition-speed) ease;
            position: relative;
            z-index: 2;
        }

        .stat-card:nth-child(1) i {
            color: var(--secondary-color);
        }

        .stat-card:nth-child(2) i {
            color: var(--accent-color);
        }

        .stat-card:nth-child(3) i {
            color: var(--success-color);
        }

        .stat-card:hover i {
            transform: scale(1.2) rotate(10deg);
        }

        .stat-card h3 {
            margin: 0.5rem 0;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.2rem;
            position: relative;
            z-index: 2;
        }

        .stat-card p {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin: 0.8rem 0;
            position: relative;
            z-index: 2;
            transition: all var(--transition-speed) ease;
        }

        .stat-card:nth-child(1) p {
            color: var(--secondary-color);
        }

        .stat-card:nth-child(2) p {
            color: var(--accent-color);
        }

        .stat-card:nth-child(3) p {
            color: var(--success-color);
        }

        .view-details {
            font-size: 0.9rem;
            color: #666;
            opacity: 0;
            transform: translateY(10px);
            transition: all var(--transition-speed) ease;
            display: inline-block;
            margin-top: 0.5rem;
            padding: 5px 15px;
            border-radius: 20px;
            background-color: #f5f5f5;
            position: relative;
            z-index: 2;
        }

        .stat-card:hover .view-details {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .profile-card {
                grid-template-columns: 1fr;
                text-align: center;
                padding: 1.5rem;
            }

            .profile-image {
                margin: 0 auto;
                width: 150px;
                height: 150px;
            }

            .profile-name::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .section-title {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            position: relative;
            display: inline-block;
            padding-left: 1rem;
            font-weight: 600;
        }

        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 5px;
            height: 25px;
            background-color: var(--secondary-color);
            border-radius: 5px;
        }

        .section-title i {
            margin-right: 10px;
            color: var(--secondary-color);
        }

        .upcoming-movies {
            margin-top: 2.5rem;
            padding-bottom: 4rem;
        }

        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
            margin-top: 1.5rem;
        }

        .movie-card {
            background: white;
            border-radius: var(--card-radius);
            position: relative;
            box-shadow: var(--card-shadow);
            display: flex;
            height: 220px;
            overflow: hidden;
            transition: all var(--transition-speed) ease;
        }

        .movie-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .movie-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 25%;
            height: 100%;
            width: 1px;
            background: repeating-linear-gradient(
                to bottom,
                #ccc,
                #ccc 5px,
                transparent 5px,
                transparent 10px
            );
            z-index: 1;
        }

        .movie-thumbnail-container {
            width: 25%;
            padding: 15px;
            position: relative;
            z-index: 2;
        }

        .movie-thumbnail {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 12px;
            transition: transform var(--transition-speed) ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .movie-card:hover .movie-thumbnail {
            transform: scale(1.05);
        }

        .ticket-hole {
            position: absolute;
            width: 20px;
            height: 20px;
            background: var(--background-color);
            border-radius: 50%;
            left: -10px;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.1);
            z-index: 2;
        }

        .ticket-hole-top {
            top: 30px;
        }

        .ticket-hole-bottom {
            bottom: 30px;
        }

        .movie-info {
            width: 75%;
            padding: 20px 15px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 2;
        }

        .movie-header {
            margin-bottom: 10px;
        }

        .movie-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: color var(--transition-speed) ease;
        }

        .movie-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            color: #666;
        }

        .movie-details {
            font-size: 0.95rem;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 0.8rem;
            position: relative;
        }

        .movie-details p {
            margin: 0.3rem 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .movie-details i {
            width: 18px;
            color: var(--primary-color);
        }

        .movie-countdown {
            background: linear-gradient(45deg, var(--primary-color), #34495e);
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 500;
            position: absolute;
            bottom: 20px;
            right: 15px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            transition: all var(--transition-speed) ease;
        }

        .movie-card:hover .movie-countdown {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.2);
        }

        .today-movie {
            border-left: 5px solid var(--secondary-color);
        }

        .today-movie::after {
            content: 'TODAY';
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--secondary-color);
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: bold;
            letter-spacing: 1px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            z-index: 10;
        }

        .ticket-number {
            margin-top: 10px;
            font-size: 0.8rem;
            color: #777;
            font-weight: 600;
            letter-spacing: 1px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .movie-grid {
                grid-template-columns: 1fr;
            }
            
            .movie-card {
                height: auto;
                flex-direction: column;
            }
            
            .movie-card::before {
                left: 0;
                top: 180px;
                width: 100%;
                height: 1px;
                background: repeating-linear-gradient(
                    to right,
                    #ccc,
                    #ccc 5px,
                    transparent 5px,
                    transparent 10px
                );
            }
            
            .movie-thumbnail-container {
                width: 100%;
                padding: 15px 15px 0;
            }
            
            .movie-info {
                width: 100%;
                padding: 15px;
            }
            
            .ticket-hole {
                top: 180px;
                left: auto;
            }
            
            .ticket-hole-top {
                left: 30px;
            }
            
            .ticket-hole-bottom {
                right: 30px;
                left: auto;
            }
        }
        
        footer {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 1rem;
            position: fixed;
            bottom: 0;
            width: 100%;
            left: 0;
            margin-top: 30px;
            box-shadow: 0 -5px 10px rgba(0,0,0,0.1);
            z-index: 900;
            transition: background-color var(--transition-speed) ease;
        }

        .qr-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .qr-button {
            background: linear-gradient(45deg, var(--primary-color), #34495e);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all var(--transition-speed) ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .qr-button:hover {
            background: linear-gradient(45deg, #34495e, var(--primary-color));
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(0,0,0,0.15);
        }

        .feedback-button {
            position: fixed;
            right: 30px;
            bottom: 80px;
            z-index: 1000;
        }

        .feedback-button button {
            background: linear-gradient(45deg, var(--secondary-color), #c0392b);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all var(--transition-speed) ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feedback-button button:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .feedback-form {
            position: fixed;
            right: 30px;
            bottom: 100px;
            width: 320px;
            background: white;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 25px;
            display: none;
            z-index: 1001;
            transition: all var(--transition-speed) ease;
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .feedback-header h3 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.2rem;
        }

        .close-button {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 1.2rem;
            transition: color var(--transition-speed) ease;
        }

        .close-button:hover {
            color: var(--secondary-color);
        }

        .feedback-form textarea {
            width: 100%;
            height: 120px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 20px;
            resize: vertical;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: border-color var(--transition-speed) ease;
        }

        .feedback-form textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .rating {
            margin-bottom: 20px;
        }

        .rating > span {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary-color);
        }

        .stars {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 8px;
        }

        .stars input {
            display: none;
        }

        .stars label {
            color: #ddd;
            cursor: pointer;
            font-size: 1.5rem;
            transition: color 0.2s ease;
        }

        .stars label:hover,
        .stars label:hover ~ label,
        .stars input:checked ~ label {
            color: #ffd700;
        }
        
        .submit-button {
            width: 100%;
            background: linear-gradient(45deg, var(--primary-color), #34495e);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all var(--transition-speed) ease;
            font-weight: 500;
            font-size: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .submit-button:hover {
            background: linear-gradient(45deg, var(--secondary-color), #c0392b);
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(0,0,0,0.15);
        }

        .success-message {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-speed) ease;
        }

        .success-message.show {
            opacity: 1;
            visibility: visible;
        }

        .success-content {
            background: white;
            padding: 40px;
            border-radius: var(--card-radius);
            text-align: center;
            transform: translateY(50px);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 90%;
        }

        .success-message.show .success-content {
            transform: translateY(0);
            opacity: 1;
        }

        .success-icon {
            font-size: 5rem;
            color: var(--success-color);
            margin-bottom: 20px;
            animation: successAnimation 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes successAnimation {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        .success-content h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .success-content p {
            color: #666;
            margin: 0;
            font-size: 1.1rem;
        }

        .submit-button.loading {
            position: relative;
            color: transparent;
        }

        .submit-button.loading::after {
            content: "";
            position: absolute;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .feedback-form.error {
            animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--secondary-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .notifications-panel {
            position: fixed;
            right: 30px;
            top: 80px;
            width: 350px;
            background: white;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 0;
            display: none;
            z-index: 1001;
            transition: all var(--transition-speed) ease;
            overflow: hidden;
            max-height: 500px;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: var(--primary-color);
            color: white;
        }

        .notifications-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .mark-all-read {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 0.85rem;
            opacity: 0.8;
            transition: opacity var(--transition-speed) ease;
        }

        .mark-all-read:hover {
            opacity: 1;
        }

        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            transition: background-color var(--transition-speed) ease;
            cursor: pointer;
            position: relative;
        }

        .notification-item:hover {
            background-color: #f9f9f9;
        }

        .notification-item.unread::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background-color: var(--secondary-color);
            border-radius: 50%;
        }

        .notification-item.unread {
            padding-left: 25px;
            background-color: rgba(231, 76, 60, 0.05);
        }

        .notification-message {
            font-size: 0.9rem;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .notification-date {
            font-size: 0.75rem;
            color: #999;
        }

        .notification-footer {
            padding: 12px;
            text-align: center;
            border-top: 1px solid #eee;
        }

        .view-all-btn {
            color: var(--accent-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color var(--transition-speed) ease;
        }

        .view-all-btn:hover {
            color: var(--secondary-color);
        }

        .notification-list::-webkit-scrollbar {
            width: 5px;
        }

        .notification-list::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .notification-list::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 5px;
        }

        .notification-list::-webkit-scrollbar-thumb:hover {
            background: #ccc;
        }

        @keyframes slideIn {
            0% {
                transform: translateX(100%);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .notifications-panel.show {
            display: block;
            animation: slideIn 0.3s forwards;
        }

        .empty-notifications {
            padding: 30px 20px;
            text-align: center;
            color: #999;
        }

        .empty-notifications i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        .empty-notifications p {
            font-size: 0.95rem;
            margin: 0;
        }

        .toast {
            position: fixed;
            bottom: 100px;
            right: 30px;
            background: white;
            color: var(--text-color);
            padding: 12px 25px;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 1200;
            opacity: 0;
            transform: translateY(30px);
            transition: all var(--transition-speed) ease;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.success i {
            color: var(--success-color);
        }

        .toast.error i {
            color: var(--secondary-color);
        }

        .toast-message {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .tooltip {
            position: relative;
        }

        .tooltip .tooltip-text {
            visibility: hidden;
            width: 120px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.8rem;
            pointer-events: none;
        }

        .tooltip .tooltip-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #333 transparent transparent transparent;
        }

        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }

        @media (max-width: 600px) {
            .feedback-button {
                right: 15px;
                bottom: 70px;
            }
            
            .feedback-form {
                right: 15px;
                width: calc(100% - 30px);
                max-width: 400px;
            }
            
            .notifications-panel {
                right: 15px;
                width: calc(100% - 30px);
                max-width: 400px;
            }
            
            .toast {
                right: 15px;
                max-width: calc(100% - 30px);
            }
            
            .movie-meta {
                flex-direction: column;
                gap: 0.3rem;
            }
        }

        button:focus, a:focus, input:focus, textarea:focus {
            outline: 3px solid rgba(52, 152, 219, 0.5);
            outline-offset: 2px;
        }

        @media print {
            .feedback-button, .notifications-panel, .toast, footer {
                display: none !important;
            }
            
            body {
                padding-bottom: 0;
            }
            
            .profile-card, .stat-card, .movie-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
    <button class="mobile-menu-btn" id="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>
        <div class="logo">
            <i class="fas fa-film"></i>
            <span>MovieMate</span>
        </div>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="movie.php"><i class="fas fa-film"></i> Movies</a>
            <a href="food.php"><i class="fas fa-utensils"></i> Food</a>
            <a href="items.php"><i class="fas fa-box"></i> Merchandise</a> 
            <a href="#" id="notifications-toggle" class="tooltip">
                <i class="fas fa-bell"></i>
                <?php if ($messages_count > 0): ?>
                    <span class="notification-badge"><?php echo $messages_count; ?></span>
                <?php endif; ?>
                <span class="tooltip-text">Notifications</span>
            </a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <button class="theme-toggle tooltip" id="theme-toggle">
                <i class="fas fa-moon"></i>
                <span class="tooltip-text">Toggle Dark Mode</span>
            </button>
        </div>
    </nav>

    <div class="container">
        <div class="profile-card">
            <div class="profile-image">
                <?php if ($profile_picture): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($profile_picture); ?>" alt="Profile Picture">
                <?php else: ?>
                    <img src="Images/default-avatar.jpg" alt="Default Profile Picture">
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <div class="profile-header">
                    <h1 class="profile-name"><?php echo htmlspecialchars($full_name); ?></h1>
                    <a href="edit-profile.php" class="edit-profile-btn">
                        <i class="fas fa-edit"></i>
                        Edit Profile
                    </a>
                </div>
                <div>
                    <span class="profile-badge"><i class="fas fa-crown"></i> Premium Member</span>
                    <span class="profile-badge"><i class="fas fa-certificate"></i> Movie Enthusiast</span>
                </div>
                <div class="stats-grid">
                    <a href="movie-history.php" class="stat-card">
                        <i class="fas fa-ticket-alt"></i>
                        <h3>Movies Booked</h3>
                        <p><?php echo $movie_count; ?></p>
                        <span class="view-details">Click to view details</span>
                    </a>
                    <a href="food-history.php" class="stat-card">
                        <i class="fas fa-utensils"></i>
                        <h3>Food Orders</h3>
                        <p><?php echo $food_count; ?></p>
                        <span class="view-details">Click to view details</span>
                    </a>
                    <a href="item-history.php" class="stat-card">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>Items Ordered</h3>
                        <p><?php echo $item_count; ?></p>
                        <span class="view-details">Click to view details</span>
                    </a>
                </div>
                <?php if (!empty($upcoming_movies)): ?>
                    <div class="qr-actions">
                        <button onclick="window.location.href='generate_qr.php?booking_id=<?php echo $upcoming_movies[0]['id']; ?>&format=image'" class="qr-button">
                            <i class="fas fa-qrcode"></i> View QR Code
                        </button>
                        <button onclick="window.location.href='generate_qr.php?booking_id=<?php echo $upcoming_movies[0]['id']; ?>&format=pdf'" class="qr-button">
                            <i class="fas fa-file-pdf"></i> View Ticket
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($upcoming_movies)): ?>
        <div class="upcoming-movies">
            <h2 class="section-title"><i class="fas fa-ticket-alt"></i>Your Upcoming Shows</h2>
            <div class="movie-grid">
                <?php foreach ($upcoming_movies as $movie): ?>
                    <?php 
                    $isToday = date('Y-m-d') === $movie['booking_date'];
                    $movieDateTime = strtotime($movie['booking_date'] . ' ' . $movie['show_time']);
                    $ticketNumber = sprintf('TKT-%05d', $movie['id']);
                    ?>
                    <div class="movie-card <?php echo $isToday ? 'today-movie' : ''; ?>">
                        <div class="ticket-hole ticket-hole-top"></div>
                        <div class="ticket-hole ticket-hole-bottom"></div>
                        
                        <div class="movie-thumbnail-container">
                            <img 
                                src="<?php echo htmlspecialchars($movie['thumbnail']); ?>" 
                                alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                class="movie-thumbnail"
                            >
                            <div class="ticket-number"><?php echo $ticketNumber; ?></div>
                        </div>
                        
                        <div class="movie-info">
                            <div class="movie-header">
                                <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                            </div>
                            <div class="movie-details">
                                <p><i class="fas fa-calendar"></i> <?php echo date('l, F j, Y', strtotime($movie['booking_date'])); ?></p>
                                <p><i class="fas fa-clock"></i> <?php echo $movie['show_time']; ?></p>
                                <p><i class="fas fa-chair"></i> 
                                    <?php 
                                    $ticketText = [];
                                    if ($movie['adult_tickets']) $ticketText[] = $movie['adult_tickets'] . ' Adult';
                                    if ($movie['child_tickets']) $ticketText[] = $movie['child_tickets'] . ' Child';
                                    echo implode(', ', $ticketText);
                                    ?>
                                </p>
                                <div class="movie-countdown" id="countdown-<?php echo $movie['id']; ?>">
                                    Loading countdown...
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div id="feedback-button" class="feedback-button">
        <button onclick="toggleFeedbackForm()">
            <i class="fas fa-comment-alt"></i>
            Send Feedback
        </button>
    </div>

    <div id="feedback-form" class="feedback-form">
        <div class="feedback-header">
            <h3>Share Your Feedback</h3>
            <button onclick="toggleFeedbackForm()" class="close-button">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="userFeedbackForm" onsubmit="submitFeedback(event)">
            <textarea name="feedback" placeholder="Tell us what you think or suggest improvements..." required></textarea>
            <div class="rating">
                <span>Rate your experience:</span>
                <div class="stars">
                    <input type="radio" id="star5" name="rating" value="5" required>
                    <label for="star5"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star4" name="rating" value="4">
                    <label for="star4"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star3" name="rating" value="3">
                    <label for="star3"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star2" name="rating" value="2">
                    <label for="star2"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star1" name="rating" value="1">
                    <label for="star1"><i class="fas fa-star"></i></label>
                </div>
            </div>
            <button type="submit" class="submit-button">
                <i class="fas fa-paper-plane"></i>
                Submit Feedback
            </button>
        </form>
    </div>

    <div id="success-message" class="success-message">
        <div class="success-content">
            <i class="fas fa-check-circle success-icon"></i>
            <h3>Thank You!</h3>
            <p>Your feedback has been submitted successfully</p>
        </div>
    </div>

    <div class="notifications-panel" id="notifications-panel">
        <div class="notifications-header">
            <h3>Notifications</h3>
            <button class="mark-all-read">Mark all as read</button>
        </div>
        <div class="notification-list">
            <?php if ($messages_count > 0): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="notification-item unread">
                        <div class="notification-message"><?php echo htmlspecialchars($message['message']); ?></div>
                        <div class="notification-date">
                            <?php echo date('F j, Y g:i A', strtotime($message['sent_date'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-notifications">
                    <i class="fas fa-inbox"></i>
                    <p>No notifications yet</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="notification-footer">
            <a href="#" class="view-all-btn">View all notifications</a>
        </div>
    </div>

    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span class="toast-message">Operation completed successfully!</span>
    </div>

    <div class="menu-overlay" id="menu-overlay"></div>

    <footer>
        <p>© 2025 MovieMate. All rights reserved.</p>
    </footer>

    <script>
    function updateCountdown(movieId, movieDateTime) {
        const countdownElement = document.getElementById('countdown-' + movieId);
        
        const updateTimer = () => {
            const now = new Date().getTime();
            const distance = movieDateTime - now;

            if (distance < 0) {
                countdownElement.innerHTML = 'Movie time!';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownElement.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
        };

        updateTimer();
        setInterval(updateTimer, 1000);
    }

    <?php foreach ($upcoming_movies as $movie): ?>
    updateCountdown(
        <?php echo $movie['id']; ?>,
        <?php echo strtotime($movie['booking_date'] . ' ' . $movie['show_time']) * 1000; ?>
    );
    <?php endforeach; ?>

    function toggleFeedbackForm() {
        const form = document.getElementById('feedback-form');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

    async function submitFeedback(event) {
        event.preventDefault();
        const form = event.target;
        const submitButton = form.querySelector('.submit-button');
        const feedbackForm = document.getElementById('feedback-form');
        
        submitButton.classList.add('loading');
        
        const formData = new FormData(form);
        formData.append('feedback_action', 'submit');
        
        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showSuccessMessage();
                form.reset();
                setTimeout(() => {
                    toggleFeedbackForm();
                }, 500);
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            feedbackForm.classList.add('error');
            setTimeout(() => {
                feedbackForm.classList.remove('error');
            }, 500);
            alert('Error submitting feedback: ' + error.message);
        } finally {
            submitButton.classList.remove('loading');
        }
    }

    function showSuccessMessage() {
        const successMessage = document.getElementById('success-message');
        successMessage.classList.add('show');
        
        setTimeout(() => {
            successMessage.classList.remove('show');
        }, 3000);
    }

    document.addEventListener('click', function(event) {
        const feedbackForm = document.getElementById('feedback-form');
        const feedbackButton = document.getElementById('feedback-button');
        const successMessage = document.getElementById('success-message');
        
        if (!feedbackForm.contains(event.target) && 
            !feedbackButton.contains(event.target) && 
            !successMessage.contains(event.target)) {
            feedbackForm.style.display = 'none';
        }
    });

    const notificationBtn = document.getElementById('notifications-toggle');
    const notificationsPanel = document.getElementById('notifications-panel');
    const toast = document.getElementById('toast');
    
    notificationBtn.addEventListener('click', (e) => {
        e.preventDefault();
        notificationsPanel.classList.toggle('show');
        
        const badge = notificationBtn.querySelector('.notification-badge');
        if (badge) {
            badge.style.display = 'none';
        }
    });

    document.addEventListener('click', (e) => {
        if (!notificationBtn.contains(e.target) && !notificationsPanel.contains(e.target)) {
            notificationsPanel.classList.remove('show');
        }
    });

    const markAllRead = document.querySelector('.mark-all-read');
    
    markAllRead.addEventListener('click', () => {
        const unreadItems = document.querySelectorAll('.notification-item.unread');
        unreadItems.forEach(item => {
            item.classList.remove('unread');
        });
        
        toast.classList.add('success');
        toast.querySelector('i').className = 'fas fa-check-circle';
        toast.querySelector('.toast-message').textContent = 'All notifications marked as read';
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    });

    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    
    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        
        const icon = themeToggle.querySelector('i');
        if (body.classList.contains('dark-mode')) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
        
        localStorage.setItem('darkMode', body.classList.contains('dark-mode'));
    });

    if (localStorage.getItem('darkMode') === 'true') {
        body.classList.add('dark-mode');
        const icon = themeToggle.querySelector('i');
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    }

    // Mobile Menu Functionality
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    const menuOverlay = document.getElementById('menu-overlay');

    function toggleMobileMenu() {
        const isOpen = navLinks.classList.contains('active');
        
        if (isOpen) {
            navLinks.classList.remove('active');
            menuOverlay.classList.remove('active');
            mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            document.body.style.overflow = '';
        } else {
            navLinks.classList.add('active');
            menuOverlay.classList.add('active');
            mobileMenuBtn.innerHTML = '<i class="fas fa-times"></i>';
            document.body.style.overflow = 'hidden';
        }
    }

    mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    menuOverlay.addEventListener('click', toggleMobileMenu);

    // Close mobile menu when clicking on a link
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (navLinks.classList.contains('active')) {
                toggleMobileMenu();
            }
        });
    });

    // Close mobile menu on window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768 && navLinks.classList.contains('active')) {
            toggleMobileMenu();
        }
    });
    </script>
</body>
</html>