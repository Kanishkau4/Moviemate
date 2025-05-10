<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'moviemate';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch users data
    $usersQuery = $pdo->query("SELECT full_name, created_at FROM users");
    $users = $usersQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch movie bookings data
    $moviesQuery = $pdo->query("
        SELECT movies.title AS movie_name, COUNT(bookings.user_id) AS booked_count
        FROM movies
        LEFT JOIN bookings ON movies.id = bookings.movie_id
        GROUP BY movies.id
    ");
    $movieBookings = $moviesQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch food orders data
    $foodOrdersQuery = $pdo->query("
        SELECT food.name AS food_name, SUM(food_orders.quantity) AS quantity, food_orders.order_date
        FROM food_orders
        JOIN food ON food.id = food_orders.food_id
        GROUP BY food_orders.food_id
    ");
    $foodOrders = $foodOrdersQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch item orders data
    $itemsOrdersQuery = $pdo->query("
        SELECT items.name AS item_name, SUM(items_orders.quantity) AS quantity
        FROM items_orders
        JOIN items ON items.id = items_orders.item_id
        GROUP BY items_orders.item_id
    ");
    $itemsOrders = $itemsOrdersQuery->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the final JSON data
    $response = [
        'users' => $users,
        'movieBookings' => $movieBookings,
        'foodOrders' => $foodOrders,
        'itemsOrders' => $itemsOrders
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
