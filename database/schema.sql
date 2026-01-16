-- Create Database
CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

-- 1. Customers Table (User Management)
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Admins Table (Back-office users)
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin', -- e.g., 'superadmin', 'support'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Products Table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    old_price DECIMAL(10, 2) DEFAULT NULL,
    stock_quantity INT DEFAULT 100,
    image_url VARCHAR(255),
    category VARCHAR(100),
    average_rating DECIMAL(3,2) DEFAULT 0,
    review_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- 5. Order Items (Links Products to Orders)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- 6. Shipping Table
CREATE TABLE shipping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    shipping_address TEXT NOT NULL,
    tracking_number VARCHAR(100),
    carrier VARCHAR(50), -- e.g., FedEx, DHL
    estimated_delivery DATE,
    status VARCHAR(50) DEFAULT 'Preparing',
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- 7. Chatbot Knowledge Base
CREATE TABLE bot_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(100) NOT NULL,
    response TEXT NOT NULL
);

-- Seed Data: Products
INSERT INTO products (name, description, price, old_price, image_url, category) VALUES 
('Royal Blue Hoodie', 'Premium cotton hoodie in school colors.', 12500.00, 15000.00, 'https://placehold.co/300x300/1e3a8a/white?text=Hoodie', 'Apparel'),
('Wireless Headphones', 'Noise cancelling over-ear headphones.', 42000.00, 50000.00, 'https://placehold.co/300x300/333/white?text=Audio', 'Electronics'),
('Smart Watch', 'Track your fitness and notifications.', 85000.00, NULL, 'https://placehold.co/300x300/444/white?text=Watch', 'Electronics');

-- Seed Data: Chatbot
INSERT INTO bot_responses (keyword, response) VALUES 
('hello', 'Hi there! Welcome to Fedpodam Express. How can I help you?'),
('shipping', 'We ship worldwide! Delivery usually takes 3-5 business days.');

-- 8. Homepage Banners (Dynamic Content)
CREATE TABLE banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    subtitle VARCHAR(255),
    image_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Data: Banners
INSERT INTO banners (title, subtitle, image_url) VALUES 
('Grand Opening Sale', 'Get 50% off on all electronics this week!', 'https://placehold.co/1200x300/1e3a8a/white?text=Grand+Opening');

-- 9. Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Data: Categories
INSERT INTO categories (name) VALUES ('Apparel'), ('Electronics'), ('Home'), ('Footwear');

-- 10. Reviews Table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- 11. Testimonials Table
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100), -- e.g. "CS Dept", "Staff"
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 12. Site Settings Table
CREATE TABLE site_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

-- Seed Data: Customers (Default Login: customer@example.com / password)
INSERT INTO customers (full_name, email, password_hash, phone, address) VALUES 
('John Doe', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08012345678', '123 Student Hostel, Fedpodam');

-- Seed Data: Admins (Default Login: admin / password)
INSERT INTO admins (username, password_hash, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- Seed Data: Testimonials
INSERT INTO testimonials (name, role, content) VALUES 
('Amina Y.', 'CS Dept', 'Fedpodam Express made getting my textbooks and snacks so much easier. Delivery to the hostel was super fast!'),
('Emeka O.', 'Staff', 'I love the Adire collection! Great quality and it supports local student entrepreneurs.');

-- Seed Data: Site Settings
INSERT INTO site_settings (setting_key, setting_value) VALUES 
('contact_phone', '+234 800 123 4567'),
('contact_email', 'support@fedpodam.com'),
('contact_address', 'Federal Polytechnic Damaturu, Yobe State.');

-- Seed Data: Customers
-- Email: customer@example.com | Password: password
INSERT INTO customers (full_name, email, password_hash, phone, address) VALUES 
('John Doe', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08012345678', '123 Student Hostel, Fedpodam');

-- Seed Data: Admins
-- Username: admin | Password: password
INSERT INTO admins (username, password_hash, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');