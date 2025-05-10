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

// Handle update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $discount = floatval($_POST['discount']);

    $old_file = $conn->query("SELECT image_url FROM food WHERE id=$id")->fetch_assoc()['image_url'];
    $image_url = handleFileUpload($_FILES['image'], $old_file);

    $sql = "UPDATE food SET 
            name='$name', 
            price=$price, 
            image_url='$image_url', 
            discount=$discount 
            WHERE id=$id";

    if ($conn->query($sql)) {
        $_SESSION['message'] = 'Food item updated successfully!';
    } else {
        $_SESSION['error'] = 'Error: ' . $conn->error;
    }

    header('Location: update-food.php');
    exit();
}

// Handle delete form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['id']);

    // Fetch the image URL for deletion
    $result = $conn->query("SELECT image_url FROM food WHERE id=$id");
    $file = $result->fetch_assoc()['image_url'];

    // Delete the image file if it exists
    if (!empty($file) && file_exists($file)) {
        unlink($file);
    }

    // Delete the food item from the database
    $sql = "DELETE FROM food WHERE id=$id";

    if ($conn->query($sql)) {
        $_SESSION['message'] = 'Food item deleted successfully!';
    } else {
        $_SESSION['error'] = 'Error: ' . $conn->error;
    }

    header('Location: update-food.php');
    exit();
}

// Handle fetching food data for the update form
$edit_food = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM food WHERE id=$edit_id");
    $edit_food = $result->fetch_assoc();
}

// Fetch all food items
$food_items = $conn->query("SELECT * FROM food ORDER BY created_at DESC");

function handleFileUpload($file, $old_file = '') {
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
                if ($old_file && file_exists($old_file)) unlink($old_file);
                return $upload_path;
            }
        }
    }
    return $old_file;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Food</title>
    <link rel="stylesheet" href="movies.css">
</head>
<body>
    <div class="container">
        <h1>Update or Delete Food Items</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <h2>Food List</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Discount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($food = $food_items->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($food['name']); ?></td>
                        <td>$<?php echo number_format($food['price'], 2); ?></td>
                        <td>
                            <?php if ($food['image_url']): ?>
                                <img src="<?php echo $food['image_url']; ?>" alt="Food Image" style="max-width: 50px;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo $food['discount']; ?>%</td>
                        <td>
                            <a href="update-food.php?edit=<?php echo $food['id']; ?>" class="btn edit-btn">Update</a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $food['id']; ?>">
                                <button type="submit" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this food item?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($edit_food): ?>
            <h2>Edit Food Item</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo $edit_food['id']; ?>">

                <div class="form-group">
                    <label for="name">Food Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_food['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" value="<?php echo $edit_food['price']; ?>" step="0.01" >
                </div>

                <div class="form-group">
                    <label for="image">Image:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if ($edit_food['image_url']): ?>
                        <img src="<?php echo $edit_food['image_url']; ?>" alt="Current Image" style="max-width: 100px;">
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="discount">Discount (%):</label>
                    <input type="number" id="discount" name="discount" value="<?php echo $edit_food['discount']; ?>" step="0.01" required>
                </div>

                <button type="submit" class="btn">Update Food</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
