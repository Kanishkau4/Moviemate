<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'moviemate';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();

function handleFileUpload($file) {
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    if ($file['error'] == 0) {
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                return $upload_path;
            }
        }
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $discount = floatval($_POST['discount']);
    $image_url = handleFileUpload($_FILES['image']);
    $sql = "INSERT INTO food (name, price, image_url, discount) VALUES ('$name', $price, '$image_url', $discount)";
    if ($conn->query($sql)) {
        $_SESSION['message'] = 'Food item added successfully!';
    } else {
        $_SESSION['error'] = 'Error: ' . $conn->error;
    }
    header('Location: add-food.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Food</title>
    <link rel="stylesheet" href="movies.css">
</head>
<body>
    <div class="container">
        <h1>Add Food Item</h1>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Food Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="image">Image:</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="discount">Discount (%):</label>
                <input type="number" id="discount" name="discount" step="0.01" >
            </div>
            <button type="submit" class="btn submit-btn">Add Food</button>
        </form>
    </div>
</body>
</html>
