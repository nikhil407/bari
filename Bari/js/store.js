// ===== STORE JS =====

// User Dropdown
const userBtn = document.getElementById('userBtn');
const userDropdown = document.getElementById('userDropdown');
if (userBtn) {
    userBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
    });
    document.addEventListener('click', () => userDropdown.classList.remove('show'));
}

// Mobile Menu
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const mobileMenu = document.getElementById('mobileMenu');
if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', () => {
        mobileMenuBtn.classList.toggle('active');
        mobileMenu.classList.toggle('show');
    });
}

// Add to Cart
function addToCart(productId, btn) {
    fetch('api/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&product_id=${productId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'login_required') {
            window.location.href = 'login.php';
            return;
        }
        if (data.status === 'success') {
            // Animate button
            btn.classList.add('added');
            btn.innerHTML = '<span class="add-icon">✓</span><span class="add-text">ADDED</span>';
            setTimeout(() => {
                btn.classList.remove('added');
                btn.innerHTML = '<span class="add-icon">+</span><span class="add-text">ADD</span>';
            }, 1500);

            // Update cart badge
            updateCartBadge(data.cart_count);
            showToast(`${data.message}`);
        } else {
            showToast(data.message || 'Error adding to cart');
        }
    })
    .catch(() => showToast('Network error. Try again.'));
}

function updateCartBadge(count) {
    const cartBtn = document.getElementById('navCartBtn');
    if (!cartBtn) return;

    let badge = cartBtn.querySelector('.cart-badge');
    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            cartBtn.appendChild(badge);
        }
        badge.textContent = count;
        badge.style.animation = 'none';
        badge.offsetHeight; // Force reflow
        badge.style.animation = '';
    } else if (badge) {
        badge.remove();
    }
}

// Update cart quantity (for cart page)
function updateQty(productId, delta) {
    fetch('api/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&product_id=${productId}&delta=${delta}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        }
    });
}

function removeFromCart(productId) {
    fetch('api/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&product_id=${productId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        }
    });
}

function reorderOrder(orderId, btn) {
    if (btn) { btn.disabled = true; btn.textContent = 'Adding…'; }
    fetch('api/cart_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=reorder&order_id=${orderId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            updateCartBadge(data.cart_count);
            showToast(data.message);
            if (btn) btn.textContent = 'Added to cart';
        } else {
            showToast(data.message || 'Unable to add order');
            if (btn) { btn.disabled = false; btn.textContent = 'Order again'; }
        }
    })
    .catch(() => {
        showToast('Network error. Try again.');
        if (btn) { btn.disabled = false; btn.textContent = 'Order again'; }
    });
}

// Toast
function showToast(msg) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<span>✅</span> ${msg}`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('out');
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

// Card stagger animation
document.querySelectorAll('.product-card').forEach((card, i) => {
    card.style.animationDelay = `${i * 0.04}s`;
});

// Navbar scroll
window.addEventListener('scroll', () => {
    const navbar = document.getElementById('navbar');
    if (navbar) {
        navbar.style.boxShadow = window.scrollY > 10 ? '0 4px 20px rgba(0,0,0,0.08)' : '0 1px 3px rgba(0,0,0,0.04)';
    }
});
