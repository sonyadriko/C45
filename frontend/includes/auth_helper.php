<?php
session_start();

// Fungsi untuk mengecek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user']);
}

// Fungsi untuk mengecek role user
function getUserRole() {
    if (!isLoggedIn()) {
        return null;
    }
    return $_SESSION['user']['role'] ?? 'user';
}

// Fungsi untuk mengecek apakah user adalah admin
function isAdmin() {
    return getUserRole() === 'admin';
}

// Fungsi untuk mengecek apakah user adalah kepala toko
function isKepalaToko() {
    return getUserRole() === 'kepala_toko';
}

// Fungsi untuk mengecek apakah user adalah user biasa
function isUser() {
    return getUserRole() === 'user';
}

// Fungsi untuk redirect jika tidak login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Fungsi untuk redirect jika bukan admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: index.php?error=unauthorized");
        exit;
    }
}

// Fungsi untuk mengecek akses menu berdasarkan role
function canAccessMenu($menuName) {
    $role = getUserRole();
    
    // Menu yang hanya bisa diakses admin
    $adminOnlyMenus = [
        'kriteria',
        'penilaian', 
        'input_data',
        'proses_upload_excel',
        'proses_input_manual'
    ];
    
    // Menu yang bisa diakses semua user
    $publicMenus = [
        'dashboard',
        'pohon_keputusan',
        'pengujian'
    ];
    
    if (in_array($menuName, $adminOnlyMenus)) {
        return $role === 'admin';
    }
    
    if (in_array($menuName, $publicMenus)) {
        return true;
    }
    
    return false;
}
?> 