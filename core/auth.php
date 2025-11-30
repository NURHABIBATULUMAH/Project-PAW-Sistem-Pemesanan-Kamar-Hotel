<?php
// File: /htdocs/core/auth.php

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi Cek Status Login
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']);
    }
}

// Fungsi Paksa Login (untuk halaman profil/booking)
if (!function_exists('require_login')) {
    function require_login() {
        if (!is_logged_in()) {
            $_SESSION['error_message'] = "Silakan login terlebih dahulu.";
            
            // Redirect aman menggunakan BASE_URL
            $redirect = defined('BASE_URL') ? BASE_URL . 'login.php' : '../login.php';
            header('Location: ' . $redirect); 
            exit;
        }
    }
}

// Fungsi Cek Status Admin
if (!function_exists('is_admin')) {
    function is_admin() {
        return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}

// Fungsi Paksa Admin (untuk halaman admin)
if (!function_exists('require_admin')) {
    function require_admin() {
        if (!is_admin()) {
            $_SESSION['error_message'] = "Akses ditolak. Khusus Admin.";
            
            $redirect = defined('BASE_URL') ? BASE_URL . 'index.php' : '../index.php';
            header('Location: ' . $redirect);
            exit;
        }
    }
}
?>