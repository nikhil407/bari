<?php
require_once '../config/db.php';

if (!isLoggedIn()) redirect('../login.php');
$user = getCurrentUser($pdo);
if (!$user || $user['is_admin'] != 1) die("Access denied.");

$users = $pdo->query("
    SELECT id, full_name, email, phone, city, is_admin, created_at
    FROM users
    ORDER BY created_at DESC
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-brand"><span class="brand-mark">B</span><span>Bari <i>&amp;</i> Saha</span></div>
            <nav class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="categories.php">Categories</a>
                <a href="orders.php">Orders</a>
                <a href="users.php" class="active">Users</a>
            </nav>
            <div class="admin-user"><span><?= htmlspecialchars($user['full_name']) ?></span><a href="../logout.php">Logout</a></div>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Users</h1>
            </header>

            <div class="admin-content">
                <div class="table-panel">
                    <h2>Registered Users</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>City</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['full_name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['phone']) ?></td>
                                <td><?= htmlspecialchars($u['city']) ?></td>
                                <td>
                                    <?php if ($u['is_admin'] == 1): ?>
                                        <span class="status-badge" style="background:#e0e7ff; color:#3730a3;">Admin</span>
                                    <?php else: ?>
                                        <span class="status-badge" style="background:#f3f4f6; color:#374151;">User</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
