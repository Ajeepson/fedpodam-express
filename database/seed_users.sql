-- Seed Data: Default Customer
-- Email: customer@example.com
-- Password: password
INSERT INTO customers (full_name, email, password_hash, phone, address) VALUES 
('John Doe', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08012345678', '123 Student Hostel, Fedpodam');

-- Seed Data: Default Admin
-- Username: admin
-- Password: password
INSERT INTO admins (username, password_hash, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');