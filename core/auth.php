<?php
// /core/auth.php
// (File ini tidak diubah, hanya memastikan session_start() ada)

// Selalu mulai session di file yang menggunakan fungsi auth
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error_message'] = "Anda harus login untuk mengakses halaman ini.";
        
        // Pastikan BASE_URL sudah didefinisikan (seharusnya sudah oleh header.php)
        if (defined('BASE_URL')) {
            header('Location: ' . BASE_URL . 'login.php'); 
        } else {
            // Fallback jika BASE_URL tidak ada
            header('Location: /PAWPROYEK/login.php'); 
        }
        exit;
    }
}

function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function require_admin() {
    if (!is_admin()) {
        $_SESSION['error_message'] = "Anda tidak memiliki hak akses admin.";
        
        if (defined('BASE_URL')) {
            $base_url = BASE_URL;
        } else {
            $base_url = '/PAWPROYEK/'; // Fallback
        }

        if (is_logged_in()) {
            header('Location: ' . $base_url . 'index.php');
        } else {
            header('Location: ' . $base_url . 'login.php');
        }
        exit;
    }
}
?>