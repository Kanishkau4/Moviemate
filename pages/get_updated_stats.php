<?php
// get_updated_stats.php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moviemate";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

// Fetch updated stats
$totalBookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$totalSales = $conn->query("SELECT SUM(total_price) as total FROM bookings")->fetch_assoc()['total'];
$activeUsers = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM bookings WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];



// Fetch recent orders for the table
$recentOrders = $conn->query("
    SELECT b.id, m.title, b.total_price, b.created_at, b.adult_tickets + b.child_tickets as total_tickets
    FROM bookings b
    JOIN movies m ON b.movie_id = m.id
    ORDER BY b.created_at DESC
    LIMIT 4
");

$orders = [];
while ($row = $recentOrders->fetch_assoc()) {
    $orders[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'total_price' => number_format($row['total_price'], 2),
        'date' => date('M d, Y', strtotime($row['created_at'])),
        'tickets' => $row['total_tickets']
    ];
}

// Prepare response data
$response = [
    'stats' => [
        'totalBookings' => $totalBookings,
        'totalSales' => number_format($totalSales, 2),
        'activeUsers' => $activeUsers
    ],
    'salesData' => $hourlyData,
    'transactionData' => $transactionData,
    'recentOrders' => $orders
];

// Close connection
$conn->close();

// Return JSON response
echo json_encode($response);