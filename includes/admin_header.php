<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../core/auth.php'; 

require_admin(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Skyline Hotel</title>
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin_style.css">
    
</head>
<body class="admin-page"> 

<div class="admin-layout">
    
    <?php 
    include __DIR__ . '/admin_sidebar.php'; 
    ?>
    
    <main class="admin-content">