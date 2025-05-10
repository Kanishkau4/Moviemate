<?php
session_start();
header('Content-Type: application/json'); // Add this to return JSON

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moviemate";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['name'];
    $username = $_POST['email'];
    $contact_number = $_POST['contact'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = $_POST['address'];

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $profile_picture = null;

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_type = $_FILES['profile_picture']['type'];
        $file_data = file_get_contents($file_tmp);

        if (strpos($file_type, 'image') === false) {
            echo json_encode(['success' => false, 'message' => 'Uploaded file is not an image']);
            exit();
        }
        $profile_picture = $file_data;
    }

    $stmt = $conn->prepare("INSERT INTO users (full_name, username, contact_number, password, address, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $full_name, $username, $contact_number, $hashed_password, $address, $profile_picture);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['profile_picture'] = $profile_picture;
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Redirecting to login...',
            'redirect' => 'Login.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }

    $stmt->close();
}
$conn->close();
?>