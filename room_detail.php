<?php
// /room_detail.php
// VERSI DENGAN FIX DOUBLE INCLUDE

// Muat logika spesifik
include_once 'core/booking_logic.php'; 
// Muat header (yang akan memuat $mysqli dan auth)
include 'includes/header.php';

$room_type_id = $_GET['id'] ?? null;
if (!$room_type_id) {
    echo "<div class='container'><p class='alert alert-error'>Tipe kamar tidak ditemukan.</p></div>";
    include 'includes/footer.php';
    exit;
}

// $mysqli sudah ada dari header.php
try {
    $sql = "SELECT * FROM Room_Types WHERE room_type_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $room_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room_type = $result->fetch_assoc(); 
    if (!$room_type) {
        throw new Exception("Data kamar tidak valid.");
    }
} catch (Exception $e) {
    echo "<div class='container'><p class='alert alert-error'>".$e->getMessage()."</p></div>";
    include 'includes/footer.php';
    exit;
}

$check_in = '';
$check_out = '';
$jumlah_kamar = 1; 
$availability_message = '';
$availability_class = '';
$is_available = false; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $jumlah_kamar = (int) $_POST['jumlah_kamar']; 
    $date_validation = validate_dates($check_in, $check_out);
    if ($date_validation !== true) {
        $availability_message = $date_validation;
        $availability_class = 'alert-error';
    } else {
        // $mysqli sudah ada
        $is_available = check_room_stock($mysqli, $room_type_id, $check_in, $check_out, $jumlah_kamar);
        if ($is_available) {
            $availability_message = "Stok kamar tersedia untuk tanggal yang Anda pilih.";
            $availability_class = 'alert-success';
        } else {
            $availability_message = "Maaf, stok kamar tidak cukup untuk tanggal tersebut.";
            $availability_class = 'alert-error';
        }
    }
}
$today = date('Y-m-d');
?>
<div class="container room-detail-container">
    <div class="room-detail-info">
        <h2><?php echo htmlspecialchars($room_type['nama_tipe']); ?></h2>
        <img src="assets/images/rooms/<?php echo htmlspecialchars($room_type['foto_utama']); ?>" alt="<?php echo htmlspecialchars($room_type['nama_tipe']); ?>">
        <div class="price detail-price">
            <span>Rp <?php echo number_format($room_type['harga_weekdays'], 0, ',', '.'); ?></span> / malam (Weekday)
            <br>
            <span style="font-size: 18px; color: #555;">
                Rp <?php echo number_format($room_type['harga_weekend'], 0, ',', '.'); ?> / malam (Weekend)
            </span>
        </div>
        <p class="deskripsi"><?php echo htmlspecialchars($room_type['deskripsi_tipe']); ?></p>
    </div>
    <div class="availability-check-form">
        <h3>Cek Ketersediaan</h3>
        <form action="room_detail.php?id=<?php echo $room_type_id; ?>" method="POST">
            <div class="form-group">
                <label for="check_in">Tanggal Check-in:</label>
                <input type="date" id="check_in" name="check_in" 
                       value="<?php echo htmlspecialchars($check_in); ?>" 
                       min="<?php echo $today; ?>" required>
            </div>
            <div class="form-group">
                <label for="check_out">Tanggal Check-out:</label>
                <input type="date" id="check_out" name="check_out" 
                       value="<?php echo htmlspecialchars($check_out); ?>" 
                       min="<?php echo date('Y-m-d', strtotime($today . ' +1 day')); ?>" required>
            </div>
            <div class="form-group">
                <label for="jumlah_kamar">Jumlah Kamar:</label>
                <input type="number" id="jumlah_kamar" name="jumlah_kamar" 
                       value="<?php echo htmlspecialchars($jumlah_kamar); ?>" 
                       min="1" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div class="form-group">
                <button type="submit" class="btn-secondary full-width">Cek Ketersediaan</button>
            </div>
        </form>
        <?php if ($availability_message): ?>
            <div class="alert <?php echo $availability_class; ?>">
                <?php echo $availability_message; ?>
            </div>
        <?php endif; ?>
        <?php if ($is_available): ?>
            <div class="booking-confirmation">
                <p>Silakan lanjutkan ke pemesanan.</p>
                <form action="booking.php" method="POST">
                    <input type="hidden" name="room_type_id" value="<?php echo $room_type_id; ?>">
                    <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                    <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                    <input type="hidden" name="jumlah_kamar" value="<?php echo $jumlah_kamar; ?>">
                    <button type="submit" class="btn-primary full-width">
                        Pesan Sekarang
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>