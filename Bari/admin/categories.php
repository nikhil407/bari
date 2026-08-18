<?php
require_once '../config/db.php';

if (!isLoggedIn()) redirect('../login.php');
$user = getCurrentUser($pdo);
if (!$user || $user['is_admin'] != 1) die("Access denied.");

$message = '';

// Handle Category Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Optional: Check if category has products before deleting
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Category deleted successfully.";
    }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = sanitize($_POST['name']);
    $icon = sanitize($_POST['icon']);
    $color = sanitize($_POST['color']);
    $sort_order = (int)$_POST['sort_order'];

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE categories SET name=?, icon=?, color=?, sort_order=? WHERE id=?");
        $stmt->execute([$name, $icon, $color, $sort_order, $id]);
        $message = "Category updated successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, icon, color, sort_order) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $icon, $color, $sort_order]);
        $message = "Category added successfully.";
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();

$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editCat = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories — Admin</title>
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
                <a href="categories.php" class="active">Categories</a>
                <a href="orders.php">Orders</a>
                <a href="users.php">Users</a>
            </nav>
            <div class="admin-user"><span><?= htmlspecialchars($user['full_name']) ?></span><a href="../logout.php">Logout</a></div>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Categories</h1>
            </header>

            <div class="admin-content">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="form-panel">
                    <h2><?= $editCat ? 'Edit Category' : 'Add New Category' ?></h2>
                    <form method="POST" action="categories.php" class="admin-form">
                        <?php if ($editCat): ?>
                            <input type="hidden" name="id" value="<?= $editCat['id'] ?>">
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Category Name</label>
                                <input type="text" name="name" required value="<?= $editCat['name'] ?? '' ?>">
                            </div>
                            <div class="form-group">
                                <label>Icon (Emoji)</label>
                                <input type="text" name="icon" required value="<?= $editCat['icon'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Color (Hex)</label>
                                <input type="color" name="color" required value="<?= $editCat['color'] ?? '#22c55e' ?>">
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" required value="<?= $editCat['sort_order'] ?? 0 ?>">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary"><?= $editCat ? 'Update Category' : 'Add Category' ?></button>
                            <?php if ($editCat): ?>
                                <a href="categories.php" class="btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="table-panel">
                    <h2>Category List</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Color</th>
                                <th>Sort Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $c): ?>
                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><?= htmlspecialchars($c['icon'] . ' ' . $c['name']) ?></td>
                                <td>
                                    <div style="width: 20px; height: 20px; background: <?= htmlspecialchars($c['color']) ?>; border-radius: 50%; display: inline-block; vertical-align: middle;"></div>
                                    <?= htmlspecialchars($c['color']) ?>
                                </td>
                                <td><?= $c['sort_order'] ?></td>
                                <td>
                                    <a href="categories.php?edit=<?= $c['id'] ?>" class="btn-sm btn-edit">Edit</a>
                                    <a href="categories.php?delete=<?= $c['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Are you sure? Deleting this might break products linked to it.')">Delete</a>
                                </td>
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
