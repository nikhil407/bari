<?php
require_once '../config/db.php';

if (!isLoggedIn()) redirect('../login.php');
$user = getCurrentUser($pdo);
if (!$user || $user['is_admin'] != 1) die("Access denied.");

$message = '';

// Handle Product Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Product deleted successfully.";
    }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $category_id = (int)$_POST['category_id'];
    $name = sanitize($_POST['name']);
    $emoji = sanitize($_POST['emoji']);
    $weight = sanitize($_POST['weight']);
    $price = (float)$_POST['price'];
    $old_price = !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null;
    $badge = !empty($_POST['badge']) ? sanitize($_POST['badge']) : null;
    $stock = (int)$_POST['stock'];

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE products SET category_id=?, name=?, emoji=?, weight=?, price=?, old_price=?, badge=?, stock=? WHERE id=?");
        $stmt->execute([$category_id, $name, $emoji, $weight, $price, $old_price, $badge, $stock, $id]);
        $message = "Product updated successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (category_id, name, emoji, weight, price, old_price, badge, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$category_id, $name, $emoji, $weight, $price, $old_price, $badge, $stock]);
        $message = "Product added successfully.";
    }
}

// Fetch all products
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
// Fetch categories for form
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY sort_order")->fetchAll();

$editProduct = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editProduct = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-brand"><span class="brand-mark">B</span><span>Bari <i>&amp;</i> Saha</span></div>
            <nav class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="products.php" class="active">Products</a>
                <a href="categories.php">Categories</a>
                <a href="orders.php">Orders</a>
                <a href="users.php">Users</a>
            </nav>
            <div class="admin-user"><span><?= htmlspecialchars($user['full_name']) ?></span><a href="../logout.php">Logout</a></div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Products</h1>
            </header>

            <div class="admin-content">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="form-panel">
                    <h2><?= $editProduct ? 'Edit Product' : 'Add New Product' ?></h2>
                    <form method="POST" action="products.php" class="admin-form">
                        <?php if ($editProduct): ?>
                            <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Product Name</label>
                                <input type="text" name="name" required value="<?= $editProduct['name'] ?? '' ?>">
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($editProduct && $editProduct['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Emoji</label>
                                <input type="text" name="emoji" required value="<?= $editProduct['emoji'] ?? '' ?>">
                            </div>
                            <div class="form-group">
                                <label>Weight/Quantity (e.g. 1 kg)</label>
                                <input type="text" name="weight" required value="<?= $editProduct['weight'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Price (₹)</label>
                                <input type="number" step="0.01" name="price" required value="<?= $editProduct['price'] ?? '' ?>">
                            </div>
                            <div class="form-group">
                                <label>Old Price (₹) - Optional</label>
                                <input type="number" step="0.01" name="old_price" value="<?= $editProduct['old_price'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Badge</label>
                                <select name="badge">
                                    <option value="">None</option>
                                    <option value="sale" <?= ($editProduct && $editProduct['badge'] == 'sale') ? 'selected' : '' ?>>Sale</option>
                                    <option value="new" <?= ($editProduct && $editProduct['badge'] == 'new') ? 'selected' : '' ?>>New</option>
                                    <option value="organic" <?= ($editProduct && $editProduct['badge'] == 'organic') ? 'selected' : '' ?>>Organic</option>
                                    <option value="bestseller" <?= ($editProduct && $editProduct['badge'] == 'bestseller') ? 'selected' : '' ?>>Bestseller</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Stock</label>
                                <input type="number" name="stock" required value="<?= $editProduct['stock'] ?? 100 ?>">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary"><?= $editProduct ? 'Update Product' : 'Add Product' ?></button>
                            <?php if ($editProduct): ?>
                                <a href="products.php" class="btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="table-panel">
                    <h2>Product List</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><?= htmlspecialchars($p['emoji'] . ' ' . $p['name']) ?></td>
                                <td><?= htmlspecialchars($p['category_name']) ?></td>
                                <td>₹<?= $p['price'] ?></td>
                                <td><?= $p['stock'] ?></td>
                                <td>
                                    <a href="products.php?edit=<?= $p['id'] ?>" class="btn-sm btn-edit">Edit</a>
                                    <a href="products.php?delete=<?= $p['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
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
