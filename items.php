<?php
// index.php
session_start();

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moviemate";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search query
$search = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $sql = "SELECT * FROM items WHERE name LIKE '%$search%' ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM items ORDER BY created_at DESC";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Merchandise Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('Images/item.jpeg');
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-top: 0px;
        }

        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .hero-content p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-container {
            max-width: 600px;
            margin: -30px auto 0;
            position: relative;
            z-index: 10;
        }

        .search-form {
            display: flex;
            background: white;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .search-input {
            flex: 1;
            border: none;
            padding: 15px 20px;
            font-size: 1rem;
            outline: none;
        }

        .search-button {
            background: #ff4444;
            border: none;
            color: white;
            padding: 0 25px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-button:hover {
            background: #e03333;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .merchandise-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .merchandise-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .merchandise-card:hover {
            transform: translateY(-5px);
        }

        .merchandise-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .merchandise-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff4444;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
        }

        .merchandise-content {
            padding: 20px;
        }

        .merchandise-name {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .merchandise-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #ff4444;
            margin-bottom: 15px;
        }

        .merchandise-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .action-btn {
            background: none;
            border: none;
            padding: 8px;
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }

        .action-btn:hover {
            color: #ff4444;
        }

        .view-details {
            text-decoration: none;
            color: inherit;
        }

        .search-results-info {
            margin: 20px 0;
            font-size: 1.1rem;
            color: #555;
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            .merchandise-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .search-container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>

    <section class="hero-section">
        <div class="hero-content">
            <h1>Movie Merchandise Store</h1>
            <p>Collect your favorite movie memorabilia and merchandise</p>
        </div>
    </section>

    <div class="search-container">
        <form action="" method="GET" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="Search for merchandise..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="search-button">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <div class="container">
        <?php if (!empty($search)): ?>
            <div class="search-results-info">
                Search results for: <strong><?php echo htmlspecialchars($search); ?></strong>
                <a href="items.php" style="margin-left: 15px; color: #ff4444;">Clear search</a>
            </div>
        <?php endif; ?>

        <div class="merchandise-grid">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
            ?>
                <div class="merchandise-card">
                    <a href="items-order.php?id=<?php echo $row['id']; ?>" class="view-details">
                        <div class="merchandise-image">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <?php if ($row['discount'] > 0): ?>
                                <div class="discount-badge">-<?php echo $row['discount']; ?>% OFF</div>
                            <?php endif; ?>
                        </div>
                        <div class="merchandise-content">
                            <div class="merchandise-name"><?php echo htmlspecialchars($row['name']); ?></div>
                            <div class="merchandise-price">
                                Rs:<?php echo number_format($row['price'], 2); ?>
                            </div>
                        </div>
                    </a>
                    <div class="merchandise-actions">
                        <button class="action-btn">
                            <i class="far fa-heart"></i>
                        </button>
                        <button class="action-btn">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                        <button class="action-btn">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p>No merchandise available</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>