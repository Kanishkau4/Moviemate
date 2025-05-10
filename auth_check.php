<?php
// auth_check.php
function requireLogin() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: Login.php");
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserType() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'customer';
}

function isAdmin() {
    return isLoggedIn() && getUserType() === 'admin';
}

function isStaff() {
    return isLoggedIn() && getUserType() === 'staff';
}

function isCustomer() {
    return isLoggedIn() && getUserType() === 'customer';
}

// Get user display info if logged in
function getUserDisplayInfo() {
    if (isLoggedIn()) {
        $userType = getUserType();
        
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "moviemate";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            return [
                'full_name' => $_SESSION['full_name'] ?? '',
                // Return default avatar if database connection fails
                'profile_picture' => 'Images/default-avatar.jpg',
                'user_type' => $userType
            ];
        }

        // Fetch profile picture
        $profile_picture = null;
        $sql = "SELECT profile_picture FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($picture_data);
        
        if ($stmt->fetch() && $picture_data) {
            // Encode image as base64
            $profile_picture = 'data:image/jpeg;base64,' . base64_encode($picture_data);
        } else {
            // Use default avatar if no profile picture exists
            $profile_picture = 'Images/default-avatar.jpg';
        }
        
        $stmt->close();
        $conn->close();

        // Default values for all user types
        $info = [
            'full_name' => $_SESSION['full_name'] ?? '',
            'profile_picture' => $profile_picture,
            'user_type' => $userType
        ];

        // For admin and staff, use default values if not set
        if ($userType === 'admin' || $userType === 'staff') {
            $info['full_name'] = $_SESSION['full_name'] ?? ($userType === 'admin' ? 'Admin' : 'Staff');
        }

        return $info;
    }
    return null;
}
?>