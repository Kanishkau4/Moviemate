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

// Fetch all movies
$movies = $conn->query("SELECT * FROM movies ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie List</title>
    <link rel="stylesheet" href="movies.css">
</head>
<body>
    <div class="container">
        <h1>Movie List</h1>
        
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

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Thumbnail</th>
                        <th>Release Date</th>
                        <th>Status</th>
                        <th>Ratings</th>
                        <th>Language</th>
                        <th>Runtime</th>
                        <th>Discount</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($movie = $movies->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($movie['title']); ?></td>
                            <td>
                                <?php if ($movie['thumbnail']): ?>
                                    <img src="<?php echo $movie['thumbnail']; ?>" alt="Movie thumbnail" style="max-width: 50px;">
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($movie['release_date'])); ?></td>
                            <td><?php echo str_replace('_', ' ', ucfirst($movie['status'])); ?></td>
                            <td><?php echo $movie['ratings']; ?></td>
                            <td><?php echo $movie['language']; ?></td>
                            <td><?php echo $movie['runtime']; ?> mins</td>
                            <td><?php echo $movie['discount']; ?>%</td>
                            <td><?php echo $movie['price']; ?></td>
                            <td class="actions">
                                <a href="add-movie.php?edit=<?php echo $movie['id']; ?>" class="btn edit-btn">Edit</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $movie['id']; ?>">
                                    <button type="submit" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this movie?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <a href="add-movie.php" class="btn add-movie-btn">Add New Movie</a>
    </div>
</body>
</html>