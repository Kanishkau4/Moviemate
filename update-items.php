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
    $category = $conn->real_escape_string($_POST['category']); // Get the selected category

    // Handle file upload
    $old_file = $conn->query("SELECT image_url FROM items WHERE id=$id")->fetch_assoc()['image_url'];
    $image_url = handleFileUpload($_FILES['image'], $old_file);

    // Update query
    $sql = "UPDATE items SET 
            name='$name', 
            price=$price, 
            image_url='$image_url', 
            discount=$discount, 
            category='$category' 
            WHERE id=$id";

    if ($conn->query($sql)) {
        $_SESSION['message'] = 'Item updated successfully!';
    } else {
        $_SESSION['error'] = 'Error: ' . $conn->error;
    }

    header('Location: update-items.php');
    exit();
}


// Handle delete action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['id']);

    // Fetch and delete image file
    $result = $conn->query("SELECT image_url FROM items WHERE id=$id");
    $file = $result->fetch_assoc()['image_url'];
    if (!empty($file) && file_exists($file)) {
        unlink($file);
    }

    // Delete the item
    $sql = "DELETE FROM items WHERE id=$id";
    if ($conn->query($sql)) {
        $_SESSION['message'] = 'Item deleted successfully!';
    } else {
        $_SESSION['error'] = 'Error: ' . $conn->error;
    }

    header('Location: update-items.php');
    exit();
}

// Fetch all items
$items = $conn->query("SELECT * FROM items ORDER BY created_at DESC");

// Function to handle file upload
function handleFileUpload($file, $old_file = '') {
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    if ($file['error'] == 0) {
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
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
    <title>Manage Items</title>
    <link rel="stylesheet" href="movies.css">
    <style>
        .update-form {
            display: none;
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .update-form.active {
            display: block;
        }
    </style>
    <script>
        function toggleUpdateForm(id) {
            const forms = document.querySelectorAll('.update-form');
            forms.forEach(form => form.classList.remove('active'));
            document.getElementById('update-form-' + id).classList.add('active');
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Manage Store Items</h1>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Discount</th>
                    <th>Category</th> <!-- New Column for Category -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <?php if ($item['image_url']): ?>
                                <img src="<?php echo $item['image_url']; ?>" alt="Item Image" style="max-width: 50px;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item['discount']; ?>%</td>
                        <td><?php echo ucfirst($item['category']); ?></td> <!-- Display Category -->
                        <td>
                            <button class="btn" onclick="toggleUpdateForm(<?php echo $item['id']; ?>)">Update</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn delete-btn" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <div class="update-form" id="update-form-<?php echo $item['id']; ?>">
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">

                                    <div class="form-group">
                                        <label for="name">Item Name:</label>
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="price">Price:</label>
                                        <input type="number" name="price" value="<?php echo $item['price']; ?>" step="0.01" >
                                    </div>

                                    <div class="form-group">
                                        <label for="image">Image:</label>
                                        <input type="file" name="image" accept="image/*">
                                        <?php if ($item['image_url']): ?>
                                            <img src="<?php echo $item['image_url']; ?>" alt="Current Image" style="max-width: 100px;">
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="discount">Discount (%):</label>
                                        <input type="number" name="discount" value="<?php echo $item['discount']; ?>" step="0.01" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="category">Category:</label>
                                        <select name="category" required>
                                            <option value="t-shirt" <?php echo $item['category'] == 't-shirt' ? 'selected' : ''; ?>>T-Shirt</option>
                                            <option value="shirt" <?php echo $item['category'] == 'shirt' ? 'selected' : ''; ?>>Shirt</option>
                                            <option value="others" <?php echo $item['category'] == 'others' ? 'selected' : ''; ?>>Others</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn">Save Changes</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
