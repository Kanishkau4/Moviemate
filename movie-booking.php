<?php
// Include authentication
require_once 'auth_check.php';
requireLogin();

$movie_id = $_GET['id'] ?? null;
if (!$movie_id) {
    header('Location: movie-booking.php');
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "moviemate");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch movie details
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

// Available showtimes (you can make this dynamic from database)
$showtimes = ["10:00 AM", "2:30 PM", "6:00 PM"];

// Ticket prices (you can store these in database)
$adult_price = $movie['price'];
$child_price = $adult_price / 2;

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data) {
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Prepare the booking insertion
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, movie_id, booking_date, show_time, 
                                  adult_tickets, child_tickets, selected_seats, total_price) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            $user_id = $_SESSION['user_id'];
            $booking_date = $data['bookingDate'];
            $show_time = $data['showTime'];
            $adult_tickets = $data['quantities']['adult'];
            $child_tickets = $data['quantities']['child'];
            $selected_seats = implode(',', $data['selectedSeats']);
            $total_price = $data['totalPrice'];
            
            $stmt->bind_param("iissiiss", 
                $user_id,
                $movie_id,
                $booking_date,
                $show_time,
                $adult_tickets,
                $child_tickets,
                $selected_seats,
                $total_price
            );
            
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Send success response
            http_response_code(200);
            echo json_encode(['success' => true, 'booking_id' => $conn->insert_id]);
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}

// Add new function to get booked seats
function getBookedSeats($conn, $movie_id, $booking_date, $show_time) {
    $stmt = $conn->prepare(
        "SELECT selected_seats FROM bookings 
        WHERE movie_id = ? AND booking_date = ? AND show_time = ?"
    );
    $stmt->bind_param("iss", $movie_id, $booking_date, $show_time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $booked_seats = [];
    while ($row = $result->fetch_assoc()) {
        $seats = explode(',', $row['selected_seats']);
        $booked_seats = array_merge($booked_seats, $seats);
    }
    
    return array_unique($booked_seats);
}

// Add AJAX endpoint to get booked seats
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_booked_seats') {
    $date = $_GET['date'] ?? '';
    $time = $_GET['time'] ?? '';
    
    if ($date && $time) {
        $booked_seats = getBookedSeats($conn, $movie_id, $date, $time);
        header('Content-Type: application/json');
        echo json_encode(['booked_seats' => $booked_seats]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie['title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Main Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Arial', sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        /* Enhanced Banner Section */
        .banner {
            position: relative;
            height: 500px;
            background-size: cover;
            background-position: center;
            color: white;
        }

        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.9));
            padding: 3rem;
            display: flex;
            align-items: flex-end;
        }

        .movie-poster {
            width: 240px;
            border-radius: 12px;
            margin-right: 2.5rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
            transition: transform 0.3s ease;
        }

        .movie-poster:hover {
            transform: scale(1.03);
        }

        .movie-info {
            flex: 1;
        }

        .movie-title {
            font-size: 3rem;
            margin-bottom: 1.2rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .movie-meta {
            display: flex;
            gap: 1.2rem;
            margin-bottom: 1.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .tag {
            background: rgba(255, 255, 255, 0.15);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            backdrop-filter: blur(5px);
            transition: all 0.2s ease;
        }

        .tag:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .fa-star, .fa-star-half-alt, .far.fa-star {
            color: #ffc107;
            margin-right: 2px;
        }

        .social-links {
            display: flex;
            gap: 1.2rem;
            margin-top: 1.5rem;
        }

        .social-links .tag {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.6rem;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            border-radius: 50%;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .social-links .tag:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-3px);
        }

        /* Enhanced Content Section */
        .content {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
        }

        /* Left Column - Synopsis and Trailer */
        .left-column h2 {
            font-size: 1.8rem;
            margin-bottom: 1.2rem;
            font-weight: 600;
            color: #1a1a1a;
            position: relative;
            padding-bottom: 0.8rem;
        }

        .left-column h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: #2563eb;
            border-radius: 3px;
        }

        .synopsis {
            line-height: 1.8;
            color: #4a4a4a;
            font-size: 1.05rem;
            margin-bottom: 2.5rem;
        }

        .trailer-section {
            margin-top: 3rem;
        }

        .trailer-section iframe {
            width: 100%;
            height: 450px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Enhanced Booking Section */
        .booking-section {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            position: sticky;
            top: 2rem;
        }

        .booking-section h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            color: #1a1a1a;
            position: relative;
            padding-bottom: 0.8rem;
        }

        .booking-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: #2563eb;
            border-radius: 3px;
        }

        .booking-date {
            width: 100%;
            padding: 0.8rem 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .booking-date:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        .time-slots {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.8rem;
            margin-bottom: 1.8rem;
        }

        .time-slot {
            padding: 0.8rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f9fafb;
            position: relative;
            overflow: hidden;
        }

        .time-slot::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(37, 99, 235, 0.1);
            transform: scale(0);
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .time-slot:hover::before {
            transform: scale(1);
        }

        .time-slot:hover {
            background: #f3f4f6;
            transform: translateY(-2px);
        }

        .time-slot.selected {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }

        .time-slot.disabled {
            opacity: 0.6;
            cursor: not-allowed !important;
            position: relative;
        }

        .time-slot.disabled::after {
            content: 'Passed';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.8rem;
            color: #9ca3af;
            font-weight: 500;
        }

        .time-slot.disabled:hover {
            transform: none !important;
            background: #e5e7eb !important;
        }

        .time-slot.disabled::before {
            display: none;
        }

        .ticket-types {
            margin-bottom: 1.8rem;
            margin-top: 0.8rem;
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 10px;
        }

        .ticket-type {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .ticket-type:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .ticket-type span {
            font-weight: 500;
            color: #4b5563;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: #f3f4f6;
            border-color: #c7c7c7;
        }

        #adult-quantity, #child-quantity {
            font-weight: 600;
            min-width: 20px;
            text-align: center;
        }

        .seats-btn {
            width: 100%;
            padding: 1rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }

        .seats-btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.4);
        }

        .seats-btn:active {
            transform: translateY(0);
        }

        .total-section {
            border-top: 1px solid #e5e7eb;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
        }

        .booking-label {
            display: block;
            margin-bottom: 0.8rem;
            margin-top: 1.2rem;
            font-weight: 600;
            color: #374151;
            font-size: 1.1rem;
        }

        .total-section h3 {
            font-size: 1.8rem;
            color: #2563eb;
        }

        /* Enhanced Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            backdrop-filter: blur(5px);
            animation: fadeInModal 0.4s ease-out;
        }

        .modal-content {
            position: relative;
            background: white;
            width: 90%;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            animation: slideInUp 0.5s ease-out;
        }

        .close-modal {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 1.8rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f3f4f6;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: #e5e7eb;
            transform: rotate(90deg);
        }

        /* Enhanced Seat Styling */
        .modal-content h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            text-align: center;
            color: #1a1a1a;
        }

        .screen {
            background: linear-gradient(90deg, #ececec, #d1d1d1, #ececec);
            height: 8px;
            width: 100%;
            margin: 2.5rem 0;
            position: relative;
            border-radius: 4px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }

        .screen::before {
            content: 'SCREEN';
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.85rem;
            color: #666;
            font-weight: 500;
            background: #f3f4f6;
            padding: 0.3rem 1rem;
            border-radius: 4px;
        }

        .seats-container {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 0.8rem;
            margin: 2.5rem 0;
            animation: fadeIn 0.5s ease-in-out;
        }

        .seat {
            aspect-ratio: 1;
            background: #e5e7eb;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }

        .seat:hover {
            transform: scale(1.1);
            background: #d1d5db;
        }

        .seat.selected {
            background: #2563eb;
            color: white;
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
        }

        .seat.occupied {
            background: #6b7280;
            cursor: not-allowed;
        }

        .seat.occupied:hover {
            transform: none;
        }

        .seat-number {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1rem;
            font-weight: 600;
        }

        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 2.5rem;
            margin: 2rem 0;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .seat-sample {
            width: 20px;
            height: 20px;
            border-radius: 6px;
        }

        .seat-sample.available {
            background: #e5e7eb;
        }

        .seat-sample.selected {
            background: #2563eb;
        }

        .seat-sample.occupied {
            background: #6b7280;
        }

        /* Enhanced Payment Modal */
        .payment-form {
            background: white;
            padding: 0;
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 500;
            color: #4b5563;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        .card-grid {
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 1.5rem;
        }

        .payment-btn {
            width: 100%;
            padding: 1.2rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
            margin-top: 1rem;
        }

        .payment-btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.4);
        }

        .card-error {
            color: #dc2626;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: none;
        }

        /* Success Modal Enhancements */
        .success-content {
            background: white;
            width: 90%;
            max-width: 500px;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0,0,0,0.25);
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.6s ease-out forwards;
        }

        .success-icon {
            margin-bottom: 1.5rem;
            animation: scaleIn 0.5s ease-out 0.3s both;
        }

        .success-icon i {
            font-size: 6rem;
            color: #10b981;
            animation: pulse 1.5s infinite;
        }

        .success-content h2 {
            color: #10b981;
            margin-bottom: 2rem;
            font-size: 2.2rem;
            animation: fadeInUp 0.5s ease-out 0.5s both;
        }

        .ticket-details {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            position: relative;
            animation: fadeInUp 0.5s ease-out 0.7s both;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        .ticket-details::before,
        .ticket-details::after {
            content: '';
            position: absolute;
            background: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
        }

        .ticket-details::before {
            top: 50%;
            left: -12px;
            transform: translateY(-50%);
        }

        .ticket-details::after {
            top: 50%;
            right: -12px;
            transform: translateY(-50%);
        }

        .movie-title {
            font-weight: bold;
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px dashed rgba(255,255,255,0.3);
        }

        .ticket-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            text-align: left;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }

        .info-item i {
            font-size: 1.3rem;
            width: 25px;
            text-align: center;
        }

        /* Enhanced Animation Keyframes */
        @keyframes fadeInModal {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInUp {
            from { transform: translateY(80px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes scaleIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @keyframes fadeInUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes confettiFall {
            0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(500px) rotate(360deg); opacity: 0; }
        }

        /* Enhanced Responsive Design */
        @media (max-width: 992px) {
            .content {
                grid-template-columns: 1fr;
            }
            
            .booking-section {
                position: static;
                margin-bottom: 2rem;
            }
        }

        @media (max-width: 768px) {
            .banner {
                height: auto;
            }
            
            .banner-overlay {
                position: relative;
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 2rem 1rem;
            }
            
            .movie-poster {
                margin-right: 0;
                margin-bottom: 2rem;
                width: 200px;
            }
            
            .movie-title {
                font-size: 2.2rem;
            }
            
            .movie-meta {
                justify-content: center;
            }
            
            .social-links {
                justify-content: center;
            }
            
            .seats-container {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        @media (max-width: 576px) {
            .time-slots {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                padding: 1.5rem;
            }
            
            .card-grid {
                grid-template-columns: 1fr;
            }
            
            .seats-container {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Custom Font Import */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    </style>
</head>
<body>
    <!-- Banner Section -->
    <div class="banner" style="background-image: url('<?php echo htmlspecialchars($movie['banner']); ?>')">
        <div class="banner-overlay">
            <img src="<?php echo htmlspecialchars($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster">
            <div class="movie-info">
                <h1 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h1>
                <div class="movie-meta">
                    <span class="tag"><?php echo htmlspecialchars($movie['status']); ?></span>
                    <span class="tag"><?php echo htmlspecialchars($movie['language']); ?></span>
                    <span class="tag">
                        <?php
                        // Convert runtime from minutes to hours and minutes
                        $runtime = intval($movie['runtime']);
                        $hours = floor($runtime / 60);
                        $minutes = $runtime % 60;
                        echo $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
                        ?>
                    </span>
                    <span class="tag">
                        <?php
                        // Display star ratings
                        $rating = floatval($movie['ratings']);
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $rating) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif ($i - 0.5 <= $rating) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </span>
                </div>
                <div class="social-links">
                    <a href="#" class="tag"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="tag"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="tag"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="content">
        <div class="left-column">
            <h2>Synopsis</h2>
            <p class="synopsis"><?php echo htmlspecialchars($movie['description']); ?></p>
            
            <!-- Trailer Section -->
            <div class="trailer-section">
                <h2>Trailer</h2>
                <?php
                // Extract YouTube video ID from the URL
                $trailer_url = $movie['trailer_url'];
                $video_id = '';
                if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $trailer_url, $matches)) {
                    $video_id = $matches[1];
                }
                ?>
                <?php if ($video_id): ?>
                    <iframe src="https://www.youtube.com/embed/<?php echo $video_id; ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                <?php else: ?>
                    <p>Trailer not available.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="booking-section">
            <h2>Book Tickets</h2>
            
            <!-- Date Selection -->
            <label for="booking-date" class="booking-label">Select Date:</label>
            <input type="date" id="booking-date" class="booking-date" min="<?php echo date('Y-m-d'); ?>" required>
            
            <!-- Time Slots -->
            <label class="booking-label">Select Show Time:</label>
            <div class="time-slots">
                <?php foreach ($showtimes as $time): ?>
                    <button class="time-slot"><?php echo $time; ?></button>
                <?php endforeach; ?>
            </div>

            <!-- Ticket Selection -->
            <label class="booking-label">Select Tickets:</label>
            <div class="ticket-types">
                <div class="ticket-type">
                    <span>Adult (Rs:<?php echo number_format($adult_price, 2); ?>)</span>
                    <div class="quantity-selector">
                        <button class="quantity-btn" onclick="updateQuantity('adult', -1)">-</button>
                        <span id="adult-quantity">0</span>
                        <button class="quantity-btn" onclick="updateQuantity('adult', 1)">+</button>
                    </div>
                </div>
                <div class="ticket-type">
                    <span>Child (Rs:<?php echo number_format($child_price, 2); ?>)</span>
                    <div class="quantity-selector">
                        <button class="quantity-btn" onclick="updateQuantity('child', -1)">-</button>
                        <span id="child-quantity">0</span>
                        <button class="quantity-btn" onclick="updateQuantity('child', 1)">+</button>
                    </div>
                </div>
            </div>

            <button class="seats-btn" onclick="openSeatsModal()">Select Seats</button>

            <!-- Total Price -->
            <div class="total-section">
                <label class="booking-label">Total Amount:</label>
                <h3>Rs:<span id="total-price">0.00</span></h3>
            </div>
        </div>

        <div id="payment-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal" onclick="closePaymentModal()">×</span>
                <h2>Payment Details</h2>
                <div class="payment-form">
                    <div class="form-group">
                        <label for="card-name">Cardholder Name</label>
                        <input type="text" id="card-name" placeholder="Name on card" required>
                        <div class="card-error" id="name-error">Please enter a valid name</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="card-number">Card Number</label>
                        <input type="text" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                        <div class="card-error" id="number-error">Please enter a valid card number</div>
                    </div>
                    
                    <div class="card-grid">
                        <div class="form-group">
                            <label for="card-expiry">Expiry Date</label>
                            <input type="text" id="card-expiry" placeholder="MM/YY" maxlength="5" required>
                            <div class="card-error" id="expiry-error">Please enter a valid expiry date</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="card-cvv">CVV</label>
                            <input type="text" id="card-cvv" placeholder="123" maxlength="3" required>
                            <div class="card-error" id="cvv-error">Please enter a valid CVV</div>
                        </div>
                    </div>
                    
                    <button class="payment-btn" onclick="processPayment()">Pay Rs:<span id="payment-amount">0.00</span></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Seats Modal -->
    <div id="seats-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeSeatsModal()">×</span>
            <h2>Select Your Seats</h2>
            <div class="screen"></div>
            <div class="seats-container">
                <?php for ($i = 0; $i < 50; $i++): ?>
                    <button class="seat" data-seat="<?php echo $i; ?>">
                        <span class="seat-number"><?php echo $i + 1; ?></span>
                    </button>
                <?php endfor; ?>
            </div>
            <div class="seat-legend">
                <div class="legend-item">
                    <div class="seat-sample available"></div>
                    <span>Available</span>
                </div>
                <div class="legend-item">
                    <div class="seat-sample selected"></div>
                    <span>Selected</span>
                </div>
                <div class="legend-item">
                    <div class="seat-sample occupied"></div>
                    <span>Occupied</span>
                </div>
            </div>
            <button class="seats-btn" onclick="confirmSeats()">Confirm Selection</button>
        </div>
    </div>

    <script>
        // Ticket quantity and price calculation
        const prices = {
            adult: <?php echo $adult_price; ?>,
            child: <?php echo $child_price; ?>
        };
        let quantities = {
            adult: 0,
            child: 0
        };

        function updateQuantity(type, change) {
            const newQuantity = quantities[type] + change;
            if (newQuantity >= 0) {
                quantities[type] = newQuantity;
                document.getElementById(`${type}-quantity`).textContent = newQuantity;
                updateTotal();
            }
        }

        function updateTotal() {
            const total = (quantities.adult * prices.adult) + (quantities.child * prices.child);
            document.getElementById('total-price').textContent = total.toFixed(2);
        }

        // Replace the existing time slot click handler with this updated version
        function initializeTimeSlots() {
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.addEventListener('click', function() {
                    if (this.classList.contains('disabled')) {
                        return;
                    }
                    document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
                    this.classList.add('selected');
                    updateBookedSeats();
                });
            });
        }

        // Add this function to check and disable passed show times
        function updateTimeSlots() {
            const selectedDate = document.getElementById('booking-date').value;
            const currentDate = new Date().toISOString().split('T')[0];
            const currentTime = new Date();
            
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected', 'disabled');
                
                if (selectedDate === currentDate) {
                    const showTime = convertTo24Hour(slot.textContent);
                    const showDateTime = new Date(currentDate + ' ' + showTime);
                    
                    if (showDateTime < currentTime) {
                        slot.classList.add('disabled');
                        slot.style.cursor = 'not-allowed';
                        slot.style.backgroundColor = '#e5e7eb';
                        slot.style.color = '#9ca3af';
                    } else {
                        slot.style.cursor = 'pointer';
                        slot.style.backgroundColor = '';
                        slot.style.color = '';
                    }
                } else {
                    slot.style.cursor = 'pointer';
                    slot.style.backgroundColor = '';
                    slot.style.color = '';
                }
            });
        }

        // Helper function to convert 12-hour format to 24-hour format
        function convertTo24Hour(time12h) {
            const [time, modifier] = time12h.split(' ');
            let [hours, minutes] = time.split(':');
            
            if (hours === '12') {
                hours = '00';
            }
            
            if (modifier === 'PM') {
                hours = parseInt(hours, 10) + 12;
            }
            
            return `${hours}:${minutes}`;
        }

        // Update the date change event listener
        document.getElementById('booking-date').addEventListener('change', function() {
            const selectedDate = this.value;
            const currentDate = new Date().toISOString().split('T')[0];
            
            if (selectedDate < currentDate) {
                alert('Please select today or a future date');
                this.value = currentDate;
            }
            updateTimeSlots();
            updateBookedSeats();
        });

        // Function to fetch and update booked seats
        async function updateBookedSeats() {
            const date = document.getElementById('booking-date').value;
            const timeSlot = document.querySelector('.time-slot.selected');
            
            if (!date || !timeSlot) {
                return;
            }

            try {
                const response = await fetch(`${window.location.href}&action=get_booked_seats&date=${date}&time=${encodeURIComponent(timeSlot.textContent)}`);
                const data = await response.json();
                
                // Reset all seats
                document.querySelectorAll('.seat').forEach(seat => {
                    seat.classList.remove('occupied');
                    seat.classList.remove('selected');
                });

                // Mark booked seats as occupied
                data.booked_seats.forEach(seatId => {
                    const seat = document.querySelector(`.seat[data-seat="${seatId - 1}"]`);
                    if (seat) {
                        seat.classList.add('occupied');
                    }
                });

                // Clear selected seats set
                selectedSeats.clear();
            } catch (error) {
                console.error('Error fetching booked seats:', error);
            }
        }

        // Seat selection logic
        let selectedSeats = new Set();
        let totalTickets = 0;

        // Update seat click handler
        document.querySelectorAll('.seat').forEach(seat => {
            seat.addEventListener('click', function() {
                if (this.classList.contains('occupied')) {
                    return;
                }

                const seatId = this.dataset.seat;

                if (this.classList.contains('selected')) {
                    this.classList.remove('selected');
                    selectedSeats.delete(seatId);
                } else if (selectedSeats.size < totalTickets) {
                    this.classList.add('selected');
                    selectedSeats.add(seatId);
                } else {
                    alert(`You can only select ${totalTickets} seats.`);
                }
            });
        });

        // Update openSeatsModal function
        async function openSeatsModal() {
            if (!validateTickets()) {
                return;
            }
            
            totalTickets = quantities.adult + quantities.child;
            document.getElementById('seats-modal').style.display = 'block';
            
            await updateBookedSeats();
        }

        function closeSeatsModal() {
            document.getElementById('seats-modal').style.display = 'none';
        }

        function confirmSeats() {
            if (selectedSeats.size !== totalTickets) {
                alert(`Please select exactly ${totalTickets} seats.`);
                return;
            }
            
            // Convert selected seats to 1-based index
            const selectedSeatsArray = Array.from(selectedSeats).map(seat => parseInt(seat) + 1);
            
            // Store the selected seats in a global variable or send them directly to the payment modal
            // For example, you can store them in a hidden input or pass them to the payment modal
            // Here, we will just log them for demonstration
            console.log("Selected Seats (1-based):", selectedSeatsArray);
            
            closeSeatsModal();
            openPaymentModal();
        }

        // Payment Modal Functions
        function openPaymentModal() {
            const modal = document.getElementById('payment-modal');
            modal.style.display = 'block';
            document.getElementById('payment-amount').textContent = 
                document.getElementById('total-price').textContent;
        }

        function closePaymentModal() {
            document.getElementById('payment-modal').style.display = 'none';
        }

        // Card input formatting and validation
        document.getElementById('card-number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = value;
        });

        document.getElementById('card-expiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0,2) + '/' + value.slice(2);
            }
            e.target.value = value;
        });

        document.getElementById('card-cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });

        function validateCard() {
            let isValid = true;
            
            const name = document.getElementById('card-name').value;
            if (name.length < 3) {
                document.getElementById('name-error').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('name-error').style.display = 'none';
            }
            
            const number = document.getElementById('card-number').value.replace(/\s/g, '');
            if (number.length !== 16 || !luhnCheck(number)) {
                document.getElementById('number-error').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('number-error').style.display = 'none';
            }
            
            const expiry = document.getElementById('card-expiry').value;
            if (!isValidExpiry(expiry)) {
                document.getElementById('expiry-error').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('expiry-error').style.display = 'none';
            }
            
            const cvv = document.getElementById('card-cvv').value;
            if (cvv.length !== 3) {
                document.getElementById('cvv-error').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('cvv-error').style.display = 'none';
            }
            
            return isValid;
        }

        function luhnCheck(number) {
            let sum = 0;
            let isEven = false;
            
            for (let i = number.length - 1; i >= 0; i--) {
                let digit = parseInt(number.charAt(i));
                
                if (isEven) {
                    digit *= 2;
                    if (digit > 9) {
                        digit -= 9;
                    }
                }
                
                sum += digit;
                isEven = !isEven;
            }
            
            return sum % 10 === 0;
        }

        function isValidExpiry(expiry) {
            if (!/^\d{2}\/\d{2}$/.test(expiry)) return false;
            
            const [month, year] = expiry.split('/');
            const now = new Date();
            const currentYear = now.getFullYear() % 100;
            const currentMonth = now.getMonth() + 1;
            
            const expiryMonth = parseInt(month);
            const expiryYear = parseInt(year);
            
            if (expiryMonth < 1 || expiryMonth > 12) return false;
            if (expiryYear < currentYear) return false;
            if (expiryYear === currentYear && expiryMonth < currentMonth) return false;
            
            return true;
        }

        async function processPayment() {
            if (!validateCard()) {
                return;
            }

            const bookingDate = document.getElementById('booking-date').value;
            const selectedTimeSlot = document.querySelector('.time-slot.selected');
            
            if (!bookingDate || !selectedTimeSlot) {
                alert('Please select a date and time slot');
                return;
            }

            document.querySelector('.payment-btn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            document.querySelector('.payment-btn').disabled = true;

            // Use the updated selected seats array
            const bookingData = {
                bookingDate: bookingDate,
                showTime: selectedTimeSlot.textContent,
                quantities: quantities,
                selectedSeats: Array.from(selectedSeats).map(seat => parseInt(seat) + 1), // Convert to 1-based index
                totalPrice: parseFloat(document.getElementById('total-price').textContent)
            };

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(bookingData)
                });

                const result = await response.json();

                if (result.success) {
                    closePaymentModal();
                    showSuccessAnimation(bookingData);
                    setTimeout(() => {
                        window.location.href = `movie.php?id=${result.booking_id}`;
                    }, 4000);
                } else {
                    throw new Error(result.error || 'Booking failed');
                }
            } catch (error) {
                document.querySelector('.payment-btn').innerHTML = 'Pay Rs:<span id="payment-amount">' + 
                    document.getElementById('total-price').textContent + '</span>';
                document.querySelector('.payment-btn').disabled = false;
                alert('Error processing booking: ' + error.message);
            }
        }

        function showSuccessAnimation(bookingData) {
            if (!document.getElementById('success-modal')) {
                const successModal = document.createElement('div');
                successModal.id = 'success-modal';
                successModal.className = 'modal success-animation';
                
                successModal.innerHTML = `
                    <div class="success-content">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2>Booking Successful!</h2>
                        <div class="ticket-details">
                            <div class="movie-title">${document.querySelector('.movie-title').textContent}</div>
                            <div class="ticket-info">
                                <div class="info-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span id="success-date"></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <span id="success-time"></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-ticket-alt"></i>
                                    <span id="success-seats"></span>
                                </div>
                            </div>
                        </div>
                        <div class="confetti-container"></div>
                    </div>
                `;
                
                document.body.appendChild(successModal);
                
                const style = document.createElement('style');
                style.textContent = `
                    .success-animation {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        animation: fadeInSuccess 0.5s ease-out forwards;
                    }
                    
                    .success-content {
                        background: white;
                        width: 90%;
                        max-width: 500px;
                        border-radius: 16px;
                        padding: 2rem;
                        text-align: center;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                        position: relative;
                        overflow: hidden;
                        animation: slideInUp 0.6s ease-out forwards;
                    }
                    
                    .success-icon {
                        margin-bottom: 1rem;
                        animation: scaleIn 0.5s ease-out 0.3s both;
                    }
                    
                    .success-icon i {
                        font-size: 5rem;
                        color: #10b981;
                        animation: pulse 1.5s infinite;
                    }
                    
                    .success-content h2 {
                        color: #10b981;
                        margin-bottom: 1.5rem;
                        animation: fadeInUp 0.5s ease-out 0.5s both;
                    }
                    
                    .ticket-details {
                        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                        color: white;
                        border-radius: 12px;
                        padding: 1.5rem;
                        margin-bottom: 1rem;
                        position: relative;
                        animation: fadeInUp 0.5s ease-out 0.7s both;
                    }
                    
                    .ticket-details::before,
                    .ticket-details::after {
                        content: '';
                        position: absolute;
                        background: white;
                        width: 20px;
                        height: 20px;
                        border-radius: 50%;
                    }
                    
                    .ticket-details::before {
                        top: 50%;
                        left: -10px;
                        transform: translateY(-50%);
                    }
                    
                    .ticket-details::after {
                        top: 50%;
                        right: -10px;
                        transform: translateY(-50%);
                    }
                    
                    .movie-title {
                        font-weight: bold;
                        font-size: 1.2rem;
                        margin-bottom: 1rem;
                        padding-bottom: 1rem;
                        border-bottom: 1px dashed rgba(255,255,255,0.3);
                    }
                    
                    .ticket-info {
                        display: flex;
                        flex-direction: column;
                        gap: 0.8rem;
                        text-align: left;
                    }
                    
                    .info-item {
                        display: flex;
                        align-items: center;
                        gap: 1rem;
                    }
                    
                    .info-item i {
                        font-size: 1.2rem;
                        width: 20px;
                        text-align: center;
                    }
                    
                    .confetti-container {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        pointer-events: none;
                        z-index: -1;
                    }
                    
                    .confetti {
                        position: absolute;
                        width: 10px;
                        height: 10px;
                        opacity: 0;
                    }
                    
                    @keyframes fadeInSuccess {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    
                    @keyframes slideInUp {
                        from { transform: translateY(50px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                    
                    @keyframes scaleIn {
                        from { transform: scale(0); opacity: 0; }
                        to { transform: scale(1); opacity: 1; }
                    }
                    
                    @keyframes fadeInUp {
                        from { transform: translateY(20px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                    
                    @keyframes pulse {
                        0% { transform: scale(1); }
                        50% { transform: scale(1.1); }
                        100% { transform: scale(1); }
                    }
                    
                    @keyframes confettiFall {
                        0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
                        100% { transform: translateY(500px) rotate(360deg); opacity: 0; }
                    }
                `;
                
                document.head.appendChild(style);
            }
            
            const successModal = document.getElementById('success-modal');
            successModal.style.display = 'flex';
            
            document.getElementById('success-date').textContent = new Date(bookingData.bookingDate).toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            document.getElementById('success-time').textContent = bookingData.showTime;
            
            const seatNumbers = bookingData.selectedSeats.map(seat => parseInt(seat) + 1);
            let seatsText = `Seat${seatNumbers.length > 1 ? 's' : ''} `;
            if (seatNumbers.length <= 3) {
                seatsText += seatNumbers.join(', ');
            } else {
                seatsText += `${seatNumbers.length} seats`;
            }
            document.getElementById('success-seats').textContent = seatsText;
            
            createConfetti();
        }

        function createConfetti() {
            const container = document.querySelector('.confetti-container');
            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
            const shapes = ['circle', 'square', 'triangle'];
            
            container.innerHTML = '';
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                
                const left = Math.random() * 100;
                const color = colors[Math.floor(Math.random() * colors.length)];
                const shape = shapes[Math.floor(Math.random() * shapes.length)];
                const size = Math.random() * 10 + 5;
                
                confetti.style.left = `${left}%`;
                confetti.style.width = `${size}px`;
                confetti.style.height = `${size}px`;
                confetti.style.backgroundColor = color;
                
                if (shape === 'circle') {
                    confetti.style.borderRadius = '50%';
                } else if (shape === 'triangle') {
                    confetti.style.width = '0';
                    confetti.style.height = '0';
                    confetti.style.backgroundColor = 'transparent';
                    confetti.style.borderLeft = `${size/2}px solid transparent`;
                    confetti.style.borderRight = `${size/2}px solid transparent`;
                    confetti.style.borderBottom = `${size}px solid ${color}`;
                }
                
                const delay = Math.random() * 2;
                const duration = Math.random() * 3 + 2;
                confetti.style.animation = `confettiFall ${duration}s ease-in ${delay}s forwards`;
                
                container.appendChild(confetti);
            }
        }

        // Validate total tickets before opening seat selection
        function validateTickets() {
            const total = quantities.adult + quantities.child;
            if (total === 0) {
                alert('Please select at least one ticket');
                return false;
            }
            if (!document.querySelector('.time-slot.selected')) {
                alert('Please select a show time');
                return false;
            }
            if (!document.getElementById('booking-date').value) {
                alert('Please select a booking date');
                return false;
            }
            return true;
        }

        // Initialize when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeTimeSlots();
            // Set current date as default if no date is selected
            const dateInput = document.getElementById('booking-date');
            if (!dateInput.value) {
                dateInput.value = new Date().toISOString().split('T')[0];
            }
            updateTimeSlots();
        });
    </script>
</body>
</html>