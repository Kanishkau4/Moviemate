<?php
// config.php - Database configuration
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

// Initialize variables
$search = "";
$price_range = "";

// Define price ranges
$price_ranges = [
    'all' => 'All Prices',
    '0-100' => 'Rs 0 - 100',
    '100-200' => 'Rs 100 - 200',
    '200-500' => 'Rs 200 - 500',
    '500+' => 'Rs 500+'
];

// Check if filters are submitted
if(isset($_GET['search'])) {
    $search = $_GET['search'];
}
if(isset($_GET['price_range']) && $_GET['price_range'] !== 'all') {
    $price_range = $_GET['price_range'];
}

// Build SQL query
$sql = "SELECT * FROM food WHERE 1=1";
if(!empty($search)) {
    $sql .= " AND name LIKE '%" . $conn->real_escape_string($search) . "%'";
}
if(!empty($price_range)) {
    switch($price_range) {
        case '0-100':
            $sql .= " AND price BETWEEN 0 AND 100";
            break;
        case '100-200':
            $sql .= " AND price BETWEEN 100 AND 200";
            break;
        case '200-500':
            $sql .= " AND price BETWEEN 200 AND 500";
            break;
        case '500+':
            $sql .= " AND price >= 500";
            break;
    }
}
$sql .= " ORDER BY created_at ASC";

$result = $conn->query($sql);

// Fetch all results into an array
$foodItems = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $foodItems[] = $row;
    }
}

// Split the food items into two parts
$firstEightItems = array_slice($foodItems, 0, 8);
$remainingItems = array_slice($foodItems, 8);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Menu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Arial', sans-serif;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .filter-container {
        margin-bottom: 30px;
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .filter-form {
        display: flex;
        gap: 15px;
        width: 100%;
        max-width: 800px;
    }

    .filter-select {
        padding: 15px 20px;
        border-radius: 50px;
        border: 1px solid #ddd;
        font-size: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        background: white;
        min-width: 200px;
    }

    .filter-select:focus {
        outline: none;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        border-color: #aaa;
    }

    .search-form {
        width: 100%;
        max-width: 600px;
        position: relative;
    }
    
    .search-input {
        width: 100%;
        padding: 15px 20px;
        border-radius: 50px;
        border: 1px solid #ddd;
        font-size: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .search-input:focus {
        outline: none;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        border-color: #aaa;
    }
    
    .search-btn {
        position: absolute;
        right: 5px;
        top: 5px;
        background: #ff6b6b;
        color: white;
        border: none;
        border-radius: 50px;
        padding: 10px 20px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .search-btn:hover {
        background: #ff5252;
    }

    .food-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .food-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .food-card:hover {
        transform: translateY(-5px);
    }

    .food-link {
        text-decoration: none;
        color: inherit;
    }

    .food-link:hover {
        text-decoration: none;
    }

    .food-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .food-image img {
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
        font-size: 14px;
    }

    .food-content {
        padding: 15px;
    }

    .food-name {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .price-rating {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .price {
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }

    .rating {
        color: #ffd700;
    }

    .actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 10px 15px;
        border-top: 1px solid #eee;
    }

    .action-btn {
        background: none;
        border: none;
        padding: 8px;
        cursor: pointer;
        border-radius: 50%;
        transition: background-color 0.3s;
    }

    .action-btn:hover {
        background-color: #f5f5f5;
    }

    .promo-section {
        background: url('Images/food.jpg') no-repeat center center/cover;
        padding: 60px 40px;
        border-radius: 12px;
        margin-top: 40px;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
        animation: fadeIn 2s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .promo-content {
        max-width: 800px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        animation: slideIn 1.5s ease-in-out;
    }

    @keyframes slideIn {
        from { transform: translateY(50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .promo-text {
        flex: 1;
    }

    .snacks-tag {
        color: #ff6b6b;
        font-weight: 500;
        margin-bottom: 10px;
        animation: bounce 2s infinite;
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        60% { transform: translateY(-5px); }
    }

    .promo-title {
        font-size: 32px;
        font-weight: bold;
        line-height: 1.2;
        color: #fff;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    .discount-tag {
        background: #ffe8e8;
        color: #ff6b6b;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 500;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    @media (max-width: 768px) {
        .promo-content {
            flex-direction: column;
            text-align: center;
            gap: 20px;
        }

        .promo-title {
            font-size: 28px;
        }

        .filter-form {
            flex-direction: column;
            align-items: center;
        }
    }
    </style>
</head>
<body>
    <div class="container">
        <!-- Filter and Search Section -->
        <div class="filter-container">
            <form class="filter-form" method="GET" action="">
                <select name="price_range" class="filter-select" onchange="this.form.submit()">
                    <?php foreach($price_ranges as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>" 
                                <?php echo $price_range === $value ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="search-form">
                    <input type="text" name="search" class="search-input" 
                           placeholder="Search for food items..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <div class="food-grid">
            <?php
            foreach ($firstEightItems as $row) {
            ?>
                <div class="food-card">
                    <a href="food-order.php?id=<?php echo $row['id']; ?>" class="food-link">
                        <div class="food-image">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <?php if ($row['discount'] > 0) { ?>
                                <div class="discount-badge">-<?php echo $row['discount']; ?>%</div>
                            <?php } ?>
                        </div>
                        <div class="food-content">
                            <div class="food-name"><?php echo htmlspecialchars($row['name']); ?></div>
                            <div class="price-rating">
                                <div class="price">Rs:<?php echo number_format($row['price'], 2); ?></div>
                                <div class="rating">
                                    <?php for($i = 0; $i < 5; $i++) { ?>
                                        <i class="fas fa-star"></i>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </a>
                    <div class="actions">
                        <button class="action-btn"><i class="fas fa-heart"></i></button>
                        <button class="action-btn"><i class="fas fa-shopping-bag"></i></button>
                        <button class="action-btn"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>

        <!-- Promo Section -->
        <div class="promo-section">
            <div class="promo-content">
                <div class="promo-text">
                    <div class="snacks-tag">MOVIE SNACKS TIME</div>
                    <h2 class="promo-title">
                        Grab your favorite<br>
                        snacks and drinks<br>
                        for the show!
                    </h2>
                </div>
                <div class="discount-tag">Up to 50% OFF</div>
            </div>
        </div>

        <div class="food-grid">
            <?php
            foreach ($remainingItems as $row) {
            ?>
                <div class="food-card">
                    <a href="food-order.php?id=<?php echo $row['id']; ?>" class="food-link">
                        <div class="food-image">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <?php if ($row['discount'] > 0) { ?>
                                <div class="discount-badge">-<?php echo $row['discount']; ?>%</div>
                            <?php } ?>
                        </div>
                        <div class="food-content">
                            <div class="food-name"><?php echo htmlspecialchars($row['name']); ?></div>
                            <div class="price-rating">
                                <div class="price">Rs:<?php echo number_format($row['price'], 2); ?></div>
                                <div class="rating">
                                    <?php for($i = 0; $i < 5; $i++) { ?>
                                        <i class="fas fa-star"></i>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </a>
                    <div class="actions">
                        <button class="action-btn"><i class="fas fa-heart"></i></button>
                        <button class="action-btn"><i class="fas fa-shopping-bag"></i></button>
                        <button class="action-btn"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>