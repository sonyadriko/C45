# 🚀 Quick Installation Guide

Panduan instalasi cepat untuk menjalankan sistem C4.5 Decision Tree.

## ⚡ Quick Start (5 Menit)

### Prerequisites
- XAMPP/WAMP sudah terinstall
- Python 3.8+ sudah terinstall
- Git sudah terinstall

### Step 1: Clone & Setup
```bash
# Clone repository
git clone <repository-url>
cd c45

# Copy frontend ke htdocs
cp -r frontend /path/to/xampp/htdocs/c45/
```

### Step 2: Setup Database
1. Start XAMPP (Apache + MySQL)
2. Buka `http://localhost/phpmyadmin`
3. Buat database `c45`
4. Import file `c45.sql`

### Step 3: Setup Backend
```bash
cd backend
python -m venv venv
venv\Scripts\activate  # Windows
source venv/bin/activate  # macOS/Linux
pip install -r requirements.txt
python app.py
```

### Step 4: Setup Role
Jalankan SQL ini di phpMyAdmin:
```sql
ALTER TABLE users ADD COLUMN role ENUM('admin', 'user', 'kepala_toko') DEFAULT 'user' AFTER password;
UPDATE users SET role = 'admin' WHERE username = 'admin';
```

### Step 5: Test
1. Buka `http://localhost/c45/frontend/view/login.php`
2. Login: `admin` / `admin123`
3. Test fitur!

## 🔧 Troubleshooting Quick Fix

### Backend tidak bisa diakses?
```bash
# Cek apakah Flask berjalan
curl http://localhost:5000/c45/akurasi
```

### Database connection error?
```php
// Edit frontend/database/config.php
$databaseConnection = mysqli_connect("localhost", "root", "", "c45");
```

### CORS error?
```python
# Pastikan di backend/app.py ada:
from flask_cors import CORS
CORS(app)
```

## 📋 Checklist Instalasi

- [ ] XAMPP/WAMP terinstall dan running
- [ ] Database `c45` dibuat dan diimport
- [ ] Backend Python berjalan di port 5000
- [ ] Frontend bisa diakses di `localhost/c45/frontend/view/`
- [ ] Role database sudah disetup
- [ ] Bisa login dengan admin/admin123

## 🎯 Next Steps

Setelah instalasi berhasil:
1. Baca [README.md](README.md) untuk dokumentasi lengkap
2. Test semua role (admin, user, kepala_toko)
3. Upload data Excel untuk testing
4. Jalankan analisis C4.5

## 🆘 Need Help?

Jika ada masalah:
1. Cek [Troubleshooting di README.md](README.md#-troubleshooting)
2. Pastikan semua service berjalan
3. Periksa error log di XAMPP 