-- Fix missing tables

-- 1. Testimonials Table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100), -- e.g. "CS Dept", "Staff"
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Data for Testimonials
INSERT INTO testimonials (name, role, content) VALUES 
('Amina Y.', 'CS Dept', 'Fedpodam Express made getting my textbooks and snacks so much easier. Delivery to the hostel was super fast!'),
('Emeka O.', 'Staff', 'I love the Adire collection! Great quality and it supports local student entrepreneurs.');

-- 2. Site Settings Table (Just in case it is missing too)
CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES 
('contact_phone', '+234 800 123 4567'),
('contact_email', 'support@fedpodam.com'),
('contact_address', 'Federal Polytechnic Damaturu, Yobe State.');