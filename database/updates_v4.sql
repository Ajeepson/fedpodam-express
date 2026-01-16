-- 11. Testimonials Table
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100), -- e.g. "CS Dept", "Staff"
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Data
INSERT INTO testimonials (name, role, content) VALUES 
('Amina Y.', 'CS Dept', 'Fedpodam Express made getting my textbooks and snacks so much easier. Delivery to the hostel was super fast!'),
('Emeka O.', 'Staff', 'I love the Adire collection! Great quality and it supports local student entrepreneurs.'),
('Zainab B.', 'SLT Dept', 'Reliable payment options and the customer support is actually helpful. Highly recommended.');