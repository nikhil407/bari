<?php
require_once 'config/db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$user = getCurrentUser($pdo);

// Fetch cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.emoji, p.weight, p.price, p.old_price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    redirect('cart.php');
}

$subtotal = 0;
$savings = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    if ($item['old_price']) {
        $savings += ($item['old_price'] - $item['price']) * $item['quantity'];
    }
}
$deliveryFee = $subtotal >= 499 ? 0 : 30;
$total = $subtotal + $deliveryFee;

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = sanitize($_POST['address'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $payment = sanitize($_POST['payment'] ?? 'cod');
    $notes = sanitize($_POST['notes'] ?? '');

    if (empty($address) || empty($phone)) {
        $error = 'Please fill in delivery address and phone number.';
    } else {
        try {
            $pdo->beginTransaction();

            // Create order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, phone, payment_method, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $total, $address, $phone, $payment, $notes]);
            $orderId = $pdo->lastInsertId();

            // Add order items
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_emoji, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($cartItems as $item) {
                $stmt->execute([$orderId, $item['product_id'], $item['name'], $item['emoji'], $item['quantity'], $item['price']]);
            }

            // Clear cart
            $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);

            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Order failed. Please try again.';
        }
    }
}

$cartCount = getCartCount($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $success ? 'Order Placed!' : 'Checkout' ?> — Bari & Saha</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/store.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container nav-inner">
            <a href="store.php" class="logo">
                <span class="logo-emoji">🛒</span>
                <div class="logo-info">
                    <span class="logo-name">Bari & Saha</span>
                    <span class="logo-tagline">Delivery in <strong>30 mins</strong></span>
                </div>
            </a>
            <div class="nav-actions">
                <a href="store.php" class="nav-action-btn">
                    <span>🏪</span>
                    <span class="nav-action-label">Store</span>
                </a>
                <a href="cart.php" class="nav-cart-btn" id="navCartBtn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    <span class="nav-cart-label">Cart</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="cart-page">
        <div class="container">
            <?php if ($success): ?>
                <!-- Success State -->
                <div class="empty-state" style="padding: 80px 20px;">
                    <span style="font-size: 72px;">🎉</span>
                    <h3 style="font-size: 28px; margin-top: 16px;">Order Placed Successfully!</h3>
                    <p style="font-size: 16px; margin-top: 8px;">Your order #<?= $orderId ?> has been received. We'll deliver it in 30 minutes!</p>
                    <div style="display: flex; gap: 12px; justify-content: center; margin-top: 24px; flex-wrap: wrap;">
                        <a href="track_order.php?id=<?= $orderId ?>" class="checkout-btn" style="width: auto; padding: 14px 28px; display: inline-flex;">Track this order →</a>
                        <a href="store.php" class="back-link" style="padding: 14px 28px; border: 2px solid var(--primary); border-radius: var(--radius); color: var(--primary); font-weight: 700;">← Continue Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <h1 class="cart-page-title">📋 Checkout</h1>

                <?php if ($error): ?>
                    <div style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 500;">
                        ⚠️ <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="cart-layout">
                        <!-- Delivery Details -->
                        <div class="checkout-form-card">
                            <h3>📍 Delivery Details</h3>
                            <div class="form-grid">
                                <div class="form-group-checkout">
                                    <label>Full Name</label>
                                    <input type="text" value="<?= sanitize($user['full_name']) ?>" readonly>
                                </div>
                                <div class="form-group-checkout">
                                    <label>Phone Number *</label>
                                    <input type="tel" name="phone" required value="<?= sanitize($user['phone']) ?>" placeholder="+91 98765 43210">
                                </div>
                                <div class="form-group-checkout form-full">
                                    <label>Delivery Address *</label>
                                    <textarea name="address" rows="3" required placeholder="Full delivery address..."><?= sanitize($user['address'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group-checkout">
                                    <label>City</label>
                                    <input type="text" value="<?= sanitize($user['city'] ?? 'Mumbai') ?>" readonly>
                                </div>
                                <div class="form-group-checkout">
                                    <label>Pincode</label>
                                    <input type="text" value="<?= sanitize($user['pincode'] ?? '') ?>" readonly>
                                </div>
                            </div>

                            <h3 style="margin-top: 28px;">💳 Payment Method</h3>
                            <div class="payment-options">
                                <div class="payment-option">
                                    <input type="radio" name="payment" value="cod" id="payCod" checked>
                                    <label for="payCod"><span>💵</span> Cash on Delivery</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" name="payment" value="upi" id="payUpi">
                                    <label for="payUpi"><span>📱</span> UPI</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" name="payment" value="card" id="payCard">
                                    <label for="payCard"><span>💳</span> Card</label>
                                </div>
                            </div>

                            <div class="form-group-checkout" style="margin-top: 20px;">
                                <label>Order Notes (optional)</label>
                                <textarea name="notes" rows="2" placeholder="Any special instructions..."></textarea>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="cart-summary">
                            <h3>Order Summary</h3>
                            <?php foreach ($cartItems as $item): ?>
                                <div style="display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 14px;">
                                    <span style="font-size: 20px;"><?= $item['emoji'] ?></span>
                                    <span style="flex: 1;"><?= sanitize($item['name']) ?> × <?= $item['quantity'] ?></span>
                                    <strong>₹<?= number_format($item['price'] * $item['quantity']) ?></strong>
                                </div>
                            <?php endforeach; ?>

                            <div class="summary-row" style="margin-top: 16px;">
                                <span>Subtotal</span>
                                <span>₹<?= number_format($subtotal) ?></span>
                            </div>
                            <?php if ($savings > 0): ?>
                            <div class="summary-row">
                                <span>Savings</span>
                                <span class="savings">−₹<?= number_format($savings) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="summary-row">
                                <span>Delivery Fee</span>
                                <span><?= $deliveryFee === 0 ? '<span class="savings">FREE</span>' : '₹' . $deliveryFee ?></span>
                            </div>
                            <div class="summary-row total">
                                <span>Total</span>
                                <span>₹<?= number_format($total) ?></span>
                            </div>
                            <button type="submit" class="checkout-btn">
                                🛒 Place Order — ₹<?= number_format($total) ?>
                            </button>
                            <div class="delivery-note">
                                ⚡ Estimated delivery: <strong>30 minutes</strong>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <script src="js/store.js"></script>
</body>
</html>
