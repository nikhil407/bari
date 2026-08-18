<?php
require_once 'config/db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];

// Fetch cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.emoji, p.weight, p.price, p.old_price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.added_at DESC
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

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
$cartCount = getCartCount($pdo);
$user = getCurrentUser($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart — Bari & Saha</title>
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
            <div class="nav-search">
                <form method="GET" action="store.php" class="search-form">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" placeholder="Search for products...">
                </form>
            </div>
            <div class="nav-actions">
                <a href="store.php" class="nav-action-btn">
                    <span>🏪</span>
                    <span class="nav-action-label">Store</span>
                </a>
                <a href="cart.php" class="nav-cart-btn" id="navCartBtn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    <span class="nav-cart-label">My Cart</span>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>

    <main class="cart-page">
        <div class="container">
            <h1 class="cart-page-title">🛒 My Cart</h1>

            <?php if (empty($cartItems)): ?>
                <div class="empty-state">
                    <span>🛒</span>
                    <h3>Your cart is empty</h3>
                    <p>Add some fresh groceries and start shopping!</p>
                    <a href="store.php" class="back-link">← Continue Shopping</a>
                </div>
            <?php else: ?>
                <div class="cart-layout">
                    <!-- Cart Items -->
                    <div class="cart-items-list">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item-row">
                                <div class="ci-emoji"><?= $item['emoji'] ?></div>
                                <div class="ci-details">
                                    <h4><?= sanitize($item['name']) ?></h4>
                                    <span class="ci-weight"><?= sanitize($item['weight']) ?></span>
                                </div>
                                <div class="ci-qty">
                                    <button onclick="updateQty(<?= $item['product_id'] ?>, -1)">−</button>
                                    <span><?= $item['quantity'] ?></span>
                                    <button onclick="updateQty(<?= $item['product_id'] ?>, 1)">+</button>
                                </div>
                                <div class="ci-price">₹<?= number_format($item['price'] * $item['quantity']) ?></div>
                                <button class="ci-remove" onclick="removeFromCart(<?= $item['product_id'] ?>)" title="Remove">🗑️</button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Summary -->
                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal (<?= count($cartItems) ?> items)</span>
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
                        <a href="checkout.php" class="checkout-btn">
                            Proceed to Checkout →
                        </a>
                        <div class="delivery-note">
                            ⚡ Estimated delivery: <strong>30 minutes</strong>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="toast-container" id="toastContainer"></div>
    <script src="js/store.js"></script>
</body>
</html>
