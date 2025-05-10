<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moviemate";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Fetch all bookings from the database
$sql = "SELECT * FROM bookings";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking List</title>
    <link rel="stylesheet" href="movies.css"> <!-- Include the stylesheet -->
</head>
<body>

<div class="container">
    <h1>Booking List</h1>

    <?php
    if ($result->num_rows > 0) {
        echo "<div class='table-container'>";
        echo "<table>";
        echo "<tr><th>ID</th><th>User ID</th><th>Movie ID</th><th>Booking Date</th><th>Show Time</th><th>Seats</th><th>Total Price</th><th>Actions</th></tr>";

        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . $row['movie_id'] . "</td>";
            echo "<td>" . $row['booking_date'] . "</td>";
            echo "<td>" . $row['show_time'] . "</td>";
            echo "<td>" . $row['selected_seats'] . "</td>";
            echo "<td>" . $row['total_price'] . "</td>";
            echo "<td class='actions'>
                    <a href='edit_booking.php?id=" . $row['id'] . "' class='btn edit-btn'>Edit</a> | 
                    <a href='edit_booking.php?id=" . $row['id'] . "' class='btn delete-btn'>Delete</a>
                  </td>";
            echo "</tr>";
        }

        echo "</table>";
        echo "</div>";
    } else {
        echo "<div class='alert error'>No bookings found!</div>";
    }
    ?>
</div>

</body>
</html>
