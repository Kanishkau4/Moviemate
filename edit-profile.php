<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'moviemate';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get current user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, username, contact_number, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $errors = [];

    // Validate inputs
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($username)) $errors[] = "Username is required";
    if (empty($contact_number)) $errors[] = "Contact number is required";
    if (empty($address)) $errors[] = "Address is required";

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['size'] > 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['profile_picture']['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
        if ($_FILES['profile_picture']['size'] > 5000000) { // 5MB limit
            $errors[] = "File size too large. Maximum size is 5MB.";
        }
    }

    // If changing password, verify current password
    if (!empty($new_password)) {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
        if (!password_verify($current_password, $user_data['password'])) {
            $errors[] = "Current password is incorrect";
        }
    }

    if (empty($errors)) {
        // Start building the query
        $query = "UPDATE users SET full_name = ?, username = ?, contact_number = ?, address = ?";
        $params = [$full_name, $username, $contact_number, $address];
        $types = "ssss";

        // Add password update if provided
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query .= ", password = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }

        // Add profile picture update if provided
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['size'] > 0) {
            $image_data = file_get_contents($_FILES['profile_picture']['tmp_name']);
            $query .= ", profile_picture = ?";
            $params[] = $image_data;
            $types .= "s";
        }

        $query .= " WHERE id = ?";
        $params[] = $user_id;
        $types .= "i";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['size'] > 0) {
                $_SESSION['profile_picture'] = $image_data;
            }
            $success_message = "Profile updated successfully!";
        } else {
            $errors[] = "Error updating profile. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MovieMate - Edit Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --background-color: #f5f6fa;
            --text-color: #2c3e50;
            --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --input-border: #e1e1e1;
            --input-focus: #3498db;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .navbar {
            background-color: var(--primary-color);
            padding: 1rem 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--card-shadow);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--secondary-color);
        }

        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .form-container {
            background:rgb(62, 84, 107);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(10px);
            transform: translateY(20px);
            opacity: 0;
            animation: slideIn 0.6s ease forwards;
        }

        @keyframes slideIn {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-size: 2.2rem;
            text-align: center;
            position: relative;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 2rem;
            opacity: 0;
            transform: translateX(-20px);
            animation: fadeIn 0.5s ease forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .form-group:nth-child(odd) {
            animation-delay: 0.2s;
        }

        .form-group:nth-child(even) {
            animation-delay: 0.4s;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 500;
            color: var(--primary-color);
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .form-group:focus-within label {
            color: var(--input-focus);
        }

        .input-container {
            position: relative;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--input-border);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--input-focus);
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .file-input-container {
            position: relative;
            overflow: hidden;
            display: inline-block;
            cursor: pointer;
        }

        .file-input-label {
            display: inline-block;
            padding: 1rem 2rem;
            background: var(--primary-color);
            color: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            background:rgb(95, 122, 149);
            transform: translateY(-2px);
        }

        input[type="file"] {
            display: none;
        }

        .btn {
            background: var(--secondary-color);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            margin-top: 2rem;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .error-message,
        .success-message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            transform: translateY(-10px);
            opacity: 0;
            animation: messageSlide 0.5s ease forwards;
        }

        @keyframes messageSlide {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .error-message {
            background: rgba(231, 76, 60, 0.1);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }

        .success-message {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .section-title {
            color: var(--primary-color);
            margin: 2.5rem 0 1.5rem;
            font-size: 1.5rem;
            position: relative;
            padding-left: 1rem;
        }

        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--input-border);
            transition: color 0.3s ease;
        }

        .form-group input:focus + .input-icon {
            color: var(--input-focus);
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 2rem;
            }

            .btn {
                padding: 0.8rem 1.5rem;
            }
        }

        /* Add glass morphism effect to form sections */
        .form-section {
            background: rgba(255, 255, 255, 0.7);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Add pulse animation to validation indicators */
        .validation-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-left: 10px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.7);
            }
            
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(52, 152, 219, 0);
            }
            
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(52, 152, 219, 0);
            }
        }
    </style>
</head>
<body>
<nav class="navbar">
        <div class="logo">MovieMate</div>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="movies.php"><i class="fas fa-film"></i> Movies</a>
            <a href="food.php"><i class="fas fa-utensils"></i> Food</a>
            <a href="items.php"><i class="fas fa-box"></i> Merchandise</a> 
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="form-container">
            <h2>Edit Your Profile</h2>
            
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <p class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (isset($success_message)): ?>
                <p class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </p>
            <?php endif; ?>

            <form action="edit-profile.php" method="POST" enctype="multipart/form-data">
                <div class="form-section">
                    <h3 class="section-title">Personal Information</h3>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <div class="input-container">
                            <input type="text" id="full_name" name="full_name" 
                                value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-container">
                            <input type="text" id="username" name="username" 
                                value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            <i class="fas fa-at input-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Contact Details</h3>
                    
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <div class="input-container">
                            <input type="tel" id="contact_number" name="contact_number" 
                                value="<?php echo htmlspecialchars($user['contact_number']); ?>" required>
                            <i class="fas fa-phone input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Profile Picture</h3>
                    
                    <div class="form-group">
                        <div class="file-input-container">
                            <label class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i> Choose New Picture
                                <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Change Password (Optional)</h3>
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="input-container">
                            <input type="password" id="current_password" name="current_password">
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="input-container">
                            <input type="password" id="new_password" name="new_password">
                            <i class="fas fa-key input-icon"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>
    </div>

    <!-- Keep the existing footer -->

    <script>
        // Add file input name display
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const label = this.previousElementSibling;
            if (fileName) {
                label.innerHTML = `<i class="fas fa-file-image"></i> ${fileName}`;
            }
        });

        // Add input validation indicators
        const inputs = document.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                const isValid = this.checkValidity();
                const indicator = this.parentElement.querySelector('.validation-indicator');
                if (indicator) {
                    indicator.style.backgroundColor = isValid ? '#2ecc71' : '#e74c3c';
                }
            });
        });
    </script>
</body>
</html>