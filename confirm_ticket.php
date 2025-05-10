<?php
// confirm_ticket.php
header('Content-Type: application/json');

class TicketConfirmationHandler {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function confirmTicket($bookingId, $userId) {
        try {
            // Start transaction
            $this->conn->begin_transaction();
            
            // Check if ticket already confirmed
            $checkQuery = "SELECT confirmed FROM bookings WHERE id = ? AND user_id = ?";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->bind_param("ii", $bookingId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Booking not found or doesn't belong to this user");
            }
            
            $booking = $result->fetch_assoc();
            if ($booking['confirmed']) {
                throw new Exception("Ticket already confirmed");
            }
            
            // Update booking status
            $updateQuery = "UPDATE bookings SET confirmed = 1, confirmation_date = NOW() 
                          WHERE id = ? AND user_id = ?";
            $stmt = $this->conn->prepare($updateQuery);
            $stmt->bind_param("ii", $bookingId, $userId);
            
            if (!$stmt->execute()) {
                throw new Exception("Error confirming ticket");
            }
            
            // Get all unconfirmed food orders for this user
            $foodQuery = "SELECT fo.* FROM food_orders fo
                         WHERE fo.user_id = ? 
                         AND fo.confirmed = 0 
                         AND fo.order_date <= NOW()";
            $stmt = $this->conn->prepare($foodQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $foodOrders = $stmt->get_result();
            
            // Confirm all food orders
            if ($foodOrders->num_rows > 0) {
                $updateFoodQuery = "UPDATE food_orders 
                                  SET confirmed = 1,
                                      confirmation_date = NOW()
                                  WHERE user_id = ? 
                                  AND confirmed = 0
                                  AND order_date <= NOW()";
                $stmt = $this->conn->prepare($updateFoodQuery);
                $stmt->bind_param("i", $userId);
                
                if (!$stmt->execute()) {
                    throw new Exception("Error confirming food orders");
                }
                
                $confirmedFoodOrders = $stmt->affected_rows;
            } else {
                $confirmedFoodOrders = 0;
            }
            
            // Commit transaction
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => sprintf(
                    'Ticket confirmed successfully. %d food orders confirmed.',
                    $confirmedFoodOrders
                )
            ];
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

// Handle the request
try {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!isset($data['booking_id']) || !isset($data['user_id'])) {
        throw new Exception("Both booking ID and user ID are required");
    }
    
    // Database connection (adjust credentials as needed)
    $conn = new mysqli("localhost", "root", "", "moviemate");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $handler = new TicketConfirmationHandler($conn);
    $result = $handler->confirmTicket($data['booking_id'], $data['user_id']);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>