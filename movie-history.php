<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "moviemate");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$query = "
    SELECT b.*, m.title, m.thumbnail 
    FROM bookings b
    JOIN movies m ON b.movie_id = m.id
    WHERE b.user_id = $user_id
    ORDER BY b.booking_date DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie History - MovieMate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Include your existing CSS variables and basic styles */
        
        .history-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .history-title {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--secondary-color);
        }

        .history-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .history-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .history-card:hover {
            transform: translateY(-5px);
        }

        .movie-thumbnail {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .movie-info {
            padding: 1rem;
        }

        .movie-info h3 {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .movie-info p {
            color: #666;
            margin: 0.3rem 0;
            font-size: 0.9rem;
        }

        .ticket-details {
            background: #f8f9fa;
            padding: 0.5rem;
            margin-top: 0.5rem;
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            .history-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="history-container">
        <h1 class="history-title">
            <i class="fas fa-ticket-alt"></i> Your Movie Booking History
        </h1>

        <div class="history-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="history-card">
                    <img src="<?php echo htmlspecialchars($row['thumbnail']); ?>" 
                         alt="<?php echo htmlspecialchars($row['title']); ?>"
                         class="movie-thumbnail">
                    <div class="movie-info">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p><i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($row['booking_date'])); ?></p>
                        <p><i class="fas fa-clock"></i> <?php echo $row['show_time']; ?></p>
                        <div class="ticket-details">
                            <p><i class="fas fa-ticket-alt"></i> Tickets:</p>
                            <?php if ($row['adult_tickets']): ?>
                                <p>Adult: <?php echo $row['adult_tickets']; ?></p>
                            <?php endif; ?>
                            <?php if ($row['child_tickets']): ?>
                                <p>Child: <?php echo $row['child_tickets']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>


</body>
</html>