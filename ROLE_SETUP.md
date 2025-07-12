# Role-Based Access Control Setup

## Overview
Sistem ini telah diimplementasikan dengan role-based access control (RBAC) yang membedakan akses antara admin, kepala toko, dan user biasa.

## Setup Database

### 1. Jalankan SQL untuk menambah kolom role
```sql
-- Menambahkan kolom role ke tabel users
ALTER TABLE users ADD COLUMN role ENUM('admin', 'user', 'kepala_toko') DEFAULT 'user' AFTER password;

-- Update user admin yang sudah ada
UPDATE users SET role = 'admin' WHERE username = 'admin';

-- Menambahkan beberapa user untuk testing (opsional)
INSERT INTO users (name, username, password, role, alamat, created_at) VALUES
('User Test', 'user', '0192023a7bbd73250516f069df18b500', 'user', 'Jakarta', NOW()),
('Kepala Toko', 'kepala_toko', '0192023a7bbd73250516f069df18b500', 'kepala_toko', 'Bandung', NOW()),
('Manager', 'manager', '0192023a7bbd73250516f069df18b500', 'admin', 'Surabaya', NOW());
```

### 2. Password untuk testing
- Username: `admin`, Password: `admin123` (Role: Admin)
- Username: `user`, Password: `admin123` (Role: User)
- Username: `kepala_toko`, Password: `admin123` (Role: Kepala Toko)
- Username: `manager`, Password: `admin123` (Role: Admin)

## Role Permissions

### Admin Role
**Menu yang dapat diakses:**
- Dashboard
- Kriteria (CRUD)
- Penilaian (View data)
- Input Data (Upload Excel/Manual)
- Pohon Keputusan
- Pengujian

**Fitur khusus:**
- Dapat mengelola kriteria dan nilai kriteria
- Dapat upload data responden
- Dapat melihat semua data penilaian
- Dapat menjalankan analisis C4.5

### Kepala Toko Role
**Menu yang dapat diakses:**
- Dashboard
- Pohon Keputusan (View only)
- Pengujian (View only)

**Fitur khusus:**
- Hanya dapat melihat hasil analisis
- Tidak dapat mengubah data
- Tidak dapat upload data

### User Role
**Menu yang dapat diakses:**
- Dashboard
- Pohon Keputusan (View only)
- Pengujian (View only)

**Fitur khusus:**
- Hanya dapat melihat hasil analisis
- Tidak dapat mengubah data
- Tidak dapat upload data

## File Structure

### Auth Helper (`frontend/includes/auth_helper.php`)
Berisi fungsi-fungsi untuk:
- `isLoggedIn()` - Cek status login
- `getUserRole()` - Ambil role user
- `isAdmin()` - Cek apakah admin
- `isKepalaToko()` - Cek apakah kepala toko
- `isUser()` - Cek apakah user biasa
- `requireLogin()` - Redirect jika tidak login
- `requireAdmin()` - Redirect jika bukan admin
- `canAccessMenu()` - Cek akses menu

### Sidebar (`frontend/view/partials/sidebar.php`)
Menu sidebar yang menyesuaikan dengan role user:
- Menu admin hanya muncul untuk admin
- Menu user muncul untuk semua role

### Protected Pages
Halaman yang dilindungi dengan role check:
- `kriteria.php` - `requireAdmin()`
- `penilaian.php` - `requireAdmin()`
- `input_data.php` - `requireAdmin()`

## Cara Menambah Role Baru

### 1. Tambah role di database
```sql
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'kepala_toko', 'manager') DEFAULT 'user';
```

### 2. Update auth_helper.php
```php
// Tambah fungsi baru
function isManager() {
    return getUserRole() === 'manager';
}

// Update canAccessMenu()
function canAccessMenu($menuName) {
    $role = getUserRole();
    
    // Menu yang hanya bisa diakses manager
    $managerOnlyMenus = [
        'laporan',
        'export_data'
    ];
    
    if (in_array($menuName, $managerOnlyMenus)) {
        return $role === 'manager';
    }
    
    // ... existing code
}
```

### 3. Update sidebar
```php
<?php if (isManager()): ?>
<li class="sidebar-item">
  <a class="sidebar-link" href="./laporan.php">
    <iconify-icon icon="solar:document-bold" width="24" height="24"></iconify-icon>
    <span class="hide-menu">Laporan</span>
  </a>
</li>
<?php endif; ?>
```

## Security Features

### 1. Session Management
- Session dimulai di setiap halaman
- Role disimpan dalam session
- Auto-logout jika session expired

### 2. Access Control
- Redirect otomatis jika tidak login
- Redirect ke dashboard jika akses ditolak
- Pesan error yang informatif

### 3. Menu Protection
- Menu admin tidak muncul di sidebar untuk user biasa
- Direct URL access diblokir dengan `requireAdmin()`

## Testing

### Test Case 1: Admin Login
1. Login dengan username: `admin`, password: `admin123`
2. Verifikasi semua menu admin muncul
3. Akses halaman kriteria, penilaian, input data
4. Verifikasi tidak ada error

### Test Case 2: Kepala Toko Login
1. Login dengan username: `kepala_toko`, password: `admin123`
2. Verifikasi hanya menu user yang muncul
3. Coba akses URL admin secara langsung
4. Verifikasi redirect ke dashboard dengan pesan error

### Test Case 3: User Login
1. Login dengan username: `user`, password: `admin123`
2. Verifikasi hanya menu user yang muncul
3. Coba akses URL admin secara langsung
4. Verifikasi redirect ke dashboard dengan pesan error

### Test Case 4: Unauthorized Access
1. Coba akses halaman admin tanpa login
2. Verifikasi redirect ke login page
3. Login sebagai user, coba akses halaman admin
4. Verifikasi redirect ke dashboard dengan pesan error

## Troubleshooting

### Problem: Menu tidak muncul sesuai role
**Solution:** Pastikan session user memiliki data role yang benar

### Problem: Redirect loop
**Solution:** Cek path include di auth_helper.php

### Problem: Role tidak tersimpan di session
**Solution:** Pastikan kolom role sudah ada di database dan query login mengambil role

### Problem: Include path error
**Solution:** Pastikan path relatif sudah benar:
- Dari `frontend/view/partials/sidebar.php` ke `frontend/includes/auth_helper.php`: `../../includes/auth_helper.php`
- Dari `frontend/view/kriteria.php` ke `frontend/includes/auth_helper.php`: `../includes/auth_helper.php`
- Dari `frontend/view/index.php` ke `frontend/includes/auth_helper.php`: `../includes/auth_helper.php` 