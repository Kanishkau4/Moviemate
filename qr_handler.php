<?php
// qr_handler.php
class QRCodeHandler {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function generateQRCodeUrl($data) {
        $baseUrl = "https://api.qrserver.com/v1/create-qr-code/";
        $params = http_build_query([
            'size' => '400x400',
            'data' => $data,
            'format' => 'png',
            'margin' => '10'
        ]);
        
        return $baseUrl . '?' . $params;
    }
    
    public function getAllUserBookings($userId) {
        $query = "
            SELECT 
                b.id as booking_id,
                b.booking_date,
                b.show_time,
                b.adult_tickets,
                b.child_tickets,
                b.selected_seats,
                m.title as movie_title,
                u.full_name,
                ABS(DATEDIFF(DATE(b.booking_date), CURDATE())) as date_diff
            FROM bookings b
            JOIN movies m ON b.movie_id = m.id
            JOIN users u ON b.user_id = u.id
            WHERE b.user_id = ? 
            AND b.booking_date >= CURDATE()
            ORDER BY b.booking_date ASC";
            
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getFoodOrderHistory($userId, $limit = null) {
        $query = "
            SELECT 
                f.name as food_name,
                fo.size,
                fo.quantity,
                fo.order_date,
                fo.total_price
            FROM food_orders fo
            JOIN food f ON fo.food_id = f.id
            WHERE fo.user_id = ? 
            AND fo.confirmed = 0  -- Only fetch unconfirmed food orders
            ORDER BY fo.order_date DESC";
            
        if ($limit) {
            $query .= " LIMIT ?";
        }
            
        $stmt = $this->conn->prepare($query);
        if ($limit) {
            $stmt->bind_param("ii", $userId, $limit);
        } else {
            $stmt->bind_param("i", $userId);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function generateMultipleTicketsPDF($userId, $bookings) {
        // Get complete food order history
        $foodHistory = $this->getFoodOrderHistory($userId);
        
        $ticketsHtml = '';
        foreach ($bookings as $booking) {
            // Remove the part where food orders are fetched for each booking
            $foodOrdersHtml = '';
    
            // Food order history to be displayed in the QR code data
            $userFoodHistoryHtml = '';
            if (!empty($foodHistory)) {
                $userFoodHistoryHtml = '<p><strong>Your Food Order History:</strong></p><ul>';
                foreach ($foodHistory as $order) {
                    $userFoodHistoryHtml .= sprintf(
                        '<li>%s (%s) x%d - Rs:%.2f - Ordered on %s</li>',
                        htmlspecialchars($order['food_name']),
                        htmlspecialchars($order['size']),
                        $order['quantity'],
                        $order['total_price'],
                        date('Y-m-d H:i', strtotime($order['order_date']))
                    );
                }
                $userFoodHistoryHtml .= '</ul>';
            }
    
            // Generate QR code data including the user's food order history and user_id
            $qrData = json_encode([
                'user_id' => $userId, // Add the user_id here
                'booking_id' => $booking['booking_id'],
                'customer' => $booking['full_name'],
                'movie' => $booking['movie_title'],
                'date' => $booking['booking_date'],
                'time' => $booking['show_time'],
                'seats' => $booking['selected_seats'],
                'tickets' => [
                    'adult' => $booking['adult_tickets'],
                    'child' => $booking['child_tickets']
                ],
                'user_food_history' => $foodHistory // Include food history for the user here
            ]);
            
            $qrCodeUrl = $this->generateQRCodeUrl($qrData);
            
            // Format seat numbers
            $seatNumbers = $booking['selected_seats'] ? 
                htmlspecialchars($booking['selected_seats']) : 'Not assigned';
            
            // Add ticket with user food order history (no specific food orders for booking)
            $ticketsHtml .= '
                <div class="ticket">
                    <div class="header">MovieMate Ticket</div>
                    <div class="details">
                        <p><strong>Customer:</strong> ' . htmlspecialchars($booking['full_name']) . '</p>
                        <p><strong>Movie:</strong> ' . htmlspecialchars($booking['movie_title']) . '</p>
                        <p><strong>Date:</strong> ' . htmlspecialchars($booking['booking_date']) . '</p>
                        <p><strong>Time:</strong> ' . htmlspecialchars($booking['show_time']) . '</p>
                        <p><strong>Seat Numbers:</strong> ' . $seatNumbers . '</p>
                        <p><strong>Adult Tickets:</strong> ' . htmlspecialchars($booking['adult_tickets']) . '</p>
                        <p><strong>Child Tickets:</strong> ' . htmlspecialchars($booking['child_tickets']) . '</p>
                        
                        <!-- User Food Order History -->
                        <div class="food-history">' . $userFoodHistoryHtml . '</div>
                        
                        <p class="countdown"><strong>Days until show:</strong> ' . $booking['date_diff'] . '</p>
                    </div>
                    <div class="qr-code">
                        <img src="' . $qrCodeUrl . '" alt="QR Code">
                    </div>
                </div>
                <div class="page-break"></div>';
        }
        
        $html = '
            <!DOCTYPE html>
            <html>
            <head>
                <title>Movie Tickets</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; }
                    .ticket { max-width: 800px; margin: 0 auto 40px; border: 2px solid #333; padding: 20px; border-radius: 10px; page-break-inside: avoid; }
                    .header { text-align: center; font-size: 24px; margin-bottom: 20px; color: #333; }
                    .details { margin-bottom: 20px; }
                    .details p { margin: 10px 0; font-size: 16px; }
                    .food-history { margin: 20px 0; }
                    .food-history ul { margin: 10px 0; padding-left: 20px; }
                    .food-history li { margin: 5px 0; }
                    .qr-code { text-align: center; margin-top: 20px; }
                    .qr-code img { width: 200px; height: 200px; }
                    .countdown { color: #e44d26; font-weight: bold; }
                    .page-break { page-break-after: always; height: 0; margin: 0; }
                    @media print {
                        .ticket { border: 2px solid #333 !important; }
                        .page-break { height: 0; margin: 0; }
                    }
                </style>
            </head>
            <body>
                ' . $ticketsHtml . '
                <script>
                    window.onload = function() {
                        if (window.location.href.includes("format=pdf")) {
                            window.print();
                        }
                    }
                </script>
            </body>
            </html>';
        
        return $html;
    }
    
}
