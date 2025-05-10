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
    SELECT io.*, i.name, i.price, i.image_url
    FROM items_orders io
    JOIN items i ON io.item_id = i.id
    WHERE io.user_id = $user_id
    ORDER BY io.created_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merchandise Order History - MovieMate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Include the same CSS as movie-history.php -->
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
            <i class="fas fa-shopping-bag"></i> Your Merchandise Order History
        </h1>

        <div class="history-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="history-card">
                    <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                         class="movie-thumbnail">
                    <div class="movie-info">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p><i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($row['created_at'])); ?></p>
                        <p><i class="fas fa-shopping-cart"></i> Quantity: <?php echo $row['quantity']; ?></p>
                        <p><i class="fas fa-dollar-sign"></i> Total: Rs:<?php echo number_format($row['price'] * $row['quantity'], 2); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>


</body>
</html>