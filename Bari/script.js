// ===== PRODUCT DATA =====
const products = [
    { id: 1, name: "Red Apples", emoji: "🍎", category: "fruits", weight: "1 kg", price: 180, oldPrice: 220, badge: "sale" },
    { id: 2, name: "Fresh Broccoli", emoji: "🥦", category: "vegetables", weight: "500 g", price: 90, oldPrice: null, badge: "organic" },
    { id: 3, name: "Farm Milk", emoji: "🥛", category: "dairy", weight: "1 litre", price: 65, oldPrice: 80, badge: "sale" },
    { id: 4, name: "Organic Bananas", emoji: "🍌", category: "fruits", weight: "1 dozen", price: 55, oldPrice: null, badge: "organic" },
    { id: 5, name: "Baby Spinach", emoji: "🥬", category: "vegetables", weight: "250 g", price: 45, oldPrice: 60, badge: "new" },
    { id: 6, name: "Farm Eggs", emoji: "🥚", category: "dairy", weight: "12 pcs", price: 95, oldPrice: 110, badge: "sale" },
    { id: 7, name: "Juicy Oranges", emoji: "🍊", category: "fruits", weight: "1 kg", price: 120, oldPrice: null, badge: null },
    { id: 8, name: "Fresh Carrots", emoji: "🥕", category: "vegetables", weight: "500 g", price: 40, oldPrice: 55, badge: "sale" },
    { id: 9, name: "Greek Yogurt", emoji: "🫙", category: "dairy", weight: "400 g", price: 110, oldPrice: null, badge: "new" },
    { id: 10, name: "Avocados", emoji: "🥑", category: "organic", weight: "2 pcs", price: 199, oldPrice: 250, badge: "organic" },
    { id: 11, name: "Blueberries", emoji: "🫐", category: "fruits", weight: "150 g", price: 220, oldPrice: 280, badge: "sale" },
    { id: 12, name: "Cherry Tomatoes", emoji: "🍅", category: "vegetables", weight: "250 g", price: 75, oldPrice: null, badge: "organic" },
    { id: 13, name: "Cheddar Cheese", emoji: "🧀", category: "dairy", weight: "200 g", price: 160, oldPrice: 190, badge: null },
    { id: 14, name: "Green Grapes", emoji: "🍇", category: "fruits", weight: "500 g", price: 145, oldPrice: null, badge: "new" },
    { id: 15, name: "Organic Honey", emoji: "🍯", category: "organic", weight: "500 g", price: 350, oldPrice: 420, badge: "organic" },
    { id: 16, name: "Fresh Corn", emoji: "🌽", category: "vegetables", weight: "4 pcs", price: 60, oldPrice: null, badge: null },
];

// ===== CART STATE =====
let cart = [];

// ===== DOM ELEMENTS =====
const $ = (sel) => document.querySelector(sel);
const $$ = (sel) => document.querySelectorAll(sel);

// ===== PRELOADER =====
window.addEventListener('load', () => {
    setTimeout(() => {
        $('#preloader').classList.add('hidden');
    }, 1800);
});

// ===== NAVBAR =====
const navbar = $('#navbar');
const hamburger = $('#hamburger');
const navLinks = $('#navLinks');

// Scroll effect
window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 50);

    // Back to top visibility
    const backToTop = $('#backToTop');
    backToTop.classList.toggle('visible', window.scrollY > 400);

    // Active nav link based on scroll
    updateActiveNav();
});

// Hamburger toggle
hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navLinks.classList.toggle('active');
});

// Close mobile menu on link click
$$('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navLinks.classList.remove('active');
    });
});

// Active nav link on scroll
function updateActiveNav() {
    const sections = $$('section[id]');
    const scrollPos = window.scrollY + 150;

    sections.forEach(section => {
        const top = section.offsetTop;
        const height = section.offsetHeight;
        const id = section.getAttribute('id');
        const link = $(`.nav-link[href="#${id}"]`);

        if (link) {
            if (scrollPos >= top && scrollPos < top + height) {
                $$('.nav-link').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        }
    });
}

// ===== SEARCH =====
const searchToggle = $('#searchToggle');
const searchOverlay = $('#searchOverlay');
const searchClose = $('#searchClose');
const searchInput = $('#searchInput');

searchToggle.addEventListener('click', () => {
    searchOverlay.classList.toggle('active');
    if (searchOverlay.classList.contains('active')) {
        setTimeout(() => searchInput.focus(), 300);
    }
});

searchClose.addEventListener('click', () => {
    searchOverlay.classList.remove('active');
    searchInput.value = '';
});

// Search functionality
searchInput.addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase().trim();
    const cards = $$('.product-card');

    cards.forEach(card => {
        const name = card.querySelector('.product-name').textContent.toLowerCase();
        const category = card.dataset.category;
        const match = name.includes(query) || category.includes(query);
        card.style.display = match || query === '' ? '' : 'none';
    });
});

// ===== CART =====
const cartToggle = $('#cartToggle');
const cartOverlay = $('#cartOverlay');
const cartSidebar = $('#cartSidebar');
const cartClose = $('#cartClose');
const cartItems = $('#cartItems');
const cartFooter = $('#cartFooter');
const cartCount = $('#cartCount');
const cartTotal = $('#cartTotal');

function openCart() {
    cartOverlay.classList.add('active');
    cartSidebar.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeCart() {
    cartOverlay.classList.remove('active');
    cartSidebar.classList.remove('active');
    document.body.style.overflow = '';
}

cartToggle.addEventListener('click', openCart);
cartClose.addEventListener('click', closeCart);
cartOverlay.addEventListener('click', closeCart);

function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    const existing = cart.find(item => item.id === productId);
    if (existing) {
        existing.qty++;
    } else {
        cart.push({ ...product, qty: 1 });
    }

    updateCart();
    showToast(`${product.emoji} ${product.name} added to cart!`);

    // Bump animation on cart count
    cartCount.classList.remove('bump');
    void cartCount.offsetWidth; // Force reflow
    cartCount.classList.add('bump');
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCart();
}

function updateQty(productId, delta) {
    const item = cart.find(i => i.id === productId);
    if (!item) return;

    item.qty += delta;
    if (item.qty <= 0) {
        removeFromCart(productId);
        return;
    }
    updateCart();
}

function updateCart() {
    const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
    const totalPrice = cart.reduce((sum, item) => sum + item.price * item.qty, 0);

    cartCount.textContent = totalItems;

    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="cart-empty">
                <span class="cart-empty-icon">🛒</span>
                <p>Your cart is empty</p>
                <small>Add some fresh groceries!</small>
            </div>
        `;
        cartFooter.style.display = 'none';
    } else {
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-emoji">${item.emoji}</div>
                <div class="cart-item-info">
                    <h4>${item.name}</h4>
                    <span class="cart-item-price">₹${item.price * item.qty}</span>
                </div>
                <div class="cart-item-controls">
                    <button onclick="updateQty(${item.id}, -1)">−</button>
                    <span>${item.qty}</span>
                    <button onclick="updateQty(${item.id}, 1)">+</button>
                </div>
            </div>
        `).join('');
        cartFooter.style.display = 'block';
    }

    cartTotal.textContent = `₹${totalPrice}`;
}

// ===== RENDER PRODUCTS =====
function renderProducts(filter = 'all') {
    const grid = $('#productsGrid');
    const filtered = filter === 'all' ? products : products.filter(p => p.category === filter);

    grid.innerHTML = filtered.map((p, i) => `
        <div class="product-card show" data-category="${p.category}" style="animation-delay: ${i * 0.05}s">
            ${p.badge ? `<span class="product-badge badge-${p.badge}">${p.badge === 'sale' ? '🔥 Sale' : p.badge === 'new' ? '✨ New' : '🌿 Organic'}</span>` : ''}
            <div class="product-image">
                <span class="product-emoji">${p.emoji}</span>
                <div class="product-quick-add">
                    <button class="btn btn-primary btn-sm" onclick="addToCart(${p.id})">+ Add to Cart</button>
                </div>
            </div>
            <div class="product-info">
                <div class="product-category-label">${p.category}</div>
                <h3 class="product-name">${p.name}</h3>
                <p class="product-weight">${p.weight}</p>
                <div class="product-bottom">
                    <div class="product-price">
                        <span class="price-current">₹${p.price}</span>
                        ${p.oldPrice ? `<span class="price-old">₹${p.oldPrice}</span>` : ''}
                    </div>
                    <button class="product-add-btn" onclick="addToCart(${p.id})" aria-label="Add ${p.name} to cart">+</button>
                </div>
            </div>
        </div>
    `).join('');
}

// Initial render
renderProducts();

// Product filter buttons
$$('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        $$('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderProducts(btn.dataset.filter);
    });
});

// Category cards click
$$('.category-card').forEach(card => {
    card.addEventListener('click', () => {
        const category = card.dataset.category;
        // Scroll to products section
        $('#products').scrollIntoView({ behavior: 'smooth' });

        // Activate the correct filter button
        setTimeout(() => {
            const filterBtn = $(`.filter-btn[data-filter="${category}"]`);
            if (filterBtn) {
                $$('.filter-btn').forEach(b => b.classList.remove('active'));
                filterBtn.classList.add('active');
                renderProducts(category);
            }
        }, 500);
    });
});

// ===== COUNTDOWN TIMER =====
function updateTimer() {
    const now = new Date();
    const endOfDay = new Date();
    endOfDay.setHours(23, 59, 59, 999);

    const diff = endOfDay - now;
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    $('#hours').textContent = String(hours).padStart(2, '0');
    $('#minutes').textContent = String(minutes).padStart(2, '0');
    $('#seconds').textContent = String(seconds).padStart(2, '0');
}

updateTimer();
setInterval(updateTimer, 1000);

// ===== COUNTER ANIMATION =====
function animateCounters() {
    $$('.stat-num[data-count]').forEach(counter => {
        const target = parseInt(counter.dataset.count);
        const duration = 2000;
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
            const current = Math.floor(eased * target);
            counter.textContent = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }

        requestAnimationFrame(update);
    });
}

// ===== SCROLL ANIMATIONS =====
const observerOptions = {
    threshold: 0.15,
    rootMargin: '0px 0px -50px 0px'
};

const scrollObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animated');

            // Trigger counter animation when hero stats appear
            if (entry.target.closest('.hero-stats')) {
                animateCounters();
            }

            scrollObserver.unobserve(entry.target);
        }
    });
}, observerOptions);

$$('.animate-on-scroll').forEach(el => scrollObserver.observe(el));

// ===== TOAST NOTIFICATIONS =====
function showToast(message) {
    const container = $('#toastContainer');
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<span class="toast-icon">✅</span><span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('toast-out');
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

// ===== NEWSLETTER FORM =====
$('#newsletterForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const email = $('#emailInput').value;
    if (email) {
        showToast(`🎉 Welcome! You've subscribed with ${email}`);
        $('#emailInput').value = '';
    }
});

// ===== CHECKOUT =====
$('#checkoutBtn').addEventListener('click', () => {
    if (cart.length > 0) {
        const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
        showToast(`🎉 Order placed! Total: ₹${total}. Thank you!`);
        cart = [];
        updateCart();
        closeCart();
    }
});

// ===== BACK TO TOP =====
$('#backToTop').addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// ===== KEYBOARD SHORTCUTS =====
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        searchOverlay.classList.remove('active');
        closeCart();
        hamburger.classList.remove('active');
        navLinks.classList.remove('active');
    }

    if (e.key === '/' && !e.ctrlKey && !e.metaKey) {
        const active = document.activeElement;
        if (active.tagName !== 'INPUT' && active.tagName !== 'TEXTAREA') {
            e.preventDefault();
            searchOverlay.classList.add('active');
            setTimeout(() => searchInput.focus(), 300);
        }
    }
});

// ===== SMOOTH SCROLL FOR ALL INTERNAL LINKS =====
$$('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
        const href = anchor.getAttribute('href');
        if (href !== '#') {
            e.preventDefault();
            const target = $(href);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
});
