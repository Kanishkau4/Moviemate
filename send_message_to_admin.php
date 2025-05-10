<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$db = 'moviemate';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['message']) || empty($data['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$message = $data['message'];
$staffId = $data['staff_id'];

// Save the message to the database
$stmt = $conn->prepare("INSERT INTO chat_messages (sender_type, sender_id, message) VALUES (?, ?, ?)");
$senderType = 'staff'; // Staff is sending the message
$stmt->bind_param('sis', $senderType, $staffId, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'response' => 'Message received']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save message']);
}

$stmt->close();
$conn->close();
?>