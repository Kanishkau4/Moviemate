<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = 'localhost';
$dbname = 'moviemate';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}

// Function to fetch staff messages
function getStaffMessages() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT id, sender_type, sender_id, message, timestamp, admin_reply
        FROM chat_messages
        ORDER BY timestamp DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to save admin reply
function saveAdminReply($messageId, $replyText) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE chat_messages
        SET admin_reply = ?
        WHERE id = ?
    ");
    return $stmt->execute([$replyText, $messageId]);
}

// Handle the API request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getStaffMessages') {
    echo json_encode(getStaffMessages());
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sendAdminReply') {
    $messageId = $_POST['messageId'];
    $replyText = $_POST['replyText'];
    $result = saveAdminReply($messageId, $replyText);
    echo json_encode(['success' => $result]);
} else {
    echo json_encode(['error' => 'Invalid action']);
}
?>