-- 13. FAQs Table
CREATE TABLE faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Data
INSERT INTO faqs (question, answer) VALUES 
('How long does delivery take?', 'Delivery typically takes 3-5 business days within Nigeria depending on your location.'),
('Do you offer refunds?', 'Yes, we have a 7-day return policy for eligible items that are returned in their original condition.');