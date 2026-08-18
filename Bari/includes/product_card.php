<?php
$catImages = [
    1 => 'assets/images/categories/fruits_veg.jpg',
    2 => 'assets/images/categories/dairy.jpg',
    3 => 'assets/images/categories/snacks.jpg',
    4 => 'assets/images/categories/beverages.jpg',
    5 => 'assets/images/categories/bakery.jpg',
    6 => 'assets/images/categories/meat.jpg'
];
$fallbackImg = $catImages[$p['category_id']] ?? 'assets/images/categories/fruits_veg.jpg';
$imgSrc = (!empty($p['image_url']) && file_exists($p['image_url'])) ? $p['image_url'] : $fallbackImg;
?>
<article class="product-card" data-id="<?= $p['id'] ?>">
    <?php if ($p['badge']): ?><span class="p-badge badge-<?= $p['badge'] ?>"><?php $badgeText = ['sale'=>'Save now','new'=>'New in','organic'=>'Organic','bestseller'=>'Best seller']; echo $badgeText[$p['badge']] ?? $p['badge']; ?></span><?php endif; ?>
    <button class="p-wish" type="button" aria-label="Save <?= sanitize($p['name']) ?>">♡</button>
    <div class="p-image"><img src="<?= $imgSrc ?>" alt="<?= sanitize($p['name']) ?>" class="p-real-img" loading="lazy"></div>
    <div class="p-info"><p class="p-delivery">Ready in 30 min</p><h3 class="p-name"><?= sanitize($p['name']) ?></h3><p class="p-weight"><?= sanitize($p['weight']) ?></p><div class="p-bottom"><div class="p-pricing"><span class="p-price">₹<?= number_format($p['price']) ?></span><?php if ($p['old_price']): ?><span class="p-old-price">₹<?= number_format($p['old_price']) ?></span><?php endif; ?></div><button class="p-add-btn" onclick="addToCart(<?= $p['id'] ?>, this)" title="Add <?= sanitize($p['name']) ?> to cart"><span class="add-icon">+</span><span class="add-text">Add</span></button></div></div>
</article>
