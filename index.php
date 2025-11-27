<?php
include_once 'core/booking_logic.php'; 
include 'includes/header.php'; 

$check_in = $_GET['check_in'] ?? null;
$check_out = $_GET['check_out'] ?? null;
$person = $_GET['person'] ?? null;
$is_filtered = $check_in && $check_out;
$available_room_type_ids = [];

try { 
    if ($is_filtered) {
        $date_validation = validate_dates($check_in, $check_out);
        if ($date_validation !== true) {
            $check_in = $check_out = null;
            $is_filtered = false;
            echo "<div class='container'><p class='alert alert-error'>".$date_validation."</p></div>";
        }
        if ($is_filtered) {
            $all_types_result = $mysqli->query("SELECT * FROM Room_Types");
            $all_room_types = $all_types_result->fetch_all(MYSQLI_ASSOC);
            foreach ($all_room_types as $type) {
                $is_available = check_room_stock($mysqli, $type['room_type_id'], $check_in, $check_out, 1); 
                if ($is_available) {
                    $available_room_type_ids[] = $type['room_type_id'];
                }
            }
        }
    }

    $sql = "SELECT * FROM Room_Types";
    $params = [];
    if ($is_filtered && !empty($available_room_type_ids)) {
        $placeholders = str_repeat('?,', count($available_room_type_ids) - 1) . '?';
        $sql .= " WHERE room_type_id IN ($placeholders)";
        $params = $available_room_type_ids;
    } elseif ($is_filtered && empty($available_room_type_ids)) {
        $sql .= " WHERE 1 = 0";
    }
    $sql .= " ORDER BY harga_weekdays ASC"; 
    
    $stmt = $mysqli->prepare($sql);
    if (!empty($params)) {
        $types = str_repeat('i', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $room_types = $result->fetch_all(MYSQLI_ASSOC); 

} catch (Exception $e) {
    echo "<div class='container'><p class='alert alert-error'>Error: " . $e->getMessage() . "</p></div>";
    $room_types = [];
}
$today = date('Y-m-d');
?>

<section class="hero check-availability-form-container">
    <section class="hotel-info">
        <br><br><br>
        <h2>TENTANG SKYLINE HOTEL</h2>
        <p>
        Skyline Hotel menyediakan 30 kamar yang dirancang untuk memberikan kenyamanan dan pengalaman menginap terbaik. 
        <br>Setiap kamar dilengkapi dengan fasilitas modern seperti AC, TV, Wi-Fi, serta interior yang bersih dan nyaman. 
        <br>Dengan suasana hotel yang tenang dan pelayanan ramah, Skyline Hotel menjadi pilihan tepat untuk wisatawan maupun pebisnis.
    </p>
    <br><br>
    <p>
        Pilih kamar sesuai kebutuhan Anda, dan buat pengalaman menginap Anda lebih istimewa. 
        <br>Kami juga menawarkan berbagai fasilitas pendukung seperti restoran, area lounge, dan layanan resepsionis 24 jam. 
        <br> Lakukan pemesanan kamar dengan mudah melalui website dan nikmati pengalaman menginap yang praktis serta menyenangkan.
    </p>
    <br><br><br>
    </section>
</section>

    <div class="search-box-wrapper">
        <h3>Check Booking Availability</h3>
        <form action="index.php" method="GET" class="search-form">
            <input type="date" name="check_in" placeholder="Check-in" value="<?php echo htmlspecialchars($check_in ?? ''); ?>" min="<?php echo $today; ?>" required>
            <input type="date" name="check_out" placeholder="Check-out" value="<?php echo htmlspecialchars($check_out ?? ''); ?>" min="<?php echo date('Y-m-d', strtotime($today . ' +1 day')); ?>" required>
            <input type="number" name="person" placeholder="Person" min="1" value="<?php echo htmlspecialchars($person ?? 1); ?>">
            <button type="submit" class="btn-primary">Submit</button>
        </form>

        <?php if ($is_filtered && empty($available_room_type_ids)): ?>
            <p class="availability-status alert-error">Maaf, tidak ada kamar yang tersedia untuk tanggal tersebut.</p>
        <?php endif; ?>

        <?php if ($is_filtered && !empty($available_room_type_ids)): ?>
            <p class="availability-status alert-success"><?php echo count($available_room_type_ids); ?> Tipe Kamar Tersedia.</p>
        <?php endif; ?>

    </div>
</section>

<div class="container">
    <hr>

    <h2>
        <?php echo $is_filtered ? 'Hasil Pencarian Kamar' : 'Pilihan Tipe Kamar Kami'; ?>
    </h2>

    <section class="room-list">
        <?php if (empty($room_types)): ?>
            <p>Maaf, saat ini belum ada tipe kamar yang tersedia.</p>
        <?php else: ?>
            <?php foreach ($room_types as $type): ?>
                <div class="room-card">
                    <img src="assets/images/rooms/<?php echo htmlspecialchars($type['foto_utama']); ?>" alt="<?php echo htmlspecialchars($type['nama_tipe']); ?>">
                    <div class="room-card-content">
                        <h3><?php echo htmlspecialchars($type['nama_tipe']); ?></h3>
                        <p class="deskripsi"><?php echo htmlspecialchars($type['deskripsi_tipe']); ?></p>
                        <div class="price">
                            Mulai dari <span>Rp <?php echo number_format($type['harga_weekdays'], 0, ',', '.'); ?></span> / malam
                        </div>
                        <a href="room_detail.php?id=<?php echo $type['room_type_id']; ?><?php echo $is_filtered ? '&check_in=' . urlencode($check_in) . '&check_out=' . urlencode($check_out) : ''; ?>" class="btn-primary">
                            Lihat Detail & Pesan
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<?php include 'includes/footer.php'; ?>