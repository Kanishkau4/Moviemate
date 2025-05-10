<?php
$servername = "localhost";
$username = "root"; // Replace with your DB username
$password = ""; // Replace with your DB password
$dbname = "moviemate";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$action = $_POST['action']; // 'update' or 'delete'
$id = $_POST['id'];

if ($action === 'delete') {
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "User deleted successfully.";
} elseif ($action === 'update') {
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    $sql = "UPDATE users SET full_name = ?, username = ?, contact_number = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $full_name, $username, $contact_number, $address, $id);
    $stmt->execute();
    echo "User updated successfully.";
}

$conn->close();
?>
