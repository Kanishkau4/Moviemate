<?php
// food-order.php
require_once 'auth_check.php';
requireLogin();

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moviemate";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}

// Modified food query section
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$food = null;

if ($id > 0) {
    $sql = "SELECT * FROM food WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $food = $result->fetch_assoc();
}

// Initialize prices array only if food exists
$prices = [
    'Small' => isset($food['price']) ? (float)$food['price'] : 0,
    'Large' => isset($food['price']) ? (float)$food['price'] * 1.5 : 0
];

// Handle remove from cart
if (isset($_POST['remove_from_cart'])) {
    $index = (int)$_POST['remove_from_cart'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        
        $cartTotal = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cartTotal += $item['total'];
        }

        $response = [
            'success' => true,
            'cartCount' => count($_SESSION['cart']),
            'cartTotal' => $cartTotal,
            'cartHtml' => getCartHtml()
        ];
        echo json_encode($response);
        exit();
    }
}

// Handle quantity updates
if (isset($_POST['update_quantity']) && isset($_POST['index']) && isset($_POST['change'])) {
    $index = (int)$_POST['index'];
    $change = (int)$_POST['change'];
    
    if (isset($_SESSION['cart'][$index])) {
        $newQuantity = $_SESSION['cart'][$index]['quantity'] + $change;
        
        if ($newQuantity < 1) {
            unset($_SESSION['cart'][$index]);
        } else {
            $_SESSION['cart'][$index]['quantity'] = $newQuantity;
            $_SESSION['cart'][$index]['total'] = 
                $_SESSION['cart'][$index]['price'] * $newQuantity;
        }
        
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        
        $cartTotal = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cartTotal += $item['total'];
        }

        $response = [
            'success' => true,
            'cartCount' => count($_SESSION['cart']),
            'cartTotal' => $cartTotal,
            'cartHtml' => getCartHtml()
        ];
        echo json_encode($response);
        exit();
    }
}

function getCartHtml() {
    $cartTotal = 0;
    ob_start();
    ?>
    <div class="cart-dropdown-header">
        <h3>Your Cart</h3>
        <button class="close-btn" onclick="toggleCart()">×</button>
    </div>
    <?php
    if (!empty($_SESSION['cart'])):
        foreach ($_SESSION['cart'] as $index => $item):
            $cartTotal += isset($item['total']) ? (float)$item['total'] : 0;
            $imageUrl = isset($item['image_url']) ? $item['image_url'] : 'Images/default-food.jpg';
    ?>
            <div class="cart-item" data-index="<?php echo $index; ?>">
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                     class="cart-item-image">
                <div class="cart-item-details">
                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                    <p>Size: <?php echo htmlspecialchars($item['size']); ?></p>
                    <div class="cart-item-quantity">
                        <button onclick="updateCartQuantity(<?php echo $index; ?>, -1)">-</button>
                        <span><?php echo $item['quantity']; ?></span>
                        <button onclick="updateCartQuantity(<?php echo $index; ?>, 1)">+</button>
                    </div>
                </div>
                <div class="cart-item-actions">
                    <div class="cart-item-price">
                        Rs.<?php echo number_format($item['total'], 2); ?>
                    </div>
                    <button class="remove-btn" onclick="removeFromCart(<?php echo $index; ?>)">
                        Remove
                    </button>
                </div>
            </div>
    <?php
        endforeach;
    ?>
        <div class="cart-total">
            <strong>Cart Total: Rs.<?php echo number_format($cartTotal, 2); ?></strong>
            <button onclick="showPaymentModal()" class="btn btn-primary">Proceed to Payment</button>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Your cart is empty</p>
            <a href="food.php" class="btn btn-primary" style="margin-top: 10px;">Browse Menu</a>
        </div>
    <?php endif;
    return ob_get_clean();
}

// Handle creating pending order from cart
if (isset($_POST['create_pending_order_from_cart'])) {
    if (!empty($_SESSION['cart'])) {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['total'];
        }

        $_SESSION['pending_order'] = [
            'type' => 'cart',
            'items' => $_SESSION['cart'],
            'total' => $total
        ];

        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
}

// Handle Add to Cart
if (isset($_POST['add_to_cart']) && $food && isset($food['price'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size = isset($_POST['size']) ? $_POST['size'] : 'Small';
    $price = $prices[$size];
    $total = $price * $quantity;

    $cartItem = [
        'id' => $id,
        'name' => $food['name'],
        'price' => $price,
        'quantity' => $quantity,
        'size' => $size,
        'total' => $total,
        'image_url' => $food['image_url'] // Added image_url
    ];

    $_SESSION['cart'][] = $cartItem;
    
    $_SESSION['messages'][] = "Item successfully added to cart!";
    header("Location: food-order.php?id=$id&added=1");
    exit();
}

// Initialize cart total
$cartTotal = 0;

// Calculate cart total
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartTotal += isset($item['total']) ? (float)$item['total'] : 0;
    }
}

// Handle Buy Now
if (isset($_POST['buy_now']) && $food && isset($food['price'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size = isset($_POST['size']) ? $_POST['size'] : 'Small';
    $price = $prices[$size];
    $total = $price * $quantity;

    $_SESSION['pending_order'] = [
        'type' => 'single',
        'item_id' => $id,
        'quantity' => $quantity,
        'size' => $size,
        'price' => $price,
        'total' => $total
    ];

    error_log("Pending order set: " . print_r($_SESSION['pending_order'], true));

    header("Location: food-order.php?id=$id&show_payment=1");
    exit();
}

// Handle payment processing
if (isset($_POST['process_payment'])) {
    if (isset($_SESSION['pending_order'])) {
        $user_id = $_SESSION['user_id'];
        $pending_order = $_SESSION['pending_order'];

        if ($pending_order['type'] === 'cart') {
            $success = true;
            foreach ($pending_order['items'] as $item) {
                $sql = "INSERT INTO food_orders (user_id, food_id, size, quantity, total_price) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisid", 
                    $user_id, 
                    $item['id'],
                    $item['size'],
                    $item['quantity'],
                    $item['total']
                );

                if (!$stmt->execute()) {
                    $success = false;
                    break;
                }
            }

            if ($success) {
                $_SESSION['cart'] = [];
                unset($_SESSION['pending_order']);
                $_SESSION['messages'][] = "Order placed successfully!";
                header("Location: food-order.php?ordered=1");
                exit();
            }
        } else {
            $sql = "INSERT INTO food_orders (user_id, food_id, size, quantity, total_price) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisid", 
                $user_id, 
                $pending_order['item_id'],
                $pending_order['size'],
                $pending_order['quantity'],
                $pending_order['total']
            );

            if ($stmt->execute()) {
                unset($_SESSION['pending_order']);
                $_SESSION['messages'][] = "Order placed successfully!";
                header("Location: food-order.php?ordered=1");
                exit();
            }
        }

        $_SESSION['messages'][] = "Error processing order. Please try again.";
        header("Location: food-order.php");
        exit();
    } else {
        $_SESSION['messages'][] = "No pending order found. Please try again.";
        header("Location: food-order.php");
        exit();
    }
}

// Get user display info
$userInfo = getUserDisplayInfo();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $food ? htmlspecialchars($food['name']) : 'Food Store'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        .header {
            background: white;
            padding: 0.8rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .header-content {
            max-width: 1300px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.6rem;
            font-weight: 700;
            color: #333;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .header-icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-menu {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            background: rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .user-menu:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        .user-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
            border: 2px solid #ff6b6b;
            transition: transform 0.3s ease;
        }

        .user-menu:hover .user-icon {
            transform: scale(1.1);
        }

        .user-menu span {
            color: #333;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            min-width: 180px;
            padding: 8px 0;
            display: none;
            z-index: 1001;
        }

        .user-menu:hover .user-dropdown {
            display: block;
        }

        .user-dropdown a {
            display: block;
            padding: 6px 18px;
            color: #333;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .user-dropdown a:hover {
            background: #f5f5f5;
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
            padding: 0.4rem;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .cart-icon:hover {
            background: rgba(0, 0, 0, 0.1);
            transform: scale(1.1);
        }

        .cart-icon i {
            color: #ff6b6b;
            font-size: 1.3rem;
        }

        .cart-count {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ff6b6b;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 2px solid white;
        }

        .container {
            max-width: 1200px;
            margin: 60px auto 0;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .text-center {
            text-align: center;
        }

        .py-8 {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .text-2xl {
            font-size: 1.5rem;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-green-600 {
            color: #16a34a;
        }

        .text-gray-600 {
            color: #4b5563;
        }

        .product-image {
            border-radius: 8px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .product-info {
            padding: 20px;
        }

        .product-title {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .rating {
            color: #ffd700;
            margin-bottom: 15px;
        }

        .fas.fa-star {
            color: #ffd700;
        }

        .fas.fa-star-half-alt {
            color: #ffd700;
        }

        .availability {
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .price {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .size-selector {
            margin-bottom: 20px;
        }

        .size-options {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .size-option {
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }

        .size-option.active {
            border-color: #ff4444;
            color: #ff4444;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background: #ff6b6b;
            color: white;
            margin-right: 10px;
        }

        .btn-secondary {
            background: #333;
            color: white;
        }

        .wishlist {
            margin-top: 20px;
            color: #666;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .wishlist i {
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .wishlist.active {
            color: #ff4444;
        }

        .wishlist.active i {
            transform: scale(1.2);
            color: #ff4444;
        }

        @keyframes heartBeat {
            0% { transform: scale(1); }
            25% { transform: scale(1.3); }
            50% { transform: scale(1); }
            75% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }

        .wishlist i.beating {
            animation: heartBeat 1s ease-in-out;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .original-price {
            font-size: 1.5rem;
            color: #999;
            text-decoration: line-through;
            margin-left: 10px;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
        }

        .cart-dropdown {
            position: absolute;
            right: 20px;
            top: 50px;
            width: 350px;
            background: white;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            border-radius: 12px;
            padding: 0;
            display: none;
            z-index: 9999;
            max-height: 500px;
            overflow-y: auto;
            transform: translateY(-10px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .cart-dropdown.show {
            display: block;
            transform: translateY(0);
            opacity: 1;
        }

        .cart-dropdown-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }

        .cart-dropdown-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        .cart-dropdown-header .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #666;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .cart-dropdown-header .close-btn:hover {
            color: #ff4444;
        }

        .cart-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s ease;
            position: relative;
        }

        .cart-item:hover {
            background: #f8f9fa;
        }

        .cart-item-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-details h4 {
            font-size: 0.95rem;
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
        }

        .cart-item-details p {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 3px;
        }

        .cart-item-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
        }

        .cart-item-price {
            font-size: 0.95rem;
            font-weight: 600;
            color: #ff6b6b;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: #666;
        }

        .cart-item-quantity button {
            background: #f0f0f0;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }

        .cart-item-quantity button:hover {
            background: #e0e0e0;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #ff4444;
            font-size: 0.85rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }

        .remove-btn:hover {
            opacity: 1;
        }

        .cart-total {
            padding: 15px 20px;
            border-top: 1px solid #f0f0f0;
            background: #f8f9fa;
            border-radius: 0 0 12px 12px;
            position: sticky;
            bottom: 0;
        }

        .cart-total strong {
            font-size: 1rem;
            color: #333;
            display: block;
            margin-bottom: 15px;
        }

        .empty-cart {
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }

        .empty-cart i {
            font-size: 2rem;
            color: #ccc;
            margin-bottom: 10px;
        }

        .cart-dropdown::-webkit-scrollbar {
            width: 6px;
        }

        .cart-dropdown::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 0 12px 12px 0;
        }

        .cart-dropdown::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        .cart-dropdown::-webkit-scrollbar-thumb:hover {
            background: #bbb;
        }

        .payment-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1001;
        }

        .payment-content {
            background: white;
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .success-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.7);
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 1000;
            width: 90%;
            max-width: 400px;
        }

        .success-popup.show {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, -50%) scale(1);
        }

        .animation-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 25px;
            position: relative;
        }

        .cart-animation {
            animation: cartBounce 1s ease;
        }

        .cart-animation svg {
            width: 100%;
            height: 100%;
        }

        .cart-item-drop {
            position: absolute;
            width: 25px;
            height: 25px;
            background: #ff6b6b;
            border-radius: 50%;
            top: -20px;
            left: 60px;
            animation: dropToCart 0.7s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards 0.3s;
        }

        .order-animation {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #4CAF50;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulseGrow 1s ease;
        }

        .order-animation svg {
            width: 60px;
            height: 60px;
            stroke: white;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            animation: drawCheck 0.8s ease-in-out forwards;
        }

        .remove-animation {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .remove-animation .item {
            width: 60px;
            height: 60px;
            background: #ff4444;
            border-radius: 8px;
            animation: shrinkFade 0.7s ease-in-out forwards;
        }

        .remove-animation svg {
            position: absolute;
            width: 80px;
            height: 80px;
            stroke: #555;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            animation: removeX 0.6s ease-in-out forwards 0.2s;
        }

        .default-animation {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #4CAF50;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: bounce 1s ease;
        }

        .default-animation svg {
            width: 60px;
            height: 60px;
            fill: none;
            stroke: white;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .progress-bar {
            width: 100%;
            height: 4px;
            background: #f0f0f0;
            border-radius: 2px;
            margin-top: 20px;
            overflow: hidden;
        }

        .progress-bar .progress {
            width: 100%;
            height: 100%;
            background: #4CAF50;
            animation: progress 3s linear forwards;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
        }

        .overlay.show {
            opacity: 1;
            visibility: visible;
        }

        @keyframes cartBounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-15px); }
            60% { transform: translateY(-7px); }
        }

        @keyframes dropToCart {
            0% { transform: translateY(0); opacity: 1; }
            70% { opacity: 1; }
            100% { transform: translateY(60px); opacity: 0; }
        }

        @keyframes pulseGrow {
            0% { transform: scale(0.7); opacity: 0.7; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes drawCheck {
            0% { stroke-dasharray: 100; stroke-dashoffset: 100; }
            100% { stroke-dasharray: 100; stroke-dashoffset: 0; }
        }

        @keyframes shrinkFade {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(0.5); opacity: 0; }
        }

        @keyframes removeX {
            0% { opacity: 0; transform: scale(0.5); }
            50% { opacity: 1; transform: scale(1.1); }
            100% { opacity: 1; transform: scale(1); }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }

        @keyframes progress {
            0% { width: 100%; }
            100% { width: 0%; }
        }
    </style>
</head>
<body>
<header class="header">
    <div class="header-content">
        <div class="logo">Food Store</div>
        <div class="header-icons">
            <div class="user-menu">
                <?php if ($userInfo && isset($userInfo['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($userInfo['profile_picture']); ?>" 
                        alt="Profile" 
                        class="user-icon" 
                        onerror="this.onerror=null; this.src='Images/default-avatar.jpg'">
                <?php else: ?>
                    <i class="fas fa-user-circle fa-2x" style="color: #ff6b6b;"></i>
                <?php endif; ?>
                <span><?php echo isset($userInfo['full_name']) ? htmlspecialchars($userInfo['full_name']) : 'Guest'; ?></span>
                <div class="user-dropdown">
                    <a href="customer.php">Profile</a>
                    <a href="food-history.php">My Orders</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
            <div class="cart-icon" onclick="toggleCart()">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
            </div>
        </div>
    </div>

    <div class="cart-dropdown" id="cartDropdown">
        <?php echo getCartHtml(); ?>
    </div>
</header>

<div class="payment-modal" id="paymentModal" style="display: <?php echo isset($_GET['show_payment']) ? 'block' : 'none'; ?>">
    <div class="payment-content">
        <h2>Payment Information</h2>
        <form id="paymentForm" method="POST">
            <div class="form-group">
                <label>Card Number</label>
                <input type="text" required pattern="\d{16}" placeholder="1234 5678 9012 3456">
            </div>
            <div class="form-group">
                <label>Expiry Date</label>
                <input type="text" required pattern="\d{2}/\d{2}" placeholder="MM/YY">
            </div>
            <div class="form-group">
                <label>CVV</label>
                <input type="text" required pattern="\d{3}" placeholder="123">
            </div>
            <button type="submit" name="process_payment" class="btn btn-primary">Complete Order</button>
            <button type="button" onclick="hidePaymentModal()" class="btn btn-secondary">Cancel</button>
        </form>
    </div>
</div>

<div class="overlay" id="overlay"></div>
<div class="success-popup" id="successPopup">
    <div class="animation-container" id="animationContainer">
    </div>
    <h2 id="successMessage">Success!</h2>
    <div class="progress-bar">
        <div class="progress"></div>
    </div>
</div>

<?php if ($food): ?>
<div class="container">
    <div class="product-image">
        <img src="<?php echo htmlspecialchars($food['image_url']); ?>" alt="<?php echo htmlspecialchars($food['name']); ?>">
    </div>
    
    <div class="product-info">
        <h1 class="product-title"><?php echo htmlspecialchars($food['name']); ?></h1>
        
        <div class="rating">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
            <i class="far fa-star"></i>
        </div>

        <div class="availability">
            <i class="fas fa-check-circle"></i> in stock
        </div>

        <div class="price">
            Rs:<?php echo number_format($food['price'], 2); ?>
            <?php if (isset($food['discount']) && $food['discount'] > 0): ?>
                <span class="original-price">
                    Rs:<?php echo number_format($food['price'] * (1 + $food['discount']/100), 2); ?>
                </span>
            <?php endif; ?>
        </div>

        <p class="description">
            Enjoy our delicious <?php echo htmlspecialchars($food['name']); ?>, crafted with care and made from the finest ingredients. 
            Whether you're looking for a refreshing drink, a hearty meal, or a sweet treat, our <?php echo htmlspecialchars($food['name']); ?> 
            is the perfect choice. Order now and treat yourself to a delightful experience delivered straight to your door!
        </p>

        <form action="" method="POST">
            <div class="size-selector">
                <label>Size: </label>
                <div class="size-options">
                    <label class="size-option active">
                        <input type="radio" name="size" value="Small" checked hidden>
                        Small
                    </label>
                    <label class="size-option">
                        <input type="radio" name="size" value="Large" hidden>
                        Large
                    </label>
                </div>
            </div>

            <div class="quantity-selector">
                <label>Quantity:</label>
                <button type="button" class="btn" onclick="updateQuantity(-1)">-</button>
                <input type="number" name="quantity" value="1" min="1" class="quantity-input" id="quantity" onchange="updatePrice()">
                <button type="button" class="btn" onclick="updateQuantity(1)">+</button>
            </div>

            <div class="action-buttons">
                <button type="submit" name="add_to_cart" class="btn btn-primary">Add to cart</button>
                <button type="submit" name="buy_now" class="btn btn-secondary">Buy it now</button>
            </div>
        </form>

        <div class="wishlist" onclick="toggleWishlist(this)">
            <i class="far fa-heart"></i> Add to Wishlist
        </div>
    </div>
</div>
<?php else: ?>
<div class="container text-center py-8">
    <?php if (isset($_GET['ordered']) && $_GET['ordered'] == 1): ?>
        <h2 class="text-2xl font-bold text-green-600 mb-4">Order Completed Successfully!</h2>
        <p class="mb-4">Thank you for your order.</p>
        <a href="food.php" class="btn btn-primary">Return to Menu</a>
    <?php else: ?>
        <h2 class="text-2xl font-bold text-gray-600 mb-4">No Item Selected</h2>
        <p class="mb-4">Please select a food item from our menu.</p>
        <a href="food.php" class="btn btn-primary">View Menu</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
    function updatePrice() {
        <?php if ($food): ?>
        const size = document.querySelector('input[name="size"]:checked').value;
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const basePrice = <?php echo isset($food['price']) ? (float)$food['price'] : 0; ?>;
        const multiplier = size === 'Large' ? 1.5 : 1;
        
        const totalPrice = basePrice * multiplier * quantity;
        document.querySelector('.price').innerHTML = 
            `Rs:${totalPrice.toFixed(2)}`;
        <?php endif; ?>
    }

    function updateQuantity(change) {
        <?php if ($food): ?>
        const input = document.getElementById('quantity');
        const newValue = parseInt(input.value) + change;
        if (newValue >= 1) {
            input.value = newValue;
            updatePrice();
        }
        <?php endif; ?>
    }

    <?php if ($food): ?>
    document.querySelectorAll('.size-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.size-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
        });
    });
    <?php endif; ?>

    function toggleCart() {
        const dropdown = document.getElementById('cartDropdown');
        dropdown.classList.toggle('show');
    }

    function removeFromCart(index) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "food-order.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    updateCartInfo(response.cartCount, response.cartTotal);
                    document.getElementById('cartDropdown').innerHTML = response.cartHtml;
                    showAnimatedMessage("Item removed from cart", "remove");
                } else {
                    showAnimatedMessage("Failed to remove item", "default");
                }
            }
        };
        xhr.send("remove_from_cart=" + index);
    }

    function updateCartQuantity(index, change) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "food-order.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    updateCartInfo(response.cartCount, response.cartTotal);
                    document.getElementById('cartDropdown').innerHTML = response.cartHtml;
                    showAnimatedMessage("Cart updated successfully", "cart");
                } else {
                    showAnimatedMessage("Failed to update cart", "default");
                }
            }
        };
        xhr.send(`update_quantity=1&index=${index}&change=${change}`);
    }

    function updateCartInfo(cartCount, cartTotal) {
        cartTotal = cartTotal || 0;
        const formattedCartTotal = cartTotal.toFixed(2);
        document.querySelector('.cart-count').innerText = cartCount;
        const cartTotalElement = document.querySelector('.cart-total');
        if (cartTotalElement) {
            cartTotalElement.innerText = "Cart Total: Rs." + formattedCartTotal;
        }
    }

    function showPaymentModal() {
        fetch('food-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'create_pending_order_from_cart=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('paymentModal').style.display = 'block';
            } else {
                showAnimatedMessage("Error creating order. Please try again.", "default");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAnimatedMessage("Error creating order. Please try again.", "default");
        });
    }

    function hidePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_GET['show_payment'])): ?>
            showPaymentModal();
        <?php endif; ?>
        
        const header = document.querySelector('.header');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.style.padding = '0.5rem';
            } else {
                header.style.padding = '0.8rem';
            }
        });
    });

    <?php if ($food): ?>
    document.querySelectorAll('input[name="size"]').forEach(input => {
        input.addEventListener('change', updatePrice);
    });
    
    document.getElementById('quantity').addEventListener('change', updatePrice);
    <?php endif; ?>

    function showAnimatedMessage(message, type = 'default', delay = 0) {
        setTimeout(() => {
            const popup = document.getElementById('successPopup');
            const overlay = document.getElementById('overlay');
            const messageEl = document.getElementById('successMessage');
            const animationContainer = document.getElementById('animationContainer');
            
            messageEl.textContent = message;
            animationContainer.innerHTML = '';
            
            if (type === 'cart') {
                animationContainer.innerHTML = `
                    <div class="cart-animation">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="1.5">
                            <circle cx="8" cy="21" r="2" />
                            <circle cx="19" cy="21" r="2" />
                            <path d="M2 2h2l3.6 14h13.4" />
                            <path d="M5.5 6h16l-1 8h-14" />
                        </svg>
                        <div class="cart-item-drop"></div>
                    </div>
                `;
                document.querySelector('.progress').style.background = '#ff6b6b';
            } else if (type === 'order') {
                animationContainer.innerHTML = `
                    <div class="order-animation">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M20 6L9 17L4 12" />
                        </svg>
                    </div>
                `;
                document.querySelector('.progress').style.background = '#4CAF50';
            } else if (type === 'remove') {
                animationContainer.innerHTML = `
                    <div class="remove-animation">
                        <div class="item"></div>
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M18 6L6 18" />
                            <path d="M6 6L18 18" />
                        </svg>
                    </div>
                `;
                document.querySelector('.progress').style.background = '#ff4444';
            } else {
                animationContainer.innerHTML = `
                    <div class="default-animation">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M20 6L9 17L4 12" />
                        </svg>
                    </div>
                `;
                document.querySelector('.progress').style.background = '#4CAF50';
            }
            
            popup.classList.add('show');
            overlay.classList.add('show');
            
            setTimeout(() => {
                popup.classList.remove('show');
                overlay.classList.remove('show');
            }, 3000);
            
            overlay.onclick = () => {
                popup.classList.remove('show');
                overlay.classList.remove('show');
            };
        }, delay);
    }

    function toggleWishlist(element) {
        const icon = element.querySelector('i');
        
        if (icon.classList.contains('far')) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            element.classList.add('active');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            element.classList.remove('active');
        }
        
        icon.classList.add('beating');
        setTimeout(() => {
            icon.classList.remove('beating');
        }, 1000);
    }
</script>

<?php
if (!empty($_SESSION['messages'])) {
    foreach ($_SESSION['messages'] as $index => $message) {
        $type = "default";
        if (strpos($message, "cart") !== false) {
            $type = "cart";
        } else if (strpos($message, "Order placed") !== false || strpos($message, "payment") !== false) {
            $type = "order";
        } else if (strpos($message, "removed") !== false) {
            $type = "remove";
        }
        
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showAnimatedMessage(" . json_encode($message) . ", " . json_encode($type) . ", " . ($index * 500) . ");
            });
        </script>";
    }
    $_SESSION['messages'] = [];
}
?>

</body>
</html>
<?php
$conn->close();
?>