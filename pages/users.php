<?php
// Database connection
$servername = "localhost"; // Your MySQL server
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "moviemate"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete user if ID is provided
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM users WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param('i', $delete_id);
    $delete_stmt->execute();
    header('Location: ' . $_SERVER['PHP_SELF']); // Redirect back to the page after deleting
    exit;
}

// Update user if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $update_id = $_POST['update_id'];
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $password = $_POST['password'];

    // Update query
    $update_sql = "UPDATE users SET full_name = ?, username = ?, contact_number = ?, address = ?, password = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('sssssi', $full_name, $username, $contact_number, $address, $password, $update_id);
    $update_stmt->execute();
    header('Location: ' . $_SERVER['PHP_SELF']); // Redirect back to the page after updating
    exit;
}

// Fetch all users from the database
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
</head>
<body>
    <h1>User Management</h1>

    <h2>Available Users</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Username</th>
            <th>Contact Number</th>
            <th>Address</th>
            <th>Profile Picture</th>
            <th>Actions</th>
        </tr>

        <?php
        // Display each user in a table row
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr data-id='" . $row['id'] . "' 
                        data-full-name='" . htmlspecialchars($row['full_name'], ENT_QUOTES) . "' 
                        data-username='" . htmlspecialchars($row['username'], ENT_QUOTES) . "' 
                        data-contact-number='" . htmlspecialchars($row['contact_number'], ENT_QUOTES) . "' 
                        data-address='" . htmlspecialchars($row['address'], ENT_QUOTES) . "'>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['full_name'] . "</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "<td>" . $row['contact_number'] . "</td>";
                echo "<td>" . $row['address'] . "</td>";

                // Display profile picture if available
                if ($row['profile_picture']) {
                    echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['profile_picture']) . "' width='50' height='50' /></td>";
                } else {
                    echo "<td>No image</td>";
                }

                // Actions to update and delete
                echo "<td>
                        <a href='#' onclick='openUpdateForm(" . $row['id'] . ")'>Update</a> | 
                        <a href='" . $_SERVER['PHP_SELF'] . "?delete_id=" . $row['id'] . "' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>
                    </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No users found</td></tr>";
        }
        ?>
    </table>

    <!-- Update User Form (Hidden initially) -->
    <div id="update-form" style="display:none;">
        <h2>Update User</h2>
        <form action="" method="POST">
            <input type="hidden" name="update_id" id="update_id" value="">

            <label>Full Name: </label>
            <input type="text" name="full_name" id="update_full_name" required><br>

            <label>Username: </label>
            <input type="text" name="username" id="update_username" required><br>

            <label>Contact Number: </label>
            <input type="text" name="contact_number" id="update_contact_number" required><br>

            <label>Address: </label>
            <textarea name="address" id="update_address" required></textarea><br>

            <label>Password: </label>
            <input type="password" name="password" id="update_password" required><br>

            <button type="submit">Update User</button>
            <button type="button" onclick="closeUpdateForm()">Cancel</button>
        </form>
    </div>

    <script>
        // Open update form and populate the fields with existing data
        // Open update form and populate the fields with existing data
        function openUpdateForm(id) {
            var row = document.querySelector("tr[data-id='" + id + "']");
            document.getElementById("update_id").value = id;
            document.getElementById("update_full_name").value = row.getAttribute('data-full-name');
            document.getElementById("update_username").value = row.getAttribute('data-username');
            document.getElementById("update_contact_number").value = row.getAttribute('data-contact-number');
            document.getElementById("update_address").value = row.getAttribute('data-address');
            document.getElementById("update_password").value = ""; // Clear password field
            document.getElementById("update-form").style.display = "block";
        }

        // Close update form
        function closeUpdateForm() {
            document.getElementById("update-form").style.display = "none";
        }
    </script>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
