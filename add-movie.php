<?php
// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'moviemate';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();

// Create upload directory if it doesn't exist
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Function to handle file upload
function handleFileUpload($file, $old_file = '') {
    global $upload_dir;
    
    if ($file['error'] == 0) {
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Delete old file if it exists
                if ($old_file && file_exists($upload_dir . basename($old_file))) {
                    unlink($upload_dir . basename($old_file));
                }
                return $upload_path;
            }
        }
    }
    return $old_file; // Return old file path if upload fails
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add' || $_POST['action'] == 'edit') {
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            $title = $conn->real_escape_string($_POST['title']);
            $description = $conn->real_escape_string($_POST['description']);
            $release_date = $conn->real_escape_string($_POST['release_date']);
            $trailer_url = $conn->real_escape_string($_POST['trailer_url']);
            $status = $conn->real_escape_string($_POST['status']);
            $discount = floatval($_POST['discount']);
            $price = floatval($_POST['price']);
            $ratings = floatval($_POST['ratings']);
            $language = $conn->real_escape_string($_POST['language']);
            $runtime = intval($_POST['runtime']);

            // Handle file uploads
            if ($_POST['action'] == 'edit') {
                $result = $conn->query("SELECT thumbnail, banner FROM movies WHERE id=$id");
                $old_files = $result->fetch_assoc();
                $thumbnail = handleFileUpload($_FILES['thumbnail'], $old_files['thumbnail']);
                $banner = handleFileUpload($_FILES['banner'], $old_files['banner']);
            } else {
                $thumbnail = handleFileUpload($_FILES['thumbnail']);
                $banner = handleFileUpload($_FILES['banner']);
            }

            if ($_POST['action'] == 'add') {
                $sql = "INSERT INTO movies (title, description, release_date, trailer_url, thumbnail, banner, status, discount, price, ratings, language, runtime) 
                        VALUES ('$title', '$description', '$release_date', '$trailer_url', '$thumbnail', '$banner', '$status', $discount, $price, $ratings, '$language', $runtime)";
            } else {
                $sql = "UPDATE movies SET 
                        title='$title', 
                        description='$description', 
                        release_date='$release_date', 
                        trailer_url='$trailer_url', 
                        thumbnail='$thumbnail', 
                        banner='$banner', 
                        status='$status', 
                        discount=$discount, 
                        price=$price, 
                        ratings=$ratings, 
                        language='$language', 
                        runtime=$runtime 
                        WHERE id=$id";
            }

            if ($conn->query($sql)) {
                $_SESSION['message'] = ($_POST['action'] == 'add') ? 'Movie added successfully!' : 'Movie updated successfully!';
            } else {
                $_SESSION['error'] = 'Error: ' . $conn->error;
            }
        } elseif ($_POST['action'] == 'delete') {
            $id = intval($_POST['id']);
            
            // Delete associated files first
            $result = $conn->query("SELECT thumbnail, banner FROM movies WHERE id=$id");
            $files = $result->fetch_assoc();
            
            if ($files) {
                if (!empty($files['thumbnail']) && file_exists($files['thumbnail'])) {
                    unlink($files['thumbnail']);
                }
                if (!empty($files['banner']) && file_exists($files['banner'])) {
                    unlink($files['banner']);
                }
            }
            
            $sql = "DELETE FROM movies WHERE id=$id";
            
            if ($conn->query($sql)) {
                $_SESSION['message'] = 'Movie deleted successfully!';
            } else {
                $_SESSION['error'] = 'Error: ' . $conn->error;
            }
        }
    }
    header('Location: add-movie.php');
    exit();
}

// Get movie for editing
$edit_movie = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM movies WHERE id=$id");
    if ($result->num_rows > 0) {
        $edit_movie = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Management System</title>
    <link rel="stylesheet" href="movies.css">
</head>
<body>
    <div class="container">
        <h1><?php echo $edit_movie ? 'Edit Movie' : 'Add New Movie'; ?></h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert success">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="movie-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $edit_movie ? 'edit' : 'add'; ?>">
            <?php if ($edit_movie): ?>
                <input type="hidden" name="id" value="<?php echo $edit_movie['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="title">Movie Title:</label>
                <input type="text" id="title" name="title" required 
                    value="<?php echo $edit_movie ? $edit_movie['title'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo $edit_movie ? $edit_movie['description'] : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="release_date">Release Date:</label>
                <input type="date" id="release_date" name="release_date" required 
                    value="<?php echo $edit_movie ? $edit_movie['release_date'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="trailer_url">Trailer URL:</label>
                <input type="url" id="trailer_url" name="trailer_url" 
                    value="<?php echo $edit_movie ? $edit_movie['trailer_url'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="ratings">Ratings (out of 5):</label>
                <input type="number" id="ratings" name="ratings" min="0" max="10" step="0.1" required 
                    value="<?php echo $edit_movie ? $edit_movie['ratings'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="language">Language:</label>
                <input type="text" id="language" name="language" required 
                    value="<?php echo $edit_movie ? $edit_movie['language'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="runtime">Runtime (in minutes):</label>
                <input type="number" id="runtime" name="runtime" min="0" required 
                    value="<?php echo $edit_movie ? $edit_movie['runtime'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="thumbnail">Thumbnail Image:</label>
                <input type="file" id="thumbnail" name="thumbnail" accept="image/*" <?php echo $edit_movie ? '' : 'required'; ?>>
                <?php if ($edit_movie && $edit_movie['thumbnail']): ?>
                    <div class="current-image">
                        <img src="<?php echo $edit_movie['thumbnail']; ?>" alt="Current thumbnail" style="max-width: 100px;">
                        <p>Current thumbnail</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="banner">Banner Image:</label>
                <input type="file" id="banner" name="banner" accept="image/*" <?php echo $edit_movie ? '' : 'required'; ?>>
                <?php if ($edit_movie && $edit_movie['banner']): ?>
                    <div class="current-image">
                        <img src="<?php echo $edit_movie['banner']; ?>" alt="Current banner" style="max-width: 200px;">
                        <p>Current banner</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="upcoming" <?php echo ($edit_movie && $edit_movie['status'] == 'upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                    <option value="now_showing" <?php echo ($edit_movie && $edit_movie['status'] == 'now_showing') ? 'selected' : ''; ?>>Now Showing</option>
                </select>
            </div>

            <div class="form-group">
                <label for="discount">Discount (%):</label>
                <input type="number" id="discount" name="discount" min="0" max="100" step="0.01" 
                    value="<?php echo $edit_movie ? $edit_movie['discount'] : '0'; ?>">
            </div>

            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" min="0" step="0.01" required 
                    value="<?php echo $edit_movie ? $edit_movie['price'] : ''; ?>">
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn submit-btn">
                    <?php echo $edit_movie ? 'Update Movie' : 'Add Movie'; ?>
                </button>
                <?php if ($edit_movie): ?>
                    <a href="add-movie.php" class="btn cancel-btn">Cancel</a>
                <?php endif; ?>
            </div>
        </form>

        <a href="edit-movie.php" class="btn view-list-btn">View Movie List</a>
    </div>
</body>
</html>