<?php
// /includes/header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config/database.php'; 
include_once __DIR__ . '/../core/auth.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skyline Hotel</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        /* ===== NAVBAR TAMBAHAN ===== */
        .nav-links {
            display: flex;
            align-items: center;
            gap: 15px; /* Jarak antar item */
        }

        /* Flex khusus untuk user + logout */
        .nav-user-logout {
            display: flex;
            align-items: center;
            gap: 5px; /* jarak antara Hi dan Logout */
        }

        .nav-user-logout b {
            font-size: 12px;
            color: #000;
            font-weight: 700;
            text-transform: uppercase;
        }

        .btn-login {
            padding: 6px 12px;
            background-color: #09478f;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-login:hover {
            background-color: #06316b;
        }
    </style>
</head>
<body>

<header class="main-header">
    <nav class="main-nav">
        
        <div style="display: flex; flex-direction: column; align-items: center;">
            <a href="<?php echo BASE_URL; ?>index.php" class="logo-img">
                <img src="<?php echo BASE_URL; ?>assets/images/logo_baru.png" alt="Skyline Hotel Logo">
            </a>
        </div>

        <ul class="nav-links">
            <li><a href="<?php echo BASE_URL; ?>index.php">Beranda</a></li>
            <li><a href="<?php echo BASE_URL; ?>rooms.php">Rooms</a></li>
            <li><a href="<?php echo BASE_URL; ?>facilities.php">Facilities</a></li>
            <li><a href="<?php echo BASE_URL; ?>contact.php">Contact</a></li>

            <?php if (is_logged_in()): ?>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li><a href="<?php echo BASE_URL; ?>admin/index.php" class="btn-login">Panel Admin</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>profile.php" class="btn-login">Profil</a></li>
                <?php endif; ?>

                <!-- USER + LOGOUT dalam satu li supaya berdekatan -->
                <li class="nav-user-logout">
                    <b><?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?></b>
                    <span>|</span>
                    <a href="<?php echo BASE_URL; ?>logout.php">Logout</a>
                </li>

            <?php else: ?>
                <li><a href="<?php echo BASE_URL; ?>login.php" class="btn-login">Login / Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>