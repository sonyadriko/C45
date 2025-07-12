-- Menambahkan kolom role ke tabel users
ALTER TABLE users ADD COLUMN role ENUM('admin', 'user', 'kepala_toko') DEFAULT 'user' AFTER password;

-- Update user admin yang sudah ada
UPDATE users SET role = 'admin' WHERE username = 'admin';

-- Menambahkan beberapa user untuk testing (opsional)
INSERT INTO users (name, username, password, role, alamat, created_at) VALUES
('User Test', 'user', '0192023a7bbd73250516f069df18b500', 'user', 'Jakarta', NOW()),
('Kepala Toko', 'kepala_toko', '0192023a7bbd73250516f069df18b500', 'kepala_toko', 'Bandung', NOW()),
('Manager', 'manager', '0192023a7bbd73250516f069df18b500', 'admin', 'Surabaya', NOW()); 