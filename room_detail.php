<?php


// 1. Muat logika utama
include_once 'core/booking_logic.php'; 
include 'includes/header.php';

// 2. Validasi ID Tipe Kamar
$room_type_id = $_GET['id'] ?? null;
if (!$room_type_id) {
    echo "<div class='container'><p class='alert alert-error'>Tipe kamar tidak ditemukan.</p></div>";
    include 'includes/footer.php';
    exit;
}

// 3. Ambil Data Kamar dari Database
try {
    $sql = "SELECT * FROM Room_Types WHERE room_type_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $room_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room_type = $result->fetch_assoc(); 
    if (!$room_type) throw new Exception("Data kamar tidak valid.");
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
$available_rooms_list = []; // <--- Didefinisikan array kosong dulu

// 4. Proses Form Cek Ketersediaan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $jumlah_kamar = (int) $_POST['jumlah_kamar']; 
    
    $date_validation = validate_dates($check_in, $check_out);
    
    if ($date_validation !== true) {
        $availability_message = $date_validation;
        $availability_class = 'alert-error';
    } else {
        // Panggil fungsi pencari kamar dari booking_logic.php
        // Pastikan booking_logic.php sudah pakai fungsi 'get_available_specific_rooms' yang baru
        $available_rooms_list = get_available_specific_rooms($mysqli, $room_type_id, $check_in, $check_out);

        // Cek stok vs kebutuhan
        if (count($available_rooms_list) >= $jumlah_kamar) {
            $is_available = true;
            $availability_message = "Tersedia! Silakan pilih <strong>$jumlah_kamar</strong> nomor kamar di bawah ini.";
            $availability_class = 'alert-success';
        } else {
            $is_available = false;
            $availability_message = "Maaf, hanya tersisa " . count($available_rooms_list) . " kamar.";
            $availability_class = 'alert-error';
        }
    }
}
$today = date('Y-m-d');
?>

<style>
    .room-detail-container { display: flex; flex-wrap: wrap; gap: 30px; margin-top: 30px; }
    .room-detail-info { flex: 1; min-width: 300px; }
    .room-detail-info img { width: 100%; border-radius: 8px; }
    .availability-check-form { flex: 1; min-width: 300px; background: #f9f9f9; padding: 25px; border-radius: 8px; border: 1px solid #ddd; height: fit-content; }
    
    /* CSS KOTAK KAMAR */
    .room-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; margin-bottom: 20px; }
    .room-box {
        width: 50px; height: 50px;
        background: #fff; border: 2px solid #ccc; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-weight: bold; user-select: none;
    }
    .room-box:hover { border-color: #007bff; background: #e9f5ff; }
    .room-box.selected {
        background: #007bff; color: white; border-color: #0056b3;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1); transform: scale(1.05);
    }
    .selection-info { margin-bottom: 10px; font-size: 0.9em; color: #555; }
</style>

<div class="container room-detail-container">
    
    <div class="room-detail-info">
        <h2><?php echo htmlspecialchars($room_type['nama_tipe']); ?></h2>
        <img src="assets/images/rooms/<?php echo htmlspecialchars($room_type['foto_utama']); ?>" alt="<?php echo htmlspecialchars($room_type['nama_tipe']); ?>">
        <div class="price detail-price" style="margin-top:15px;">
            <span style="font-size:1.5rem; font-weight:bold;">Rp <?php echo number_format($room_type['harga_weekdays'], 0, ',', '.'); ?></span> / malam (Weekday)<br>
            <span style="color:#666;">Rp <?php echo number_format($room_type['harga_weekend'], 0, ',', '.'); ?> / malam (Weekend)</span>
        </div>
        <p style="margin-top:15px;"><?php echo nl2br(htmlspecialchars($room_type['deskripsi_tipe'])); ?></p>
    </div>

    <div class="availability-check-form">
        <h3>Cek Ketersediaan</h3>
        
        <form action="room_detail.php?id=<?php echo $room_type_id; ?>" method="POST">
            <div class="form-group">
                <label>Tanggal Check-in:</label>
                <input type="date" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>" min="<?php echo $today; ?>" required style="width:100%; padding:8px;">
            </div>
            <div class="form-group" style="margin-top:10px;">
                <label>Tanggal Check-out:</label>
                <input type="date" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>" min="<?php echo date('Y-m-d', strtotime($today . ' +1 day')); ?>" required style="width:100%; padding:8px;">
            </div>
            <div class="form-group" style="margin-top:10px;">
                <label>Jumlah Kamar:</label>
                <input type="number" name="jumlah_kamar" value="<?php echo htmlspecialchars($jumlah_kamar); ?>" min="1" max="10" required style="width:100%; padding:8px;">
            </div>
            <button type="submit" class="btn-secondary full-width" style="margin-top:20px; width:100%; padding:10px;">Cek Ketersediaan</button>
        </form>

        <?php if ($availability_message): ?>
            <div class="alert <?php echo $availability_class; ?>" style="margin-top:15px; padding:10px; border-radius:4px; background: <?php echo ($availability_class == 'alert-success') ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo ($availability_class == 'alert-success') ? '#155724' : '#721c24'; ?>;">
                <?php echo $availability_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($is_available && !empty($available_rooms_list)): ?>
            <div class="room-selection-area" style="margin-top: 25px; border-top: 1px dashed #ccc; padding-top: 15px;">
                <h4>Pilih Nomor Kamar</h4>
                <p class="selection-info">Klik <strong><span id="sisa-target"><?php echo $jumlah_kamar; ?></span></strong> kotak lagi.</p>
                
                <div class="room-grid">
                    <?php foreach ($available_rooms_list as $room): ?>
                        <div class="room-box" 
                             data-id="<?php echo $room['room_id']; ?>" 
                             onclick="toggleSelection(this)">
                            <?php echo htmlspecialchars($room['nomor_kamar']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form action="booking.php" method="POST">
                    <input type="hidden" name="room_type_id" value="<?php echo $room_type_id; ?>">
                    <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                    <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                    <input type="hidden" name="jumlah_kamar" value="<?php echo $jumlah_kamar; ?>">
                    
                    <input type="hidden" name="selected_room_ids" id="selected_room_ids_input">

                    <button type="submit" id="btnPesan" class="btn-primary full-width" disabled style="width:100%; padding:10px; margin-top:10px; opacity:0.5; cursor:not-allowed;">
                        Lanjut Pemesanan
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    const targetCount = <?php echo (int)$jumlah_kamar; ?>;
    let selectedIds = [];

    function toggleSelection(el) {
        const id = el.getAttribute('data-id');

        // Logic Toggle
        if (el.classList.contains('selected')) {
            // Unselect
            el.classList.remove('selected');
            selectedIds = selectedIds.filter(item => item !== id);
        } else {
            // Select (Cek kuota dulu)
            if (selectedIds.length >= targetCount) {
                alert("Anda memesan " + targetCount + " kamar. Batalkan pilihan lain dulu jika ingin mengganti nomor.");
                return;
            }
            el.classList.add('selected');
            selectedIds.push(id);
        }

        updateUI();
    }

    function updateUI() {
        // Update Text Sisa
        const sisa = targetCount - selectedIds.length;
        document.getElementById('sisa-target').innerText = sisa;

        // Update Hidden Input
        document.getElementById('selected_room_ids_input').value = selectedIds.join(',');

        // Enable/Disable Button
        const btn = document.getElementById('btnPesan');
        if (selectedIds.length === targetCount) {
            btn.disabled = false;
            btn.style.opacity = "1";
            btn.style.cursor = "pointer";
            btn.innerText = "Pesan Sekarang";
        } else {
            btn.disabled = true;
            btn.style.opacity = "0.5";
            btn.style.cursor = "not-allowed";
            btn.innerText = "Pilih " + sisa + " Lagi";
        }
    }
</script>

<?php include 'includes/footer.php'; ?>