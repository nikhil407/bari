<?php
require_once '../config/db.php';

if (!isLoggedIn()) redirect('../login.php');
$user = getCurrentUser($pdo);
if (!$user || $user['is_admin'] != 1) die("Access denied.");

$message = '';

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']);

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $order_id])) {
        $message = "Order #$order_id status updated to " . ucfirst($status) . ".";
    }
}

// Fetch Orders
$orders = $pdo->query("
    SELECT o.*, u.full_name as customer_name, u.email as customer_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.ordered_at DESC
")->fetchAll();

$statuses = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .order-details-btn { cursor: pointer; text-decoration: underline; color: var(--color-primary); }
        .order-row-details { display: none; background: #fafafa; padding: 15px; }
        .order-row-details.active { display: table-row; }
        .order-items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .order-items-table th, .order-items-table td { padding: 8px; border-bottom: 1px solid #eee; text-align: left; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-brand"><span class="brand-mark">B</span><span>Bari <i>&amp;</i> Saha</span></div>
            <nav class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="categories.php">Categories</a>
                <a href="orders.php" class="active">Orders</a>
                <a href="users.php">Users</a>
            </nav>
            <div class="admin-user"><span><?= htmlspecialchars($user['full_name']) ?></span><a href="../logout.php">Logout</a></div>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Orders</h1>
            </header>

            <div class="admin-content">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="table-panel">
                    <h2>All Orders</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <span class="order-details-btn" onclick="toggleDetails(<?= $o['id'] ?>)">#<?= $o['id'] ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($o['customer_name']) ?><br>
                                    <small style="color: #666;"><?= htmlspecialchars($o['phone']) ?></small>
                                </td>
                                <td><?= date('M d, Y h:i A', strtotime($o['ordered_at'])) ?></td>
                                <td>₹<?= number_format($o['total_amount'], 2) ?></td>
                                <td><span class="status-badge status-<?= strtolower($o['status']) ?>"><?= ucfirst(str_replace('_', ' ', $o['status'])) ?></span></td>
                                <td>
                                    <form method="POST" style="display: flex; gap: 5px;">
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <select name="status" style="padding: 4px; border: 1px solid #ddd; border-radius: 4px;">
                                            <?php foreach ($statuses as $st): ?>
                                                <option value="<?= $st ?>" <?= $o['status'] == $st ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $st)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-sm btn-primary">Save</button>
                                    </form>
                                </td>
                            </tr>
                            <tr id="details-<?= $o['id'] ?>" class="order-row-details">
                                <td colspan="6">
                                    <div style="padding: 10px; background: white; border: 1px solid #eee; border-radius: 8px;">
                                        <h4>Order Details</h4>
                                        <p><strong>Address:</strong> <?= htmlspecialchars($o['delivery_address']) ?></p>
                                        <p><strong>Payment:</strong> <?= strtoupper($o['payment_method']) ?></p>
                                        <?php if ($o['notes']): ?>
                                            <p><strong>Notes:</strong> <?= htmlspecialchars($o['notes']) ?></p>
                                        <?php endif; ?>

                                        <?php
                                        $items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                        $items->execute([$o['id']]);
                                        $orderItems = $items->fetchAll();
                                        ?>
                                        <table class="order-items-table">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Qty</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($orderItems as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['product_emoji'] . ' ' . $item['product_name']) ?></td>
                                                    <td>₹<?= number_format($item['price'], 2) ?></td>
                                                    <td>x<?= $item['quantity'] ?></td>
                                                    <td>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleDetails(id) {
            const el = document.getElementById('details-' + id);
            el.classList.toggle('active');
        }
    </script>
</body>
</html>
