# Sistem C4.5 Decision Tree - Analisis Kepuasan Pelanggan

Sistem berbasis web untuk analisis kepuasan pelanggan menggunakan algoritma C4.5 Decision Tree dengan role-based access control.

## 📋 Daftar Isi

- [Fitur](#-fitur)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi Database](#-konfigurasi-database)
- [Setup Role-Based Access Control](#-setup-role-based-access-control)
- [Menjalankan Aplikasi](#-menjalankan-aplikasi)
- [Struktur Project](#-struktur-project)
- [API Endpoints](#-api-endpoints)
- [Troubleshooting](#-troubleshooting)

## ✨ Fitur

### 🔐 Role-Based Access Control
- **Admin**: Akses penuh ke semua fitur
- **Kepala Toko**: Hanya dapat melihat hasil analisis
- **User**: Hanya dapat melihat hasil analisis

### 📊 Fitur Utama
- Dashboard dengan statistik
- Manajemen kriteria dan nilai kriteria
- Input data responden (Excel upload & manual)
- Visualisasi pohon keputusan
- Pengujian algoritma C4.5 dengan metrik evaluasi
- Confusion matrix dan skor evaluasi

### 🎯 Algoritma C4.5
- Implementasi algoritma C4.5 untuk klasifikasi
- Perhitungan Information Gain
- Visualisasi pohon keputusan
- Evaluasi performa dengan accuracy, precision, recall, F1-score

## 🛠 Teknologi yang Digunakan

### Backend
- **Python 3.8+** dengan Flask
- **scikit-learn** untuk implementasi C4.5
- **MySQL** untuk database
- **Graphviz** untuk visualisasi pohon

### Frontend
- **PHP 7.4+** dengan session management
- **Bootstrap 5** untuk UI
- **jQuery** untuk interaksi
- **D3.js** untuk visualisasi pohon
- **DataTables** untuk tabel interaktif

### Database
- **MySQL 5.7+** atau **MariaDB 10.3+**

## 💻 Persyaratan Sistem

### Minimum Requirements
- **OS**: Windows 10/11, macOS, atau Linux
- **RAM**: 4GB
- **Storage**: 2GB free space
- **PHP**: 7.4 atau lebih baru
- **Python**: 3.8 atau lebih baru
- **MySQL**: 5.7 atau lebih baru

### Recommended
- **RAM**: 8GB
- **Storage**: 5GB free space
- **PHP**: 8.0+
- **Python**: 3.9+
- **MySQL**: 8.0+

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd c45
```

### 2. Setup Backend (Python/Flask)

#### Install Python Dependencies
```bash
cd backend
python -m venv venv

# Windows
venv\Scripts\activate

# macOS/Linux
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt
```

#### Verifikasi Backend
```bash
python app.py
```
Backend akan berjalan di `http://localhost:5000`

### 3. Setup Frontend (PHP)

#### Install XAMPP/WAMP
1. Download dan install [XAMPP](https://www.apachefriends.org/) atau [WAMP](https://www.wampserver.com/)
2. Start Apache dan MySQL services
3. Copy folder `frontend` ke `htdocs` (XAMPP) atau `www` (WAMP)

#### Install Composer Dependencies (Optional)
```bash
cd frontend
composer install
```

### 4. Setup Database

#### Import Database
1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Buat database baru dengan nama `c45`
3. Import file `c45.sql`

#### Atau gunakan command line:
```bash
mysql -u root -p
CREATE DATABASE c45;
USE c45;
SOURCE c45.sql;
```

## ⚙️ Konfigurasi Database

### 1. Update Database Configuration
Edit file `frontend/database/config.php`:
```php
<?php
$databaseConnection = mysqli_connect("localhost", "root", "", "c45");
if (!$databaseConnection) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

### 2. Update Backend Database Config
Edit file `backend/app.py`:
```python
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",  # Sesuaikan dengan password MySQL Anda
    database="c45"
)
```

## 🔐 Setup Role-Based Access Control

### 1. Jalankan SQL untuk Menambah Role
```sql
-- Menambahkan kolom role ke tabel users
ALTER TABLE users ADD COLUMN role ENUM('admin', 'user', 'kepala_toko') DEFAULT 'user' AFTER password;

-- Update user admin yang sudah ada
UPDATE users SET role = 'admin' WHERE username = 'admin';

-- Menambahkan user testing
INSERT INTO users (name, username, password, role, alamat, created_at) VALUES
('User Test', 'user', '0192023a7bbd73250516f069df18b500', 'user', 'Jakarta', NOW()),
('Kepala Toko', 'kepala_toko', '0192023a7bbd73250516f069df18b500', 'kepala_toko', 'Bandung', NOW()),
('Manager', 'manager', '0192023a7bbd73250516f069df18b500', 'admin', 'Surabaya', NOW());
```

### 2. Credentials untuk Testing
- **Admin**: `admin` / `admin123`
- **User**: `user` / `admin123`
- **Kepala Toko**: `kepala_toko` / `admin123`
- **Manager**: `manager` / `admin123`

## 🏃‍♂️ Menjalankan Aplikasi

### 1. Start Backend Server
```bash
cd backend
# Aktifkan virtual environment
venv\Scripts\activate  # Windows
source venv/bin/activate  # macOS/Linux

# Jalankan Flask app
python app.py
```

### 2. Start Frontend Server
- Pastikan Apache dan MySQL berjalan di XAMPP/WAMP
- Akses aplikasi di: `http://localhost/c45/frontend/view/`

### 3. Login dan Test
1. Buka browser ke `http://localhost/c45/frontend/view/login.php`
2. Login dengan credentials yang telah disediakan
3. Test fitur sesuai role masing-masing

## 📁 Struktur Project

```
c45/
├── backend/
│   ├── app.py                 # Flask API server
│   ├── requirements.txt       # Python dependencies
│   ├── tree_decision_c45.json # Output pohon keputusan
│   └── tree_decision_c45.png  # Visualisasi pohon
├── frontend/
│   ├── assets/               # CSS, JS, Images
│   ├── database/
│   │   └── config.php        # Database configuration
│   ├── includes/
│   │   └── auth_helper.php   # Authentication helper
│   └── view/
│       ├── partials/         # Header, sidebar, footer
│       ├── index.php         # Dashboard
│       ├── login.php         # Login page
│       ├── kriteria.php      # Kriteria management
│       ├── penilaian.php     # Data penilaian
│       ├── input_data.php    # Input data
│       ├── pohon_keputusan.php # Visualisasi pohon
│       └── pengujian.php     # Testing C4.5
├── c45.sql                   # Database schema
├── ROLE_SETUP.md            # Role setup documentation
└── README.md                # This file
```

## 🔌 API Endpoints

### Backend Flask API (`http://localhost:5000`)

#### 1. Generate Decision Tree
```
GET /c45/run
```
Menghasilkan pohon keputusan dan menyimpan ke file JSON/PNG

#### 2. Get Accuracy Metrics
```
GET /c45/akurasi
```
Mengembalikan metrik evaluasi (accuracy, precision, recall, F1-score, confusion matrix)

#### 3. Predict New Data
```
POST /c45/predict
Content-Type: application/json

{
  "data": {
    "kriteria1": "nilai1",
    "kriteria2": "nilai2",
    ...
  }
}
```

## 🐛 Troubleshooting

### Common Issues

#### 1. Database Connection Error
**Error**: `Connection failed: Access denied`
**Solution**: 
- Periksa username/password di `config.php`
- Pastikan MySQL service berjalan
- Periksa nama database

#### 2. Python Module Not Found
**Error**: `ModuleNotFoundError: No module named 'flask'`
**Solution**:
```bash
cd backend
pip install -r requirements.txt
```

#### 3. CORS Error
**Error**: `Access to fetch at 'http://localhost:5000' from origin 'http://localhost' has been blocked by CORS policy`
**Solution**: 
- Pastikan backend berjalan di port 5000
- CORS sudah dikonfigurasi di `backend/app.py`

#### 4. Session Error
**Error**: `Warning: session_start(): Failed to start session`
**Solution**:
- Periksa permission folder
- Pastikan PHP session extension aktif

#### 5. File Upload Error
**Error**: `Failed to upload file`
**Solution**:
- Periksa `upload_max_filesize` di `php.ini`
- Pastikan folder upload memiliki permission write

### Debug Mode

#### Enable PHP Error Reporting
Edit `php.ini`:
```ini
display_errors = On
error_reporting = E_ALL
```

#### Enable Flask Debug Mode
Edit `backend/app.py`:
```python
if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
```

## 📝 Logs

### PHP Logs
- **XAMPP**: `xampp/apache/logs/error.log`
- **WAMP**: `wamp/logs/apache_error.log`

### Python Logs
- Flask logs akan muncul di console saat menjalankan `python app.py`

## 🔒 Security Considerations

### 1. Production Deployment
- Ganti password default
- Gunakan HTTPS
- Set `display_errors = Off` di production
- Gunakan environment variables untuk sensitive data

### 2. Database Security
- Buat user database khusus (jangan gunakan root)
- Batasi akses database sesuai kebutuhan
- Backup database secara berkala

### 3. File Permissions
- Set permission yang tepat untuk folder upload
- Batasi akses ke file konfigurasi

## 🤝 Contributing

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 License

Project ini dilisensikan di bawah MIT License - lihat file [LICENSE](LICENSE) untuk detail.

## 📞 Support

Jika mengalami masalah atau memiliki pertanyaan:

1. Periksa section [Troubleshooting](#-troubleshooting)
2. Buka issue di repository
3. Hubungi tim development

---

**Note**: Pastikan semua service (Apache, MySQL, Python Flask) berjalan sebelum mengakses aplikasi. 