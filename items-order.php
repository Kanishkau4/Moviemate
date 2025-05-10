<?php
// items-order.php
require_once 'auth_check.php';
requireLogin();

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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$item = null;

if ($id > 0) {
    $sql = "SELECT * FROM items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
}

// Define size-based price adjustments
$size_price_adjustments = [
    'small' => 1.0,
    'medium' => 1.1,
    'large' => 1.2,
    'xl' => 1.3
];

// Handle Remove from Cart
if (isset($_POST['remove_from_cart'])) {
    $index = (int)$_POST['remove_from_cart'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        
        $cartTotal = 0;
        foreach ($_SESSION['cart'] as $cartItem) {
            $cartTotal += $cartItem['total'];
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
        foreach ($_SESSION['cart'] as $cartItem) {
            $cartTotal += $cartItem['total'];
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
        foreach ($_SESSION['cart'] as $index => $cartItem):
            $cartTotal += isset($cartItem['total']) ? (float)$cartItem['total'] : 0;
            $imageUrl = isset($cartItem['image_url']) ? $cartItem['image_url'] : 'Images/default-item.jpg';
    ?>
            <div class="cart-item" data-index="<?php echo $index; ?>">
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                     alt="<?php echo htmlspecialchars($cartItem['name']); ?>" 
                     class="cart-item-image">
                <div class="cart-item-details">
                    <h4><?php echo htmlspecialchars($cartItem['name']); ?></h4>
                    <p>Size: <?php echo htmlspecialchars($cartItem['size']); ?></p>
                    <div class="cart-item-quantity">
                        <button onclick="updateCartQuantity(<?php echo $index; ?>, -1)">-</button>
                        <span><?php echo $cartItem['quantity']; ?></span>
                        <button onclick="updateCartQuantity(<?php echo $index; ?>, 1)">+</button>
                    </div>
                </div>
                <div class="cart-item-actions">
                    <div class="cart-item-price">
                        Rs.<?php echo number_format($cartItem['total'], 2); ?>
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
            <a href="items.php" class="btn btn-primary" style="margin-top: 10px;">Browse Items</a>
        </div>
    <?php endif;
    return ob_get_clean();
}

// Handle creating pending order from cart
if (isset($_POST['create_pending_order_from_cart'])) {
    if (!empty($_SESSION['cart'])) {
        $total = 0;
        foreach ($_SESSION['cart'] as $cartItem) {
            $total += $cartItem['total'];
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
if (isset($_POST['add_to_cart']) && $item) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size = isset($_POST['size']) ? $_POST['size'] : 'medium';
    
    $adjusted_price = $item['price'];
    if (in_array(strtolower($item['category']), ['t-shirt', 'shirt'])) {
        $adjusted_price *= $size_price_adjustments[$size];
    }
    
    $total = $adjusted_price * $quantity;
    
    $cartItem = [
        'id' => $id,
        'name' => $item['name'],
        'price' => $adjusted_price,
        'quantity' => $quantity,
        'size' => $size,
        'total' => $total,
        'image_url' => $item['image_url'] // Added image_url
    ];

    $_SESSION['cart'][] = $cartItem;
    
    $_SESSION['messages'][] = "Item successfully added to cart!";
    header("Location: items-order.php?id=$id&added=1");
    exit();
}

// Calculate initial price
$base_price = $item ? $item['price'] : 0;
$show_size_selector = $item && in_array(strtolower($item['category']), ['t-shirt', 'shirt']);

// Handle Buy Now
if (isset($_POST['buy_now']) && $item && isset($item['price'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size = isset($_POST['size']) ? $_POST['size'] : 'medium';

    $adjusted_price = $item['price'];
    if (in_array(strtolower($item['category']), ['t-shirt', 'shirt'])) {
        $adjusted_price *= $size_price_adjustments[$size];
    }

    $_SESSION['pending_order'] = [
        'type' => 'single',
        'item_id' => $id,
        'quantity' => $quantity,
        'size' => $size,
        'price' => $adjusted_price,
        'total' => $adjusted_price * $quantity
    ];
    
    header("Location: items-order.php?id=$id&show_payment=1");
    exit();
}

// Process Payment
if (isset($_POST['process_payment'])) {
    if (isset($_SESSION['pending_order'])) {
        $user_id = $_SESSION['user_id'];
        $pending_order = $_SESSION['pending_order'];

        if ($pending_order['type'] === 'cart') {
            $success = true;
            foreach ($pending_order['items'] as $cartItem) {
                $sql = "INSERT INTO items_orders (user_id, item_id, size, quantity, total_price) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisid", 
                    $user_id, 
                    $cartItem['id'],
                    $cartItem['size'],
                    $cartItem['quantity'],
                    $cartItem['total']
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
                header("Location: items-order.php?ordered=1");
                exit();
            }
        } else {
            $sql = "INSERT INTO items_orders (user_id, item_id, size, quantity, total_price) 
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
                header("Location: items-order.php?ordered=1");
                exit();
            }
        }

        $_SESSION['messages'][] = "Error processing order. Please try again.";
        header("Location: items-order.php");
        exit();
    } else {
        $_SESSION['messages'][] = "No pending order found. Please try again.";
        header("Location: items-order.php");
        exit();
    }
}

$userInfo = getUserDisplayInfo();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $item ? htmlspecialchars($item['name']) : 'MovieMate'; ?></title>
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
            height: 40px
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
            padding: 1.5rem;
            border-radius: 50%;
            background: transparent; /* Changed from rgba(0, 0, 0, 0.05) to transparent */
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
            top: 4px;
            right: -3px;
            background: #ff6b6b;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 2px solid white;
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

        .product-container {
            max-width: 1200px;
            margin: 60px auto 40px;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .product-gallery {
            position: sticky;
            top: 80px;
        }

        .main-image {
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .main-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .product-info {
            padding: 20px;
        }

        .product-title {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #333;
        }

        .product-price {
            font-size: 2rem;
            color: #ff4444;
            margin-bottom: 20px;
        }

        .stock-status {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .size-selector {
            margin-bottom: 30px;
        }

        .size-selector label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }

        .size-options {
            display: flex;
            gap: 10px;
        }

        .size-option {
            position: relative;
        }

        .size-option input[type="radio"] {
            display: none;
        }

        .size-option label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            color: #333;
            transition: all 0.3s ease;
        }

        .size-option input[type="radio"]:checked + label {
            border-color: #ff4444;
            background-color: #fff5f5;
            color: #ff4444;
        }

        .size-option label:hover {
            border-color: #ff4444;
        }

        .size-guide {
            display: inline-block;
            margin-top: 10px;
            color: #666;
            text-decoration: underline;
            cursor: pointer;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .quantity-btn {
            background: #f5f5f5;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .quantity-input {
            width: 60px;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background: #ff6b6b;
            color: white;
            flex: 1;
        }

        .btn-primary1 {
            background: #ff6b6b;
            color: white;
            flex: 1;
        }

        .btn-secondary {
            background: #333;
            color: white;
        }

        .btn-secondary1 {
            background: #333;
            color: white;
            flex: 1;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .additional-info {
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .features-list {
            list-style: none;
            margin-bottom: 20px;
        }

        .features-list li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .features-list i {
            color: #ff4444;
        }

        .original-price {
            font-size: 1.5rem;
            color: #999;
            text-decoration: line-through;
            margin-left: 10px;
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

        .animation-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
            z-index: 1001;
            width: 300px;
            display: none;
            overflow: hidden;
        }

        .animation-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            display: none;
            backdrop-filter: blur(3px);
        }

        .animation-title {
            font-size: 1.5rem;
            margin: 15px 0;
            color: #333;
        }

        .animation-message {
            color: #666;
            margin-bottom: 20px;
        }

        /* Cart Animation */
        .cart-animation {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto;
        }

        .cart-icon {
            width: 80px;
            height: 80px;
            position: relative;
            margin: 0 auto;
        }

        .cart-base {
            position: absolute;
            width: 60px;
            height: 35px;
            bottom: 0;
            left: 10px;
            background: #ff4444;
            border-radius: 5px 5px 2px 2px;
        }

        .cart-handle {
            position: absolute;
            width: 30px;
            height: 25px;
            right: 0;
            top: 20px;
            border: 3px solid #ff4444;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .cart-wheel {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #333;
            border-radius: 50%;
            bottom: -5px;
        }

        .cart-wheel-left {
            left: 15px;
        }

        .cart-wheel-right {
            left: 45px;
        }

        .product-item {
            position: absolute;
            width: 20px;
            height: 20px;
            background: #3498db;
            border-radius: 3px;
            top: -50px;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
        }

        /* Order Complete Animation */
        .order-animation {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto;
        }

        .order-box {
            position: absolute;
            width: 60px;
            height: 40px;
            background: #e67e22;
            bottom: 10px;
            left: 20px;
            border-radius: 5px;
            transform-origin: bottom;
        }

        .order-lid {
            position: absolute;
            width: 70px;
            height: 15px;
            background: #d35400;
            bottom: 50px;
            left: 15px;
            border-radius: 5px 5px 0 0;
            transform-origin: bottom;
        }

        .order-checkmark {
            position: absolute;
            width: 40px;
            height: 40px;
            top: 20px;
            left: 30px;
            opacity: 0;
        }

        .order-checkmark:before {
            content: '';
            position: absolute;
            width: 20px;
            height: 5px;
            background: #2ecc71;
            transform: rotate(45deg);
            bottom: 10px;
            left: 0;
            border-radius: 5px;
        }

        .order-checkmark:after {
            content: '';
            position: absolute;
            width: 35px;
            height: 5px;
            background: #2ecc71;
            transform: rotate(-45deg);
            bottom: 15px;
            left: 7px;
            border-radius: 5px;
        }

        /* Size Changed Animation */
        .size-animation {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto;
        }

        .tshirt {
            position: absolute;
            width: 60px;
            height: 60px;
            left: 20px;
            top: 20px;
        }

        .tshirt-body {
            position: absolute;
            width: 50px;
            height: 40px;
            background: #3498db;
            border-radius: 5px;
            top: 15px;
            left: 5px;
        }

        .tshirt-sleeve-left {
            position: absolute;
            width: 15px;
            height: 10px;
            background: #3498db;
            left: -5px;
            top: 15px;
            transform: rotate(30deg);
        }

        .tshirt-sleeve-right {
            position: absolute;
            width: 15px;
            height: 10px;
            background: #3498db;
            right: -5px;
            top: 15px;
            transform: rotate(-30deg);
        }

        .tshirt-neck {
            position: absolute;
            width: 20px;
            height: 10px;
            border-radius: 0 0 10px 10px;
            background: white;
            top: 5px;
            left: 20px;
        }

        .size-text {
            position: absolute;
            font-size: 24px;
            font-weight: bold;
            color: white;
            top: 25px;
            left: 26px;
        }

        /* Removal Animation */
        .remove-animation {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto;
        }

        .cart-mini {
            position: absolute;
            width: 40px;
            height: 40px;
            top: 30px;
            left: 30px;
            opacity: 1;
        }

        .cart-mini-base {
            position: absolute;
            width: 30px;
            height: 20px;
            bottom: 0;
            left: 5px;
            background: #ff4444;
            border-radius: 3px;
        }

        .cart-mini-handle {
            position: absolute;
            width: 15px;
            height: 12px;
            right: 0;
            top: 12px;
            border: 2px solid #ff4444;
            border-right: none;
            border-radius: 5px 0 0 5px;
        }

        .remove-x {
            position: absolute;
            width: 50px;
            height: 50px;
            top: 25px;
            left: 25px;
            opacity: 0;
        }

        .remove-x:before, .remove-x:after {
            content: '';
            position: absolute;
            width: 40px;
            height: 4px;
            background: #e74c3c;
            top: 23px;
            left: 5px;
            border-radius: 2px;
        }

        .remove-x:before {
            transform: rotate(45deg);
        }

        .remove-x:after {
            transform: rotate(-45deg);
        }

        /* Keyframes for animations */
        @keyframes addToCart {
            0% { transform: translateY(-50px) translateX(-50%); opacity: 0; }
            30% { transform: translateY(0) translateX(-50%); opacity: 1; }
            70% { transform: translateY(0) translateX(-50%); opacity: 1; }
            100% { transform: translateY(30px) translateX(-50%); opacity: 0; }
        }

        @keyframes cartWobble {
            0% { transform: rotate(0); }
            25% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
            75% { transform: rotate(-3deg); }
            100% { transform: rotate(0); }
        }

        @keyframes boxOpen {
            0% { transform: rotateX(0); }
            50% { transform: rotateX(-60deg); }
            100% { transform: rotateX(-60deg); }
        }

        @keyframes lidOpen {
            0% { transform: rotateX(0); }
            50% { transform: rotateX(60deg); }
            100% { transform: rotateX(60deg); }
        }

        @keyframes checkmarkAppear {
            0% { opacity: 0; transform: scale(0.5); }
            60% { opacity: 0; transform: scale(0.5); }
            100% { opacity: 1; transform: scale(1); }
        }

        @keyframes sizeChange {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        @keyframes fadeCartOut {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }

        @keyframes fadeXIn {
            0% { opacity: 0; transform: scale(0.5); }
            100% { opacity: 1; transform: scale(1); }
        }

        @keyframes progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }

        @keyframes slideUp {
            0% { transform: translate(-50%, 20px); opacity: 0; }
            100% { transform: translate(-50%, -50%); opacity: 1; }
        }

        @keyframes slideDown {
            0% { transform: translate(-50%, -50%); opacity: 1; }
            100% { transform: translate(-50%, 20px); opacity: 0; }
        }

        @media (max-width: 768px) {
            .product-container {
                grid-template-columns: 1fr;
            }
        }

        .text-center { text-align: center; }
        .py-8 { padding-top: 2rem; padding-bottom: 2rem; }
        .mb-4 { margin-bottom: 1rem; }
        .text-2xl { font-size: 1.5rem; }
        .font-bold { font-weight: bold; }
        .text-green-600 { color: #16a34a; }
        .text-gray-600 { color: #4b5563; }
    </style>
</head>
<body>   
<header class="header">
    <div class="header-content">
        <div class="logo">MovieMate</div>
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
                    <a href="item-history.php">My Orders</a>
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

<div class="animation-overlay" id="animationOverlay"></div>

<div class="animation-container" id="addToCartAnimation">
    <div class="cart-animation">
        <div class="cart-icon">
            <div class="cart-base"></div>
            <div class="cart-handle"></div>
            <div class="cart-wheel cart-wheel-left"></div>
            <div class="cart-wheel cart-wheel-right"></div>
        </div>
        <div class="product-item"></div>
    </div>
    <h3 class="animation-title">Added to Cart!</h3>
    <p class="animation-message">Item successfully added to your cart</p>
    <div class="animation-progress"></div>
</div>

<div class="animation-container" id="orderCompleteAnimation">
    <div class="order-animation">
        <div class="order-box"></div>
        <div class="order-lid"></div>
        <div class="order-checkmark"></div>
    </div>
    <h3 class="animation-title">Order Complete!</h3>
    <p class="animation-message">Your order has been successfully placed</p>
    <div class="animation-progress"></div>
</div>

<div class="animation-container" id="sizeChangedAnimation">
    <div class="size-animation">
        <div class="tshirt">
            <div class="tshirt-body"></div>
            <div class="tshirt-sleeve-left"></div>
            <div class="tshirt-sleeve-right"></div>
            <div class="tshirt-neck"></div>
            <div class="size-text" id="sizeText">M</div>
        </div>
    </div>
    <h3 class="animation-title">Size Updated!</h3>
    <p class="animation-message">Price adjusted for the selected size</p>
    <div class="animation-progress"></div>
</div>

<div class="animation-container" id="itemRemovedAnimation">
    <div class="remove-animation">
        <div class="cart-mini">
            <div class="cart-mini-base"></div>
            <div class="cart-mini-handle"></div>
        </div>
        <div class="remove-x"></div>
    </div>
    <h3 class="animation-title">Item Removed</h3>
    <p class="animation-message">Item successfully removed from your cart</p>
    <div class="animation-progress"></div>
</div>

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

<?php if ($item): ?>
<div class="product-container">
    <div class="product-gallery">
        <div class="main-image">
            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                 alt="<?php echo htmlspecialchars($item['name']); ?>">
        </div>
    </div>

    <div class="product-info">
        <h1 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h1>
        
        <div class="product-price">
            Rs:<span id="final-price"><?php echo number_format($base_price, 2); ?></span>
            <?php if ($item['discount'] > 0): ?>
                <span class="original-price">
                    Rs:<?php echo number_format($base_price * (1 + $item['discount']/100), 2); ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="stock-status">
            <i class="fas fa-check-circle"></i>
            <span>In Stock</span>
        </div>

        <form action="" method="POST">
            <?php if ($show_size_selector): ?>
            <div class="size-selector">
                <label>Select Size</label>
                <div class="size-options">
                    <div class="size-option">
                        <input type="radio" name="size" id="size-s" value="small" onchange="updatePrice()">
                        <label for="size-s">S</label>
                    </div>
                    <div class="size-option">
                        <input type="radio" name="size" id="size-m" value="medium" checked onchange="updatePrice()">
                        <label for="size-m">M</label>
                    </div>
                    <div class="size-option">
                        <input type="radio" name="size" id="size-l" value="large" onchange="updatePrice()">
                        <label for="size-l">L</label>
                    </div>
                    <div class="size-option">
                        <input type="radio" name="size" id="size-xl" value="xl" onchange="updatePrice()">
                        <label for="size-xl">XL</label>
                    </div>
                </div>
                <a href="#" class="size-guide" onclick="showSizeGuide(event)">Size Guide</a>
            </div>
            <?php endif; ?>

            <div class="quantity-selector">
                <button type="button" class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                <input type="number" name="quantity" value="1" min="1" max="10" class="quantity-input" id="quantity" onchange="updatePrice()">
                <button type="button" class="quantity-btn" onclick="updateQuantity(1)">+</button>
            </div>

            <div class="action-buttons">
                <button type="submit" name="add_to_cart" class="btn btn-primary1">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
                <button type="submit" name="buy_now" class="btn btn-secondary1">
                    <i class="fas fa-bolt"></i> Buy Now
                </button>
            </div>
        </form>

        <div class="additional-info">
            <ul class="features-list">
                <li><i class="fas fa-truck"></i><span>Free shipping on orders over Rs:5000</span></li>
                <li><i class="fas fa-undo"></i><span>30-day return policy</span></li>
                <li><i class="fas fa-shield-alt"></i><span>Secure payment</span></li>
                <li><i class="fas fa-certificate"></i><span>Authentic licensed merchandise</span></li>
            </ul>
        </div>
    </div>
</div>
<?php else: ?>
<div class="product-container text-center py-8">
    <?php if (isset($_GET['ordered']) && $_GET['ordered'] == 1): ?>
        <h2 class="text-2xl font-bold text-green-600 mb-4">Order Completed Successfully!</h2>
        <p class="mb-4">Thank you for your order.</p>
        <a href="items.php" class="btn btn-primary">Return to Menu</a>
    <?php else: ?>
        <h2 class="text-2xl font-bold text-gray-600 mb-4">No Item Selected</h2>
        <p class="mb-4">Please select an item from our menu.</p>
        <a href="items.php" class="btn btn-primary">View Menu</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
    const basePrice = <?php echo $base_price; ?>;
    const isClothing = <?php echo $show_size_selector ? 'true' : 'false'; ?>;
    const sizeAdjustments = {
        'small': 1.0,
        'medium': 1.1,
        'large': 1.2,
        'xl': 1.3
    };

    function updatePrice() {
        <?php if ($item): ?>
        const quantity = parseInt(document.getElementById('quantity').value);
        let finalPrice = basePrice;
        
        if (isClothing) {
            const selectedSize = document.querySelector('input[name="size"]:checked').value;
            finalPrice *= sizeAdjustments[selectedSize];
        }
        
        finalPrice *= quantity;
        document.getElementById('final-price').textContent = finalPrice.toFixed(2);
        <?php endif; ?>
    }

    function updateQuantity(change) {
        <?php if ($item): ?>
        const input = document.getElementById('quantity');
        const newValue = parseInt(input.value) + change;
        if (newValue >= parseInt(input.min) && newValue <= parseInt(input.max)) {
            input.value = newValue;
            updatePrice();
        }
        <?php endif; ?>
    }

    function toggleCart() {
        const dropdown = document.getElementById('cartDropdown');
        dropdown.classList.toggle('show');
    }

    function removeFromCart(index) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", window.location.href, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    updateCartInfo(response.cartCount, response.cartTotal);
                    document.getElementById('cartDropdown').innerHTML = response.cartHtml;
                    showAnimation('item-removed', "Item successfully removed from your cart");
                } else {
                    showAnimation('error', "Failed to remove item from cart.");
                }
            }
        };
        xhr.send("remove_from_cart=" + index);
    }

    function updateCartQuantity(index, change) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", window.location.href, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    updateCartInfo(response.cartCount, response.cartTotal);
                    document.getElementById('cartDropdown').innerHTML = response.cartHtml;
                    showAnimation('add-to-cart', "Cart updated successfully");
                } else {
                    showAnimation('error', "Failed to update cart");
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
            cartTotalElement.innerHTML = `<strong>Cart Total: Rs.${formattedCartTotal}</strong>`;
        }
    }

    function showPaymentModal() {
        fetch('items-order.php', {
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
                showAnimation('error', "Error creating order. Please try again.");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAnimation('error', "Error creating order. Please try again.");
        });
    }

    function hidePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none';
    }

    function showAnimation(type, customMessage = null, duration = 3000) {
        const overlay = document.getElementById('animationOverlay');
        let container;
        
        document.querySelectorAll('.animation-container').forEach(el => {
            el.style.display = 'none';
        });
        
        switch(type) {
            case 'add-to-cart':
                container = document.getElementById('addToCartAnimation');
                const productItem = container.querySelector('.product-item');
                const cartIcon = container.querySelector('.cart-icon');
                productItem.style.animation = 'none';
                cartIcon.style.animation = 'none';
                void productItem.offsetWidth;
                void cartIcon.offsetWidth;
                productItem.style.animation = 'addToCart 1.5s ease-in-out forwards';
                cartIcon.style.animation = 'cartWobble 0.5s ease-in-out 0.7s';
                break;
            case 'order-complete':
                container = document.getElementById('orderCompleteAnimation');
                const orderBox = container.querySelector('.order-box');
                const orderLid = container.querySelector('.order-lid');
                const checkmark = container.querySelector('.order-checkmark');
                orderBox.style.animation = 'none';
                orderLid.style.animation = 'none';
                checkmark.style.animation = 'none';
                void orderBox.offsetWidth;
                void orderLid.offsetWidth;
                void checkmark.offsetWidth;
                orderBox.style.animation = 'boxOpen 1.5s ease-in-out forwards';
                orderLid.style.animation = 'lidOpen 1.5s ease-in-out forwards';
                checkmark.style.animation = 'checkmarkAppear 1.5s ease-in-out forwards';
                break;
            case 'size-changed':
                container = document.getElementById('sizeChangedAnimation');
                const tshirt = container.querySelector('.tshirt');
                if (customMessage && customMessage.size) {
                    document.getElementById('sizeText').textContent = customMessage.size.toUpperCase().charAt(0);
                    const tshirtBody = container.querySelector('.tshirt-body');
                    const tshirtSleeveLeft = container.querySelector('.tshirt-sleeve-left');
                    const tshirtSleeveRight = container.querySelector('.tshirt-sleeve-right');
                    let color;
                    switch(customMessage.size.toLowerCase()) {
                        case 'small': color = '#3498db'; break;
                        case 'medium': color = '#2ecc71'; break;
                        case 'large': color = '#e67e22'; break;
                        case 'xl': color = '#9b59b6'; break;
                        default: color = '#3498db';
                    }
                    tshirtBody.style.background = color;
                    tshirtSleeveLeft.style.background = color;
                    tshirtSleeveRight.style.background = color;
                }
                tshirt.style.animation = 'none';
                void tshirt.offsetWidth;
                tshirt.style.animation = 'sizeChange 1s ease-in-out';
                break;
            case 'item-removed':
                container = document.getElementById('itemRemovedAnimation');
                const cartMini = container.querySelector('.cart-mini');
                const removeX = container.querySelector('.remove-x');
                cartMini.style.animation = 'none';
                removeX.style.animation = 'none';
                void cartMini.offsetWidth;
                void removeX.offsetWidth;
                cartMini.style.animation = 'fadeCartOut 0.5s ease-in-out forwards';
                removeX.style.animation = 'fadeXIn 0.5s ease-in-out 0.3s forwards';
                break;
            default:
                return;
        }
        
        if (customMessage && typeof customMessage === 'string') {
            const messageEl = container.querySelector('.animation-message');
            if (messageEl) {
                messageEl.textContent = customMessage;
            }
        }
        
        overlay.style.display = 'block';
        overlay.style.animation = 'fadeIn 0.3s forwards';
        container.style.display = 'block';
        container.style.animation = 'slideUp 0.5s forwards';
        
        const progressBar = container.querySelector('.animation-progress');
        progressBar.style.animation = `progress ${duration/1000}s linear forwards`;
        
        setTimeout(() => {
            overlay.style.animation = 'fadeOut 0.3s forwards';
            container.style.animation = 'slideDown 0.5s forwards';
            setTimeout(() => {
                overlay.style.display = 'none';
                container.style.display = 'none';
                progressBar.style.animation = 'none';
                void progressBar.offsetWidth;
            }, 500);
        }, duration);
    }

    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_GET['show_payment'])): ?>
            showPaymentModal();
        <?php endif; ?>

        <?php if ($item): ?>
        const sizeInputs = document.querySelectorAll('input[name="size"]');
        if (sizeInputs.length > 0) {
            sizeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    updatePrice();
                    showAnimation('size-changed', { size: this.value });
                });
            });
        }
        <?php endif; ?>

        <?php if (!empty($_SESSION['messages'])): ?>
            <?php foreach ($_SESSION['messages'] as $message): ?>
                showAnimation(
                    <?php echo json_encode($message === "Item successfully added to cart!" ? 'add-to-cart' : 
                        ($message === "Order placed successfully!" ? 'order-complete' : 
                        ($message === "Item successfully removed from your cart" ? 'item-removed' : 'default'))); ?>,
                    <?php echo json_encode($message); ?>
                );
            <?php endforeach; ?>
            <?php $_SESSION['messages'] = []; ?>
        <?php endif; ?>

        updatePrice();

        const header = document.querySelector('.header');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.style.padding = '0.5rem';
            } else {
                header.style.padding = '0.8rem';
            }
        });
    });

    function showSizeGuide(event) {
        event.preventDefault();
        alert('Size Guide: \nSmall: EU 44-46 / UK 34-36\nMedium: EU 48-50 / UK 38-40\nLarge: EU 52-54 / UK 42-44\nXL: EU 56-58 / UK 46-48');
    }
</script>
</body>
</html>
<?php
$conn->close();
?>