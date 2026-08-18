<?php
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$action = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);

if (!isLoggedIn()) {
    echo json_encode(['status' => 'login_required', 'message' => 'Please login first']);
    exit;
}

$userId = $_SESSION['user_id'];

switch ($action) {
    case 'add':
        // Check if product exists
        $stmt = $pdo->prepare("SELECT id, name, emoji FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit;
        }

        // Add or increment in cart
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
        $stmt->execute([$userId, $productId]);

        $cartCount = getCartCount($pdo);
        echo json_encode([
            'status' => 'success',
            'message' => "{$product['emoji']} {$product['name']} added to cart!",
            'cart_count' => $cartCount
        ]);
        break;

    case 'update':
        $delta = (int)($_POST['delta'] ?? 0);

        // Get current quantity
        $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $item = $stmt->fetch();

        if (!$item) {
            echo json_encode(['status' => 'error', 'message' => 'Item not in cart']);
            exit;
        }

        $newQty = $item['quantity'] + $delta;
        if ($newQty <= 0) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
        } else {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$newQty, $userId, $productId]);
        }

        echo json_encode(['status' => 'success', 'cart_count' => getCartCount($pdo)]);
        break;

    case 'remove':
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);

        echo json_encode(['status' => 'success', 'cart_count' => getCartCount($pdo)]);
        break;

    case 'reorder':
        $orderId = (int)($_POST['order_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userId]);
        if (!$stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Order not found']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();
        if (!$items) {
            echo json_encode(['status' => 'error', 'message' => 'There are no items to add']);
            exit;
        }
        $pdo->beginTransaction();
        try {
            $add = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
            foreach ($items as $item) { $add->execute([$userId, $item['product_id'], $item['quantity']]); }
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Your previous basket is ready in the cart.', 'cart_count' => getCartCount($pdo)]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'We could not add that order right now.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
?>
