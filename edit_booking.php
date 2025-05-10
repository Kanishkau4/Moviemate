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

if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];

    // Fetch the booking details based on the booking ID
    $sql = "SELECT * FROM bookings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
    } else {
        echo "Booking not found!";
        exit;
    }
} else {
    echo "Invalid booking ID!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        // Handle form submission to update the booking
        $show_time = $_POST['show_time'];
        $selected_seats = $_POST['selected_seats'];
        $total_price = $_POST['total_price'];
        $booking_date = $_POST['booking_date'];  // Capture the booking date

        $update_sql = "UPDATE bookings SET show_time = ?, selected_seats = ?, total_price = ?, booking_date = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssdsd", $show_time, $selected_seats, $total_price, $booking_date, $booking_id);
        
        if ($update_stmt->execute()) {
            echo "<div class='alert success'>Booking updated successfully!</div>";
        } else {
            echo "<div class='alert error'>Error updating booking!</div>";
        }
    }

    if (isset($_POST['delete'])) {
        // Handle the deletion of the booking
        $delete_sql = "DELETE FROM bookings WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $booking_id);
        
        if ($delete_stmt->execute()) {
            echo "<div class='alert success'>Booking deleted successfully!</div>";
            // Redirect to booking list page after deletion
            header("Location: bookings.php");
            exit;
        } else {
            echo "<div class='alert error'>Error deleting booking!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>
    <link rel="stylesheet" href="movies.css"> <!-- Include the stylesheet -->
</head>
<body>

<div class="container">
    <h1>Edit or Delete Booking</h1>

    <form action="" method="POST" class="movie-form">
        <div class="form-group">
            <label for="booking_date">Booking Date:</label>
            <input type="date" name="booking_date" id="booking_date" value="<?php echo $booking['booking_date']; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="show_time">Show Time:</label>
            <select name="show_time" id="show_time" required>
                <option value="10:00 AM" <?php if ($booking['show_time'] == '10:00 AM') echo 'selected'; ?>>10:00 AM</option>
                <option value="2:30 PM" <?php if ($booking['show_time'] == '2:30 PM') echo 'selected'; ?>>2:30 PM</option>
                <option value="6:00 PM" <?php if ($booking['show_time'] == '6:00 PM') echo 'selected'; ?>>6:00 PM</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="selected_seats">Selected Seats (e.g., 10, 12):</label>
            <input type="text" name="selected_seats" id="selected_seats" value="<?php echo $booking['selected_seats']; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="total_price">Total Price:</label>
            <input type="text" name="total_price" id="total_price" value="<?php echo $booking['total_price']; ?>" required>
        </div>

        <!-- Update and Delete Buttons -->
        <div class="form-buttons">
            <input type="submit" name="update" value="Update Booking" class="btn submit-btn">
            <input type="submit" name="delete" value="Delete Booking" class="btn delete-btn">
        </div>
    </form>
</div>

</body>
</html>
