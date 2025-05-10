<?php
// Database connection
$host = 'localhost';
$db = 'moviemate';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch bookings with user and movie details
$sql = "
    SELECT 
        b.id AS booking_id,
        u.full_name AS user_name,
        m.title AS movie_title,
        b.booking_date,
        b.show_time,
        b.adult_tickets,
        b.child_tickets,
        b.selected_seats,
        b.total_price,
        b.created_at
    FROM 
        bookings b
    JOIN 
        users u ON b.user_id = u.id
    JOIN 
        movies m ON b.movie_id = m.id
    ORDER BY 
        b.created_at DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Add the same styles from the previous page for consistency */
        :root {
            --primary: #3949ab;
            --primary-dark: #283593;
            --secondary: #5c6bc0;
            --background: #e8eaf6;
            --card: #ffffff;
            --text: #263238;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
        }

        h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--primary);
            color: white;
            font-weight: 500;
        }

        tr:hover {
            background: #f5f5f5;
        }

        .status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            text-align: center;
        }

        .status.confirmed {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status.pending {
            background: #fff3e0;
            color: #f57c00;
        }

        .status.cancelled {
            background: #ffebee;
            color: #c62828;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .action-btn.edit {
            background: var(--primary);
            color: white;
        }

        .action-btn.delete {
            background: #ff4444;
            color: white;
        }

        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-ticket-alt"></i> View Bookings</h1>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>User Name</th>
                    <th>Movie Title</th>
                    <th>Booking Date</th>
                    <th>Show Time</th>
                    <th>Adult Tickets</th>
                    <th>Child Tickets</th>
                    <th>Selected Seats</th>
                    <th>Total Price</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['booking_id']}</td>
                            <td>{$row['user_name']}</td>
                            <td>{$row['movie_title']}</td>
                            <td>{$row['booking_date']}</td>
                            <td>{$row['show_time']}</td>
                            <td>{$row['adult_tickets']}</td>
                            <td>{$row['child_tickets']}</td>
                            <td>{$row['selected_seats']}</td>
                            <td>Rs: {$row['total_price']}</td>
                            <td>{$row['created_at']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' style='text-align: center;'>No bookings found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>