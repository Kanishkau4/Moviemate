<?php
// generate_qr.php
require_once 'qr_handler.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$conn = new mysqli("localhost", "root", "", "moviemate");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$qrHandler = new QRCodeHandler($conn);

// Get all upcoming bookings for the user
$bookings = $qrHandler->getAllUserBookings($_SESSION['user_id']);

if (empty($bookings)) {
    header('HTTP/1.1 404 Not Found');
    exit('No upcoming bookings found');
}

$format = $_GET['format'] ?? 'image';

if ($format === 'pdf') {
    // Generate PDF with all tickets
    $html = $qrHandler->generateMultipleTicketsPDF($_SESSION['user_id'], $bookings);
    
    header('Content-Type: text/html');
    header('Content-Disposition: inline; filename="movie_tickets.html"');
    echo $html;
} else {
    // If specific booking_id is provided, show just that QR code
    if (isset($_GET['booking_id'])) {
        $booking = array_filter($bookings, function($b) {
            return $b['booking_id'] == $_GET['booking_id'];
        });
        
        if (empty($booking)) {
            header('HTTP/1.1 404 Not Found');
            exit('Booking not found');
        }
        
        $booking = reset($booking);
        $qrData = json_encode([
            'booking_id' => $booking['booking_id'],
            'customer' => $booking['full_name'],
            'movie' => $booking['movie_title'],
            'date' => $booking['booking_date'],
            'time' => $booking['show_time'],
            'seats' => $booking['selected_seats'],
            'tickets' => [
                'adult' => $booking['adult_tickets'],
                'child' => $booking['child_tickets']
            ]
        ]);
        
        $qrCodeUrl = $qrHandler->generateQRCodeUrl($qrData);
        header('Location: ' . $qrCodeUrl);
    } else {
        // If no specific booking_id, show the next upcoming booking's QR code
        $nextBooking = reset($bookings);
        $qrData = json_encode([
            'booking_id' => $nextBooking['booking_id'],
            'customer' => $nextBooking['full_name'],
            'movie' => $nextBooking['movie_title'],
            'date' => $nextBooking['booking_date'],
            'time' => $nextBooking['show_time'],
            'seats' => $nextBooking['selected_seats'],
            'tickets' => [
                'adult' => $nextBooking['adult_tickets'],
                'child' => $nextBooking['child_tickets']
            ]
        ]);
        
        $qrCodeUrl = $qrHandler->generateQRCodeUrl($qrData);
        header('Location: ' . $qrCodeUrl);
    }
}

$conn->close();