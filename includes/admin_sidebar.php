<?php
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="<?php echo BASE_URL; ?>index.php" target="_blank">
            <img src="<?php echo BASE_URL; ?>assets/images/logo_baru.png" alt="Logo" class="sidebar-logo">
        </a>
        <h3>Admin Panel</h3>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li><a href="<?php echo BASE_URL; ?>admin/index.php">Dashboard</a></li>
            <li><a href="<?php echo BASE_URL; ?>admin/manage_bookings.php">Kelola Pesanan</a></li>
            <li><a href="<?php echo BASE_URL; ?>admin/manage_rooms.php">Kelola Kamar</a></li>
            <li><a href="<?php echo BASE_URL; ?>admin/manage_room_types.php">Kelola Tipe Kamar</a></li>
            <li><a href="<?php echo BASE_URL; ?>admin/manage_reviews.php">Kelola Ulasan</a></li>
            
            <li><a href="<?php echo BASE_URL; ?>admin/manage_users.php">Kelola Pengguna</a></li>
            <li><a href="<?php echo BASE_URL; ?>admin/manage_fasilitas.php">Kelola Fasilitas</a></li>
            
            <li>
                <a href="<?php echo BASE_URL; ?>index.php" class="back-to-site" target="_blank">
                    Kembali ke Situs
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">Logout</a>
    </div>
</aside>