<?php
$host = 'localhost';
$dbname = 'moviemate';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Function to get feedback
function getFeedback() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            f.id,
            f.user_id,
            f.feedback_text,
            f.rating,
            f.created_at,
            u.username
        FROM user_feedback f
        JOIN users u ON f.user_id = u.id
        ORDER BY f.created_at DESC
    ");
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to save a response to feedback
function saveFeedbackResponse($feedbackId, $response) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        UPDATE user_feedback 
        SET admin_response = ?, 
            response_date = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    return $stmt->execute([$response, $feedbackId]);
}

// Function to send a message to all users
function sendMessageToAllUsers($subject, $message) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO user_messages (subject, message, sent_date)
        VALUES (?, ?, CURRENT_TIMESTAMP)
    ");
    
    return $stmt->execute([$subject, $message]);
}

// Function to get all sent messages
function getSentMessages() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT id, subject, message, sent_date
        FROM user_messages
        ORDER BY sent_date DESC
    ");
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to delete a message
function deleteMessage($messageId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        DELETE FROM user_messages
        WHERE id = ?
    ");
    
    return $stmt->execute([$messageId]);
}

// API endpoint for getting feedback
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getFeedback') {
    header('Content-Type: application/json');
    echo json_encode(getFeedback());
}

// API endpoint for saving a response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'saveResponse') {
    $feedbackId = $_POST['feedbackId'];
    $response = $_POST['response'];
    
    header('Content-Type: application/json');
    $result = saveFeedbackResponse($feedbackId, $response);
    echo json_encode(['success' => $result]);
}

// API endpoint for sending a message to all users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sendMessage') {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    header('Content-Type: application/json');
    $result = sendMessageToAllUsers($subject, $message);
    echo json_encode(['success' => $result]);
}

// API endpoint for getting sent messages
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getSentMessages') {
    header('Content-Type: application/json');
    echo json_encode(getSentMessages());
}

// API endpoint for deleting a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteMessage') {
    $messageId = $_POST['messageId'];
    
    header('Content-Type: application/json');
    $result = deleteMessage($messageId);
    echo json_encode(['success' => $result]);
}
?>