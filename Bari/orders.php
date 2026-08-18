<?php
require_once 'config/db.php';
if (!isLoggedIn()) { redirect('login.php'); }

$userId = $_SESSION['user_id'];
$cartCount = getCartCount($pdo);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY ordered_at DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

function orderProgressIndex($status) {
    $steps = ['pending' => 1, 'confirmed' => 1, 'preparing' => 2, 'out_for_delivery' => 3, 'delivered' => 4];
    return $steps[$status] ?? 0;
}
?>
<!DOCTYPE html><html lang="en"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My orders — Bari &amp; Saha</title><meta name="description" content="Review and track your Bari &amp; Saha grocery deliveries.">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="css/store.css">
</head><body>
    <nav class="navbar"><div class="container nav-inner"><a href="store.php" class="logo"><span class="logo-mark">B</span><div class="logo-info"><span class="logo-name">Bari <i>&amp;</i> Saha</span><span class="logo-tagline">Your neighbourhood market</span></div></a><div class="nav-actions"><a href="store.php" class="nav-action-btn"><span class="nav-action-label">Shop</span></a><a href="cart.php" class="nav-cart-btn" id="navCartBtn"><span class="nav-cart-label">Cart</span><?php if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?></a></div></div></nav>
    <main class="orders-page"><div class="container"><div class="orders-heading"><div><p class="eyebrow">YOUR PURCHASE HISTORY</p><h1 class="cart-page-title">Orders, made easy.</h1><p>Follow every fresh delivery from checkout to doorstep.</p></div><a href="store.php" class="orders-shop-link">Shop the market <span>→</span></a></div>
    <?php if (empty($orders)): ?><div class="empty-state"><span>□</span><h3>No orders yet</h3><p>Your first fresh basket is only a few taps away.</p><a href="store.php" class="back-link">Browse the market →</a></div>
    <?php else: foreach ($orders as $order): $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?"); $stmt->execute([$order['id']]); $items = $stmt->fetchAll(); $currentStep = orderProgressIndex($order['status']); ?>
        <article class="order-card order-card-enhanced"><div class="order-header"><div><span class="order-id">Order #<?= $order['id'] ?></span><span class="order-date">· <?= date('d M Y, h:i A', strtotime($order['ordered_at'])) ?></span></div><span class="order-status status-<?= $order['status'] ?>"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></span></div>
        <div class="order-items-list"><?php foreach ($items as $item): ?><div class="order-item-row"><span><?= $item['product_emoji'] ?></span><span class="order-item-name"><?= sanitize($item['product_name']) ?></span><span class="order-item-qty">× <?= $item['quantity'] ?></span><span class="order-item-price">₹<?= number_format($item['price'] * $item['quantity']) ?></span></div><?php endforeach; ?></div>
        <?php if ($order['status'] !== 'cancelled'): ?><div class="order-mini-progress" aria-label="Order progress"><span class="<?= $currentStep >= 1 ? 'done' : '' ?>"><i>1</i> Confirmed</span><span class="<?= $currentStep >= 2 ? 'done' : '' ?>"><i>2</i> Packing</span><span class="<?= $currentStep >= 3 ? 'done' : '' ?>"><i>3</i> On the way</span><span class="<?= $currentStep >= 4 ? 'done' : '' ?>"><i>4</i> Delivered</span></div><?php endif; ?>
        <div class="order-footer"><div class="order-footer-info"><span><?= ucfirst($order['payment_method']) ?> · <?= sanitize($order['delivery_address']) ?></span></div><div class="order-footer-actions"><span class="order-total">₹<?= number_format($order['total_amount']) ?></span><a class="order-track-btn" href="track_order.php?id=<?= $order['id'] ?>">Track order <span>→</span></a><button class="order-again-btn" onclick="reorderOrder(<?= $order['id'] ?>, this)">Order again</button></div></div></article>
    <?php endforeach; endif; ?></div></main><div class="toast-container" id="toastContainer"></div><script src="js/store.js"></script>
</body></html>
