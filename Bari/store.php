<?php
require_once 'config/db.php';

$cats = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$catFilter = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';

if ($search) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND (p.name LIKE ? OR c.name LIKE ?) ORDER BY c.sort_order, p.name");
    $stmt->execute(["%$search%", "%$search%"]);
} elseif ($catFilter) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND p.category_id = ? ORDER BY p.name");
    $stmt->execute([$catFilter]);
} else {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY c.sort_order, p.name");
}
$products = $stmt->fetchAll();
$cartCount = getCartCount($pdo);
$user = getCurrentUser($pdo);
$activeCat = $catFilter;
$catalogByCategory = [];
foreach ($products as $product) { $catalogByCategory[$product['category_name']][] = $product; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bari &amp; Saha — Fresh groceries, thoughtfully delivered</title>
    <meta name="description" content="A better way to buy everyday groceries. Fresh produce, pantry staples and more at your door in 30 minutes.">
    <meta name="theme-color" content="#102f22">
    <meta property="og:title" content="Bari &amp; Saha — Fresh groceries, thoughtfully delivered">
    <meta property="og:description" content="Fresh food and everyday essentials, delivered to your door in as little as 30 minutes.">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/store.css">
    <script type="application/ld+json">{"@context":"https://schema.org","@type":"GroceryStore","name":"Bari & Saha","description":"Fresh groceries and everyday essentials delivered locally.","currenciesAccepted":"INR","priceRange":"₹₹"}</script>
</head>
<body>
    <div class="announcement-bar"><div class="container"><span>Fresh from local farms</span><span class="announcement-dot"></span><span>Free delivery on orders over ₹499</span><span class="announcement-dot"></span><span>Delivered in as little as 30 minutes</span></div></div>
    <nav class="navbar" id="navbar">
        <div class="container nav-inner">
            <a href="store.php" class="logo"><span class="logo-mark">B</span><div class="logo-info"><span class="logo-name">Bari <i>&amp;</i> Saha</span><span class="logo-tagline">Your neighbourhood market</span></div></a>
            <div class="nav-location"><span class="loc-icon">⌖</span><div><span class="loc-label">Delivering to</span><span class="loc-address"><?= $user ? sanitize($user['city']) : 'Mumbai' ?></span></div></div>
            <div class="nav-search"><form method="GET" action="store.php" class="search-form"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input type="text" name="q" placeholder="Search fresh fruits, milk, pantry essentials…" value="<?= $search ?>" id="searchInput"><?php if ($search): ?><a href="store.php" class="search-clear" aria-label="Clear search">×</a><?php endif; ?></form></div>
            <div class="nav-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="orders.php" class="nav-action-btn"><span class="nav-symbol">□</span><span class="nav-action-label">Orders</span></a>
                    <div class="nav-user-dropdown"><button class="nav-action-btn" id="userBtn"><span class="nav-symbol">◯</span><span class="nav-action-label"><?= sanitize(explode(' ', $user['full_name'])[0]) ?></span></button><div class="dropdown-menu" id="userDropdown"><div class="dropdown-header"><strong><?= sanitize($user['full_name']) ?></strong><small><?= sanitize($user['email']) ?></small></div><a href="orders.php">My orders</a><a href="logout.php">Sign out</a></div></div>
                <?php else: ?>
                    <a href="login.php" class="nav-action-btn login-btn"><span class="nav-symbol">◯</span><span class="nav-action-label">Sign in</span></a>
                <?php endif; ?>
                <a href="cart.php" class="nav-cart-btn" id="navCartBtn"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg><span class="nav-cart-label">Cart</span><?php if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?></a>
            </div><button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Open menu"><span></span><span></span><span></span></button>
        </div>
    </nav>
    <div class="mobile-menu" id="mobileMenu"><form method="GET" action="store.php" class="mobile-search"><input type="text" name="q" placeholder="Search the market"><button type="submit">Search</button></form><div class="mobile-menu-links"><a href="store.php">Shop all</a><a href="cart.php">Cart (<?= $cartCount ?>)</a><?php if (isLoggedIn()): ?><a href="orders.php">My orders</a><a href="logout.php">Sign out</a><?php else: ?><a href="login.php">Sign in</a><a href="register.php">Create an account</a><?php endif; ?></div></div>
    <main class="main-content">
        <section class="categories-strip"><div class="container"><div class="cat-scroll" id="catScroll">
            <?php
            $catImages = [
                1 => 'fruits.jpg',
                2 => 'dairy.jpg',
                3 => 'snacks.jpg',
                4 => 'bakery.jpg',
                5 => 'beverages.jpg',
                6 => 'meat.jpg'
            ];
            ?>
            <a href="store.php" class="cat-chip <?= !$activeCat && !$search ? 'active' : '' ?>">
                <div class="cat-chip-img"><span></span></div>
                <span>All groceries</span>
            </a>
            <?php foreach ($cats as $cat): ?>
            <a href="store.php?cat=<?= $cat['id'] ?>" class="cat-chip <?= $activeCat == $cat['id'] ? 'active' : '' ?>">
                <?php if (isset($catImages[$cat['id']])): ?>
                <div class="cat-chip-img"><img src="assets/images/categories/<?= $catImages[$cat['id']] ?>" alt="<?= sanitize($cat['name']) ?>"></div>
                <?php else: ?>
                <span class="cat-chip-icon"><?= $cat['icon'] ?></span>
                <?php endif; ?>
                <span><?= sanitize($cat['name']) ?></span>
            </a>
            <?php endforeach; ?>
        </div></div></section>
        <?php if (!$search && !$catFilter): ?>
        <section class="hero-market"><div class="container hero-grid"><div class="hero-copy"><p class="eyebrow"><span></span>THE GOODNESS EDIT</p><h1>Good food,<br><em>beautifully</em> easy.</h1><p class="hero-text">The pantry, produce and everyday things you love — handpicked fresh and brought to your door.</p><div class="hero-actions"><a href="#shop" class="hero-button">Shop the market <span>→</span></a><a href="#categories" class="hero-link">Explore departments <span>↓</span></a></div><div class="hero-meta"><div><strong>30 min</strong><span>average delivery</span></div><div><strong>10k+</strong><span>happy households</span></div><div><strong>4.9/5</strong><span>neighbour rating</span></div></div></div><div class="hero-art"><img src="assets/images/bari-grocery-hero.png" alt="A market basket filled with fresh groceries"><span class="hero-note note-top">Picked this morning</span><span class="hero-note note-bottom">Always fresh, always local</span><span class="hero-quality"><b>100%</b> freshness<br>promise</span></div></div></section>
        <section class="benefit-strip"><div class="container benefit-grid"><div><span>01</span><p><strong>Peak freshness</strong> thoughtfully sourced daily</p></div><div><span>02</span><p><strong>Better value</strong> everyday fair pricing</p></div><div><span>03</span><p><strong>At your door</strong> in as little as 30 mins</p></div></div></section>
        <section class="department-section" id="categories"><div class="container"><div class="section-heading"><div><p class="eyebrow">SHOP BY DEPARTMENT</p><h2>Everything for your everyday</h2></div><p>Fresh finds, pantry favourites and more — all in one thoughtful market.</p></div><div class="department-grid"><?php foreach ($cats as $index => $cat): ?><a href="store.php?cat=<?= $cat['id'] ?>" class="department-card department-<?= ($index % 4) + 1 ?>"><span class="department-icon"><?= $cat['icon'] ?></span><div><h3><?= sanitize($cat['name']) ?></h3><span>Shop now <b>→</b></span></div></a><?php endforeach; ?></div></div></section>
        <section class="offer-section"><div class="container"><div class="offer-card"><div><p class="eyebrow">THIS WEEK’S FAVOURITE</p><h2>Sweeten your<br>everyday <em>ritual.</em></h2><p>Save up to 25% on handpicked fruit, fresh dairy and breakfast essentials.</p><a href="store.php?cat=1">Discover the offer <span>→</span></a></div><div class="offer-fruit"><span>🍊</span><span>🍎</span><span>🍓</span><span>🍌</span></div></div></div></section>
        <?php endif; ?>
        <section class="products-section" id="shop"><div class="container">
            <?php if ($search): ?><div class="results-header"><p class="eyebrow">SEARCH RESULTS</p><h2>Results for “<?= $search ?>”</h2><p><?= count($products) ?> products found</p></div><?php elseif ($catFilter): $catName = ''; foreach ($cats as $c) { if ($c['id'] == $catFilter) { $catName = $c['name']; break; } } ?><div class="results-header"><p class="eyebrow">SHOP DEPARTMENT</p><h2><?= sanitize($catName) ?></h2><p><?= count($products) ?> products, ready when you are</p></div><?php else: ?><div class="catalog-heading"><div><p class="eyebrow">THE FULL MARKET</p><h2>Stock up on the good stuff.</h2></div><p>Every product is listed below, organised by department.</p></div><?php endif; ?>
            <?php if (empty($products)): ?><div class="empty-state"><span>⌕</span><h3>We couldn’t find that</h3><p>Try a different search, or browse our departments.</p><a href="store.php" class="back-link">Back to the market</a></div><?php elseif (!$search && !$catFilter): foreach ($catalogByCategory as $catName => $catProducts): ?><div class="product-group" id="category-<?= $catProducts[0]['category_id'] ?>"><div class="group-header"><div><p class="group-kicker"><?= count($catProducts) ?> ITEMS</p><h2><?= sanitize($catName) ?></h2></div><a href="store.php?cat=<?= $catProducts[0]['category_id'] ?>" class="see-all">Browse department <span>→</span></a></div><div class="products-grid"><?php foreach ($catProducts as $p): include 'includes/product_card.php'; endforeach; ?></div></div><?php endforeach; else: ?><div class="products-grid"><?php foreach ($products as $p): include 'includes/product_card.php'; endforeach; ?></div><?php endif; ?>
        </div></section>
        <?php if (!$search && !$catFilter): ?><section class="newsletter-section"><div class="container newsletter-card"><div><p class="eyebrow">THE WEEKLY BASKET</p><h2>A little more good<br>in your inbox.</h2></div><p>Recipes, seasonal picks and first access to our best offers — only the nice stuff.</p><form onsubmit="event.preventDefault(); this.querySelector('button').textContent='You’re on the list!';"><input type="email" aria-label="Email address" placeholder="Your email address" required><button type="submit">Subscribe →</button></form></div></section><?php endif; ?>
    </main>
    <footer class="store-footer"><div class="container"><div class="footer-top"><div><a href="store.php" class="logo"><span class="logo-mark">B</span><span class="logo-name">Bari <i>&amp;</i> Saha</span></a><p>Modern groceries for the way you live now.</p></div><div><h4>Shop</h4><a href="#shop">All groceries</a><a href="#categories">Departments</a><a href="cart.php">My cart</a></div><div><h4>Account</h4><a href="login.php">Sign in</a><a href="register.php">Create account</a><a href="orders.php">My orders</a></div><div><h4>Need a hand?</h4><a href="mailto:hello@bariandsaha.local">hello@bariandsaha.local</a><a href="tel:+910000000000">+91 00000 00000</a></div></div><div class="footer-bottom"><span>© <?= date('Y') ?> Bari &amp; Saha. Made for good living.</span><span>Secure payments · Freshness promise</span></div></div></footer>
    <div class="toast-container" id="toastContainer"></div><script src="js/store.js"></script>
</body></html>
