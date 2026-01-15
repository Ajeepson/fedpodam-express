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
    stock_quantity INT DEFAULT 100,
    image_url VARCHAR(255),
    category VARCHAR(100),
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
INSERT INTO products (name, description, price, image_url, category) VALUES 
('Magenta Hoodie', 'Premium cotton hoodie in vibrant magenta.', 49.99, 'https://placehold.co/300x300/d500f9/white?text=Hoodie', 'Apparel'),
('Wireless Headphones', 'Noise cancelling over-ear headphones.', 129.50, 'https://placehold.co/300x300/333/white?text=Audio', 'Electronics'),
('Smart Watch', 'Track your fitness and notifications.', 199.99, 'https://placehold.co/300x300/444/white?text=Watch', 'Electronics');

-- Seed Data: Chatbot
INSERT INTO bot_responses (keyword, response) VALUES 
('hello', 'Hi there! Welcome to Fedpodam Express. How can I help you?'),
('shipping', 'We ship worldwide! Delivery usually takes 3-5 business days.');