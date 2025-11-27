<?php

include '../includes/admin_header.php'; 

?>

<div class="content-header">
    <h1>Dashboard</h1>
    <p>Selamat datang di panel admin, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</p>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php else: ?>
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Total Pelanggan</h3>
            <p><?php echo $total_users; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Kamar (Fisik)</h3>
            <p><?php echo $total_rooms; ?></p>
        </div> 
        <div class="stat-card">
            <h3>Pesanan Pending</h3>
            <p><?php echo $pending_bookings; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Pendapatan</h3>
            <p>Rp <?php echo number_format($total_revenue ?? 0, 0, ',', '.'); ?></p>
        </div>
    </div>
<?php endif; ?>
<br><br>
 
<div class="admin-card">
    <h2>Akses Cepat</h2>
    <br>
    <p>Gunakan navigasi di sebelah kiri untuk mengelola aspek-aspek situs Anda:</p>
    <ul>
        <li><b>Kelola Pesanan:</b> Konfirmasi, batalkan, atau tandai pqesanan sebagai lunas.</li>
        <li><b>Kelola Tipe Kamar:</b> Mengatur harga (weekday/weekend) dan foto tipe kamar.</li>
        <li><b>Kelola Kamar:</b> Menambah atau menghapus kamar fisik (stok kamar).</li>
        <li><b>Kelola Fasilitas:</b> Mengatur harga 'Breakfast' atau 'Room Service'.</li>
        <li><b>Kelola Pengguna:</b> Mengedit detail pelanggan atau mengubah status admin.</li>
    </ul>
</div>

<?php
include '../includes/admin_footer.php'; 
?>