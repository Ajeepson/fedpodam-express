-- 12. Site Settings Table
CREATE TABLE site_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

-- Seed Default Data
INSERT INTO site_settings (setting_key, setting_value) VALUES 
('contact_phone', '+234 800 123 4567'),
('contact_email', 'support@fedpodam.com'),
('contact_address', 'Federal Polytechnic Damaturu, Yobe State.');