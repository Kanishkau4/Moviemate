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

// Fetch all messages (both staff and admin replies)
$query = "
    SELECT id, sender_type, sender_id, message, admin_reply, timestamp
    FROM chat_messages
    ORDER BY timestamp ASC
";

$result = $conn->query($query);

if ($result) {
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        // Add staff message
        $messages[] = [
            'type' => 'incoming',
            'content' => $row['message'],
            'timestamp' => $row['timestamp']
        ];

        // Add admin reply if it exists
        if (!empty($row['admin_reply'])) {
            $messages[] = [
                'type' => 'outgoing',
                'content' => $row['admin_reply'],
                'timestamp' => $row['timestamp']
            ];
        }
    }
    echo json_encode(['success' => true, 'messages' => $messages]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch messages']);
}

$conn->close();
?>