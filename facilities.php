<?php
// /facilities.php
// VERSI FIX: Sesuai Screenshot Nama File di Folder Assets

include 'includes/header.php';

// Ambil data fasilitas berbayar dari database
$paid_facilities = [];
try {
    $sql = "SELECT * FROM fasilitas_tambahan ORDER BY harga ASC";
    $result = $mysqli->query($sql);
    if ($result) {
        $paid_facilities = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $paid_facilities = [];
}
?>

<div class="hero check-availability-form-container" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/hotel_lobby.jpg'); height: 350px; display: flex; align-items: center; justify-content: center; text-align: center; color: white;">
    <div style="z-index: 2; max-width: 800px; padding: 20px;">
        <h1 style="font-size: 3rem; margin-bottom: 10px; font-weight: bold;">Layanan & Fasilitas Tambahan</h1>
        <p style="font-size: 1.2rem;">Tingkatkan pengalaman menginap Anda dengan layanan eksklusif kami.</p>
    </div>
</div>

<div class="container" style="margin-top: 50px; margin-bottom: 80px;">

    <div class="hotel-info" style="margin-bottom: 40px;">
        <h2>Daftar Layanan</h2>
        <p>Berikut adalah layanan tambahan yang dapat Anda pilih langsung saat melakukan pemesanan kamar.</p>
    </div>

    <div class="facilities-grid">
        
        <?php if (empty($paid_facilities)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px;">
                <p>Belum ada data fasilitas tambahan yang tersedia.</p>
            </div>
        <?php else: ?>
            <?php foreach ($paid_facilities as $fas): ?>
                <?php
                    // === LOGIKA PENCOCOKAN GAMBAR (UPDATED) ===
                    $image_source = 'assets/images/hotel_lobby.jpg'; // Default
                    
                    $name_lower = strtolower($fas['nama_fasilitas']);
                    
                    // Cek berdasarkan kata kunci nama fasilitas vs nama file di folder kamu
                    
                    if (strpos($name_lower, 'breakfast') !== false) {
                        $image_source = 'assets/images/breakfeast.jpg'; // Sesuai screenshot
                    } 
                    elseif (strpos($name_lower, 'airport') !== false || strpos($name_lower, 'jemput') !== false) {
                        $image_source = 'assets/images/airport.jpg';
                    } 
                    elseif (strpos($name_lower, 'romantic') !== false) {
                        $image_source = 'assets/images/romantic.jpg';
                    } 
                    elseif (strpos($name_lower, 'massage') !== false || strpos($name_lower, 'spa') !== false) {
                        $image_source = 'assets/images/spa.jpg';
                    }
                    elseif (strpos($name_lower, 'bed') !== false) {
                         $image_source = 'assets/images/bed.jpg'; // Sudah ada di folder
                    }
                    elseif (strpos($name_lower, 'late') !== false) {
                         $image_source = 'assets/images/late.jpg'; // Sudah ada di folder
                    }
                    elseif (strpos($name_lower, 'fruit') !== false || strpos($name_lower, 'buah') !== false) {
                         $image_source = 'assets/images/buah.jpg'; // Sudah ada di folder
                    }
                    elseif (strpos($name_lower, 'room service') !== false) {
                         $image_source = 'assets/images/rs.jpg'; // Asumsi rs.jpg adalah Room Service
                    }
                ?>
                
                <div class="facility-card">
                    <div class="facility-image-wrapper">
                        <img src="<?php echo $image_source; ?>" alt="<?php echo htmlspecialchars($fas['nama_fasilitas']); ?>">
                    </div>
                    
                    <div class="room-card-content" style="text-align: center; padding: 20px; display: flex; flex-direction: column; flex-grow: 1;">
                        <h3 style="margin-top: 5px; color: #333; font-size: 1.25rem;">
                            <?php echo htmlspecialchars($fas['nama_fasilitas']); ?>
                        </h3>
                        
                        <p class="deskripsi" style="color: #666; font-size: 14px; line-height: 1.6; margin-bottom: 20px; flex-grow: 1;">
                            <?php echo htmlspecialchars($fas['deskripsi']); ?>
                        </p>
                        
                        <div style="padding-top: 15px; border-top: 1px solid #eee; font-weight: bold; color: var(--primary-color); font-size: 18px;">
                            Rp <?php echo number_format($fas['harga'], 0, ',', '.'); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
    </div>

</div>

<style>
    .facilities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
    }
    .facility-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .facility-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .facility-image-wrapper {
        width: 100%;
        height: 200px;
        overflow: hidden;
        background-color: #eee;
    }
    .facility-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .facility-card:hover .facility-image-wrapper img {
        transform: scale(1.1);
    }
</style>

<?php include 'includes/footer.php'; ?>