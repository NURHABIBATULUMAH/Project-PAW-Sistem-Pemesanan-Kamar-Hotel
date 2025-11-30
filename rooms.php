<?php
// /rooms.php
// VERSI UPGRADE: TAMPILAN LEBIH MEWAH & MODERN

include 'includes/header.php'; // Memuat $mysqli dan auth

// 2. Logika untuk mengambil data Tipe Kamar
try {
    $stmt = $mysqli->query("SELECT * FROM room_types ORDER BY harga_weekdays ASC");
    $room_types = $stmt->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $room_types = []; 
}
?>

<style>
    /* Hero Banner */
    .rooms-hero {
        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/images/hotel_lobby.jpg');
        background-size: cover;
        background-position: center;
        height: 350px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        margin-bottom: 50px;
    }
    .rooms-hero h1 { font-size: 3rem; margin-bottom: 10px; text-shadow: 2px 2px 5px rgba(0,0,0,0.7); }
    .rooms-hero p { font-size: 1.2rem; max-width: 600px; margin: 0 auto; }

    /* Layout Grid */
    .room-list-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); /* Responsif */
        gap: 30px;
        padding-bottom: 50px;
    }

    /* Card Design Modern */
    .modern-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08); /* Bayangan lembut */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        border: 1px solid #f0f0f0;
    }

    .modern-card:hover {
        transform: translateY(-8px); /* Efek naik saat hover */
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }

    /* Gambar Kamar */
    .card-img-wrapper {
        position: relative;
        height: 220px;
        overflow: hidden;
    }
    .card-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .modern-card:hover .card-img-wrapper img {
        transform: scale(1.1); /* Efek zoom gambar */
    }
    
    /* Badge Harga di atas gambar */
    .price-badge {
        position: absolute;
        bottom: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.95);
        padding: 8px 15px;
        border-radius: 30px;
        font-weight: bold;
        color: var(--primary-color); /* Emas/Kuning */
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        font-size: 0.95rem;
    }

    /* Konten Card */
    .card-body {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .card-body h3 {
        margin: 0 0 10px 0;
        font-size: 1.4rem;
        color: #333;
    }

    .card-desc {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box;
        overflow: hidden;
    }

    /* Ikon Fasilitas Mini */
    .mini-facilities {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        color: #555;
        font-size: 0.9rem;
        padding-top: 15px;
        border-top: 1px dashed #eee;
    }
    .mini-item { display: flex; align-items: center; gap: 5px; }

    /* Tombol Full Width */
    .btn-book {
        margin-top: auto;
        background: var(--dark-color); /* Hitam/Gelap */
        color: var(--primary-color);   /* Emas */
        text-align: center;
        padding: 12px;
        border-radius: 6px;
        font-weight: bold;
        transition: background 0.3s;
        border: none;
    }
    .btn-book:hover {
        background: #000;
        color: #fff;
    }
</style>

<div class="rooms-hero">
    <div>
        <h1>Pilihan Kamar Eksklusif</h1>
        <p>Temukan kenyamanan dan kemewahan yang dirancang khusus untuk istirahat terbaik Anda.</p>
    </div>
</div>

<div class="container">
    
    <div style="text-align: center; margin-bottom: 40px;">
        <h2 style="color: #333; font-size: 2rem; margin-bottom: 10px;">Akomodasi Kami</h2>
        <div style="width: 60px; height: 4px; background: var(--primary-color); margin: 0 auto;"></div>
    </div>
    
    <section class="room-list-container">
        <?php if (empty($room_types)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                <p>Maaf, saat ini belum ada tipe kamar yang tersedia.</p>
            </div>
        
        <?php else: ?>
            <?php foreach ($room_types as $type): ?>
                
                <div class="modern-card">
                    <div class="card-img-wrapper">
                        <img src="assets/images/rooms/<?php echo htmlspecialchars($type['foto_utama']); ?>" 
                             alt="<?php echo htmlspecialchars($type['nama_tipe']); ?>">
                        
                        <div class="price-badge">
                            Rp <?php echo number_format($type['harga_weekdays'], 0, ',', '.'); ?> <span style="font-size: 0.8em; color: #555; font-weight: normal;">/ malam</span>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($type['nama_tipe']); ?></h3>
                        
                        <p class="card-desc"><?php echo htmlspecialchars($type['deskripsi_tipe']); ?></p>
                        
                        <div class="mini-facilities">
                            <div class="mini-item">📶 WiFi</div>
                            <div class="mini-item">❄️ AC</div>
                            <div class="mini-item">🚿 Shower</div>
                        </div>
                        
                        <a href="room_detail.php?id=<?php echo $type['room_type_id']; ?>" class="btn-book">
                            Lihat Detail & Pesan ➜
                        </a>
                    </div>
                </div>
                
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

</div>

<?php
include 'includes/footer.php'; 
?>