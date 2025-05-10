<?php
// db_connection.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moviemate";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total counts
$totalBookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$totalFoodOrders = $conn->query("SELECT COUNT(*) as count FROM food_orders")->fetch_assoc()['count'];
$totalItemOrders = $conn->query("SELECT COUNT(*) as count FROM items_orders")->fetch_assoc()['count'];
$totalFoodItems = $conn->query("SELECT COUNT(*) as count FROM food")->fetch_assoc()['count'];
$totalItems = $conn->query("SELECT COUNT(*) as count FROM items")->fetch_assoc()['count'];

// Calculate total sales from all sources
$totalSalesQuery = "
    SELECT 
    (SELECT COALESCE(SUM(total_price), 0) FROM bookings) +
    (SELECT COALESCE(SUM(total_price), 0) FROM food_orders) +
    (SELECT COALESCE(SUM(total_price), 0) FROM items_orders) as total_sales";
$totalSales = $conn->query($totalSalesQuery)->fetch_assoc()['total_sales'];

$activeUsers = $conn->query("SELECT COUNT(DISTINCT id) as count FROM users")->fetch_assoc()['count'];

// Fetch most sold foods
$mostSoldFoods = $conn->query("
    SELECT f.name, SUM(fo.quantity) as total_quantity, SUM(fo.total_price) as total_sales
    FROM food_orders fo
    JOIN food f ON fo.food_id = f.id
    GROUP BY f.id
    ORDER BY total_quantity DESC
    LIMIT 5
");

// Fetch most sold items
$mostSoldItems = $conn->query("
    SELECT i.name, SUM(io.quantity) as total_quantity, SUM(io.total_price) as total_sales
    FROM items_orders io
    JOIN items i ON io.item_id = i.id
    GROUP BY i.id
    ORDER BY total_quantity DESC
    LIMIT 5
");

// Fetch recent orders
$recentOrders = $conn->query("
    SELECT b.id, m.title, b.total_price, b.created_at, b.adult_tickets + b.child_tickets as total_tickets
    FROM bookings b
    JOIN movies m ON b.movie_id = m.id
    ORDER BY b.created_at DESC
    LIMIT 4
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MovieMate Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <style>
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
        }

        .blue-gradient {
            background: linear-gradient(45deg, #4F46E5, #6366F1);
        }

        .green-gradient {
            background: linear-gradient(45deg, #10B981, #34D399);
        }

        .purple-gradient {
            background: linear-gradient(45deg, #8B5CF6, #A78BFA);
        }

        .orange-gradient {
            background: linear-gradient(45deg, #F59E0B, #FBBF24);
        }

        .badge {
            background-color: #EEF2FF;
            color: #4F46E5;
            padding: 4px 8px;
            border-radius: 9999px;
            font-size: 12px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-home {
            background: linear-gradient(45deg, #0EA5E9, #06B6D4);
            color: white;

        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(45deg, #059669, #0D9488);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-danger {
            background: linear-gradient(45deg, #EF4444, #F87171);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 p-4 sm:p-6">
    <!-- Top Navigation Bar -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">MovieMate Dashboard</h1>
        <div class="flex gap-4">
            <a href="index.php" class="btn btn-home">Home</a>
            <a href="charts.php" class="btn btn-primary">View Charts</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- First Row (Existing) -->
        <div class="card p-6 animate-fade-in" style="animation-delay: 0.1s">
            <div class="flex items-center">
                <div class="stat-icon blue-gradient">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Total Bookings</p>
                    <h3 class="text-2xl font-bold"><?php echo $totalBookings; ?>+</h3>
                </div>
            </div>
        </div>

        <div class="card p-6 animate-fade-in" style="animation-delay: 0.2s">
            <div class="flex items-center">
                <div class="stat-icon green-gradient">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Food Items</p>
                    <h3 class="text-2xl font-bold"><?php echo $totalFoodItems; ?>+</h3>
                </div>
            </div>
        </div>

        <div class="card p-6 animate-fade-in" style="animation-delay: 0.3s">
            <div class="flex items-center">
                <div class="stat-icon purple-gradient">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Total Sales</p>
                    <h3 class="text-2xl font-bold">Rs:<?php echo number_format($totalSales, 2); ?></h3>
                </div>
            </div>
        </div>

        <div class="card p-6 animate-fade-in" style="animation-delay: 0.4s">
            <div class="flex items-center">
                <div class="stat-icon orange-gradient">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Active Users</p>
                    <h3 class="text-2xl font-bold"><?php echo $activeUsers; ?>+</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- New Row (Additional Stats) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="card p-6 animate-fade-in" style="animation-delay: 0.5s">
            <div class="flex items-center">
                <div class="stat-icon blue-gradient">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Total Food Orders</p>
                    <h3 class="text-2xl font-bold"><?php echo $totalFoodOrders; ?>+</h3>
                </div>
            </div>
        </div>

        <div class="card p-6 animate-fade-in" style="animation-delay: 0.6s">
            <div class="flex items-center">
                <div class="stat-icon purple-gradient">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Total Item Orders</p>
                    <h3 class="text-2xl font-bold"><?php echo $totalItemOrders; ?>+</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Sold Foods Table -->
    <div class="card p-4 sm:p-6 mt-4 sm:mt-6">
        <h2 class="text-lg sm:text-xl font-semibold mb-4">Most Sold Foods</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Food Item</th>
                        <th class="px-4 py-2 text-left">Quantity Sold</th>
                        <th class="px-4 py-2 text-left">Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($food = $mostSoldFoods->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2"><?php echo $food['name']; ?></td>
                        <td class="px-4 py-2"><?php echo $food['total_quantity']; ?></td>
                        <td class="px-4 py-2">Rs:<?php echo number_format($food['total_sales'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Most Sold Items Table -->
    <div class="card p-4 sm:p-6 mt-4 sm:mt-6">
        <h2 class="text-lg sm:text-xl font-semibold mb-4">Most Sold Items</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Item Name</th>
                        <th class="px-4 py-2 text-left">Quantity Sold</th>
                        <th class="px-4 py-2 text-left">Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = $mostSoldItems->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2"><?php echo $item['name']; ?></td>
                        <td class="px-4 py-2"><?php echo $item['total_quantity']; ?></td>
                        <td class="px-4 py-2">Rs:<?php echo number_format($item['total_sales'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="card p-4 sm:p-6 mt-4 sm:mt-6">
        <h2 class="text-lg sm:text-xl font-semibold mb-4">Recent Bookings</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Booking ID</th>
                        <th class="px-4 py-2 text-left">Movie</th>
                        <th class="px-4 py-2 text-left">Price</th>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Tickets</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = $recentOrders->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">#<?php echo $order['id']; ?></td>
                        <td class="px-4 py-2"><?php echo $order['title']; ?></td>
                        <td class="px-4 py-2">Rs:<?php echo number_format($order['total_price'], 2); ?></td>
                        <td class="px-4 py-2"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td class="px-4 py-2"><span class="badge"><?php echo $order['total_tickets']; ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
