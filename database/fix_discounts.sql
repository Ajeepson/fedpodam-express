-- 1. Ensure the column exists (Safe to run even if it exists)
-- Note: If this fails, it means the column already exists, which is fine.
ALTER TABLE products ADD COLUMN old_price DECIMAL(10, 2) DEFAULT NULL;

-- 2. Update "Royal Blue Hoodie" to have a discount
-- Old Price: 15,000 | New Price: 12,500
UPDATE products SET price = 12500.00, old_price = 15000.00 WHERE name LIKE '%Hoodie%';

-- 3. Update "Wireless Headphones" to have a massive discount
-- Old Price: 50,000 | New Price: 42,000
UPDATE products SET price = 42000.00, old_price = 50000.00 WHERE name LIKE '%Headphones%';

-- 4. Update "Smart Watch" to realistic price (No discount)
UPDATE products SET price = 85000.00, old_price = NULL WHERE name LIKE '%Watch%';