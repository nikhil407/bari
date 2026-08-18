-- ============================================
-- Bari & Saha Grocery Store Database
-- ============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS bari_saha_grocery
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE bari_saha_grocery;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    city VARCHAR(50) DEFAULT 'Mumbai',
    pincode VARCHAR(10),
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin User (Password: admin123)
INSERT INTO users (full_name, email, phone, password, address, city, pincode, is_admin) VALUES
('Admin', 'admin@example.com', '0000000000', '$2y$10$V2RQlnxgyTWzWhx0v/4USebsk3PCxGOjr0yXMvZm3m14lo8I4rti6', 'Admin Address', 'Mumbai', '000000', 1);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(10) NOT NULL,
    color VARCHAR(20) DEFAULT '#22c55e',
    sort_order INT DEFAULT 0
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    emoji VARCHAR(10) NOT NULL,
    weight VARCHAR(30) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2) DEFAULT NULL,
    badge ENUM('sale','new','organic','bestseller') DEFAULT NULL,
    stock INT DEFAULT 100,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Cart Table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_address TEXT NOT NULL,
    phone VARCHAR(15) NOT NULL,
    status ENUM('pending','confirmed','preparing','out_for_delivery','delivered','cancelled') DEFAULT 'pending',
    payment_method ENUM('cod','upi','card') DEFAULT 'cod',
    notes TEXT,
    ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    product_emoji VARCHAR(10) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- ============================================
-- INSERT CATEGORIES
-- ============================================
INSERT INTO categories (name, icon, color, sort_order) VALUES
('Fruits & Vegetables', '🥬', '#22c55e', 1),
('Dairy & Breakfast', '🥛', '#f59e0b', 2),
('Snacks & Munchies', '🍿', '#ef4444', 3),
('Bakery & Biscuits', '🍞', '#d4a574', 4),
('Beverages', '🧃', '#3b82f6', 5),
('Meat & Fish', '🥩', '#e8590c', 6),
('Atta, Rice & Dal', '🌾', '#a3711a', 7),
('Cleaning & Household', '🧴', '#8b5cf6', 8);

-- ============================================
-- INSERT PRODUCTS
-- ============================================

-- Fruits & Vegetables (category_id = 1)
INSERT INTO products (category_id, name, emoji, weight, price, old_price, badge) VALUES
(1, 'Fresh Red Apples', '🍎', '1 kg', 180.00, 220.00, 'sale'),
(1, 'Organic Bananas', '🍌', '1 dozen', 55.00, NULL, 'organic'),
(1, 'Baby Spinach', '🥬', '250 g', 45.00, 60.00, 'new'),
(1, 'Fresh Broccoli', '🥦', '500 g', 90.00, NULL, 'organic'),
(1, 'Juicy Oranges', '🍊', '1 kg', 120.00, 150.00, 'sale'),
(1, 'Fresh Carrots', '🥕', '500 g', 40.00, 55.00, NULL),
(1, 'Cherry Tomatoes', '🍅', '250 g', 75.00, NULL, 'organic'),
(1, 'Avocados', '🥑', '2 pcs', 199.00, 250.00, 'sale'),
(1, 'Green Grapes', '🍇', '500 g', 145.00, NULL, 'new'),
(1, 'Blueberries', '🫐', '150 g', 220.00, 280.00, 'bestseller'),
(1, 'Fresh Corn', '🌽', '4 pcs', 60.00, NULL, NULL),
(1, 'Green Capsicum', '🫑', '250 g', 35.00, 45.00, NULL),
(1, 'Potatoes', '🥔', '1 kg', 30.00, NULL, NULL),
(1, 'Onions', '🧅', '1 kg', 35.00, 50.00, 'sale'),
(1, 'Garlic', '🧄', '250 g', 55.00, NULL, NULL),
(1, 'Lemon', '🍋', '500 g', 40.00, NULL, NULL);

-- Dairy & Breakfast (category_id = 2)
INSERT INTO products (category_id, name, emoji, weight, price, old_price, badge) VALUES
(2, 'Farm Fresh Milk', '🥛', '1 litre', 65.00, 80.00, 'sale'),
(2, 'Farm Eggs', '🥚', '12 pcs', 95.00, 110.00, 'bestseller'),
(2, 'Cheddar Cheese', '🧀', '200 g', 160.00, 190.00, NULL),
(2, 'Greek Yogurt', '🫙', '400 g', 110.00, NULL, 'new'),
(2, 'Butter (Amul)', '🧈', '500 g', 280.00, 310.00, 'sale'),
(2, 'Paneer', '🧊', '200 g', 90.00, NULL, 'bestseller'),
(2, 'Bread - White', '🍞', '400 g', 40.00, NULL, NULL),
(2, 'Corn Flakes', '🥣', '500 g', 185.00, 220.00, 'sale');

-- Snacks & Munchies (category_id = 3)
INSERT INTO products (category_id, name, emoji, weight, price, old_price, badge) VALUES
(3, 'Classic Chips', '🍟', '150 g', 40.00, NULL, NULL),
(3, 'Popcorn', '🍿', '100 g', 50.00, NULL, 'new'),
(3, 'Mixed Nuts', '🥜', '250 g', 299.00, 350.00, 'sale'),
(3, 'Dark Chocolate', '🍫', '100 g', 120.00, NULL, 'bestseller'),
(3, 'Cookies', '🍪', '200 g', 85.00, 100.00, NULL),
(3, 'Namkeen Mix', '🫘', '400 g', 110.00, 140.00, 'sale');

-- Bakery & Biscuits (category_id = 4)
INSERT INTO products (category_id, name, emoji, weight, price, old_price, badge) VALUES
(4, 'Whole Wheat Bread', '🍞', '400 g', 45.00, NULL, 'organic'),
(4, 'Croissants', '🥐', '4 pcs', 180.00, 220.00, 'new'),
(4, 'Chocolate Cake', '🎂', '500 g', 450.00, 550.00, 'bestseller'),
(4, 'Digestive Biscuits', '🍪', '300 g', 65.00, NULL, NULL),
(4, 'Muffins', '🧁', '4 pcs', 160.00, 200.00, 'sale');

-- Beverages (category_id = 5)
INSERT INTO products (category_id, name, emoji, weight, price, old_price, badge) VALUES
(5, 'Orange Juice', '🧃', '1 litre', 130.00, 160.00, 'sale'),
(5, 'Green Tea', '🍵', '25 bags', 175.00, NULL, 'organic'),
(5, 'Coffee Powder', '☕', '200 g', 250.00, 300.00, 'bestseller'),
(5, 'Coconut Water', '🥥', '1 litre', 60.00, NULL, 'new'),
(5, 'Mango Lassi', '🥭', '200 ml', 35.00, NULL, NULL),
(5, 'Cold Drink', '🥤', '750 ml', 40.00, 45.00, NULL);

-- Meat & Fish (category_id = 6)
INSERT INTO products (category_id, name, emoji, weight, price, old_price, badge) VALUES
(6, 'Chicken Breast', '🍗', '500 g', 220.00, 260.00, 'sale'),
(6, 'Mutton Curry Cut', '🥩', '500 g', 550.00, 620.00, 'bestseller'),
(6, 'Fresh Prawns', '🦐', '250 g', 320.00, NULL, 'new'),
(6, 'Fish Fillet', '🐟', '500 g', 380.00, 420.00, NULL),
(6, 'Eggs (30 pcs)', '🥚', '30 pcs', 210.00, 250.00, 'sale');

-- Atta, Rice & Dal (category_id = 7)
INSERT INTO products (category_id, name, emoji, weight, price, old_price, badge) VALUES
(7, 'Basmati Rice', '🍚', '5 kg', 450.00, 520.00, 'sale'),
(7, 'Wheat Atta', '🌾', '5 kg', 280.00, 320.00, 'bestseller'),
(7, 'Toor Dal', '🫘', '1 kg', 160.00, 190.00, NULL),
(7, 'Moong Dal', '🫛', '1 kg', 140.00, NULL, NULL),
(7, 'Sugar', '🧂', '1 kg', 45.00, 50.00, NULL),
(7, 'Cooking Oil', '🫗', '1 litre', 180.00, 210.00, 'sale');

-- Cleaning & Household (category_id = 8)
INSERT INTO products (category_id, name, emoji, weight, price, old_price, badge) VALUES
(8, 'Dish Soap', '🧴', '750 ml', 95.00, 120.00, 'sale'),
(8, 'Floor Cleaner', '🧹', '1 litre', 130.00, NULL, NULL),
(8, 'Laundry Detergent', '🧺', '1 kg', 220.00, 260.00, 'bestseller'),
(8, 'Toilet Cleaner', '🚽', '500 ml', 85.00, NULL, NULL),
(8, 'Tissue Paper', '🧻', '6 rolls', 180.00, 220.00, 'sale'),
(8, 'Hand Wash', '🧼', '250 ml', 75.00, NULL, 'new');
