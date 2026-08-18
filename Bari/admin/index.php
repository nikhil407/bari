<?php
require_once '../config/db.php';

// Check if user is logged in and is an admin
if (!isLoggedIn()) {
    redirect('../login.php');
}

$user = getCurrentUser($pdo);
if (!$user || $user['is_admin'] != 1) {
    die("Access denied. Admin privileges required.");
}

// Fetch some basic stats for the dashboard
$usersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$ordersCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$productsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status IN ('delivered', 'confirmed')")->fetchColumn();

// Fetch recent orders
$recentOrders = $pdo->query("
    SELECT o.id, u.full_name, o.total_amount, o.status, o.ordered_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.ordered_at DESC
    LIMIT 5
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Bari &amp; Saha</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <span class="brand-mark">B</span>
                <span>Bari <i>&amp;</i> Saha</span>
                <span class="admin-badge">Admin</span>
            </div>
            <nav class="admin-nav">
                <a href="index.php" class="active">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="categories.php">Categories</a>
                <a href="orders.php">Orders</a>
                <a href="users.php">Users</a>
            </nav>
            <div class="admin-user">
                <span><?= htmlspecialchars($user['full_name']) ?></span>
                <a href="../logout.php">Logout</a>
                <a href="../store.php" style="margin-top: 10px; display: block; color: var(--color-gray-500); font-size: 14px;">View Store</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Dashboard</h1>
            </header>

            <div class="admin-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Users</h3>
                        <p class="stat-value"><?= number_format($usersCount) ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Orders</h3>
                        <p class="stat-value"><?= number_format($ordersCount) ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Products</h3>
                        <p class="stat-value"><?= number_format($productsCount) ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Revenue (Delivered/Confirmed)</h3>
                        <p class="stat-value">₹<?= number_format($totalRevenue, 2) ?></p>
                    </div>
                </div>

                <div class="recent-section">
                    <h2>Recent Orders</h2>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentOrders)): ?>
                                    <tr>
                                        <td colspan="5">No recent orders found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?= htmlspecialchars($order['id']) ?></td>
                                            <td><?= htmlspecialchars($order['full_name']) ?></td>
                                            <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                            <td><span class="status-badge status-<?= strtolower($order['status']) ?>"><?= ucfirst($order['status']) ?></span></td>
                                            <td><?= date('M d, Y h:i A', strtotime($order['ordered_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
