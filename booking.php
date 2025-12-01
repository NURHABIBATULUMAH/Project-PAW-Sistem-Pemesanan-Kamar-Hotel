<?php

include 'config/database.php';
include 'core/auth.php'; 
include 'includes/header.php';

require_login();

// 1. CEK KELENGKAPAN DATA DIRI
$user_id_cek = $_SESSION['user_id'];
$sql_cek_profile = "SELECT nik, phone, address FROM users WHERE user_id = ?";
$stmt_cek = $mysqli->prepare($sql_cek_profile);
$stmt_cek->bind_param("i", $user_id_cek);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();
$data_user = $result_cek->fetch_assoc();

if (empty($data_user['nik']) || empty($data_user['phone']) || empty($data_user['address'])) {
    $_SESSION['error_message'] = "Mohon lengkapi NIK, Nomor HP, dan Alamat Anda sebelum melakukan pemesanan.";
    header("Location: profile.php");
    exit;
}

// 2. TANGKAP DATA DARI FORM SEBELUMNYA (ROOM DETAIL)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_type_id = $_POST['room_type_id'] ?? null;
    $check_in = $_POST['check_in'] ?? null;
    $check_out = $_POST['check_out'] ?? null;
    $jumlah_kamar = (int) ($_POST['jumlah_kamar'] ?? 0);
    
    // [PENTING] Tangkap ID kamar dari room_detail.php
    $selected_ids_str = $_POST['selected_room_ids'] ?? ''; 

    // Validasi: Jika kosong, kembalikan
    if (empty($selected_ids_str)) {
        echo "<script>alert('Harap pilih kamar terlebih dahulu!'); window.history.back();</script>";
        exit;
    }

    $selected_room_ids = explode(',', $selected_ids_str);
    
} else {
    header('Location: index.php');
    exit;
}

// 3. AMBIL DATA TYPE KAMAR & HITUNG HARGA
try {
    // A. Info Tipe Kamar
    $sql_type = "SELECT nama_tipe, harga_weekdays, harga_weekend FROM room_types WHERE room_type_id = ?";
    $stmt_type = $mysqli->prepare($sql_type);
    $stmt_type->bind_param("i", $room_type_id);
    $stmt_type->execute();
    $result_type = $stmt_type->get_result();
    $room_type = $result_type->fetch_assoc();

    if (!$room_type) { throw new Exception("Detail kamar tidak ditemukan."); }

    // B. Ambil Nomor Kamar Asli (Untuk Tampilan Konfirmasi)
    $ids_safe = array_map('intval', $selected_room_ids);
    $ids_query = implode(',', $ids_safe);
    
    $sql_nomor = "SELECT nomor_kamar FROM rooms WHERE room_id IN ($ids_query) ORDER BY nomor_kamar ASC";
    $res_nomor = $mysqli->query($sql_nomor);
    
    $list_nomor_kamar = [];
    while($row = $res_nomor->fetch_assoc()){
        $list_nomor_kamar[] = $row['nomor_kamar'];
    }
    $display_nomor_kamar = implode(', ', $list_nomor_kamar);

    // C. Hitung Durasi & Harga
    $start_date = new DateTime($check_in);
    $end_date = new DateTime($check_out);
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($start_date, $interval, $end_date);

    $count_weekday = 0;
    $count_weekend = 0;

    foreach ($period as $dt) {
        $day_num = $dt->format('N'); 
        if ($day_num == 6 || $day_num == 7) { 
            $count_weekend++;
        } else {
            $count_weekday++;
        }
    }
    
    $subtotal_weekday = $count_weekday * $room_type['harga_weekdays'];
    $subtotal_weekend = $count_weekend * $room_type['harga_weekend'];
    $harga_satu_kamar_total = $subtotal_weekday + $subtotal_weekend;
    $total_harga_kamar = $harga_satu_kamar_total * $jumlah_kamar;

    // D. Ambil Fasilitas Tambahan
    $sql_fas = "SELECT * FROM fasilitas_tambahan ORDER BY nama_fasilitas ASC";
    $fasilitas_result = $mysqli->query($sql_fas);
    $fasilitas_list = $fasilitas_result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    echo "<div class='container'><p class='alert alert-error'>Error: ".$e->getMessage()."</p></div>";
    include 'includes/footer.php';
    exit;
}
?>

<div class="container">
    <div class="form-container" style="max-width: 800px; margin: 30px auto;">
        <h2>Konfirmasi & Tambah Fasilitas</h2>
        <p>Pesanan untuk: <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></strong></p>

        <form action="actions/action_booking.php" method="POST" id="booking-form">
            
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            <input type="hidden" name="room_type_id" value="<?php echo $room_type_id; ?>">
            <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
            <input type="hidden" name="check_out" value="<?php echo $check_out; ?>">
            <input type="hidden" name="jumlah_kamar" value="<?php echo $jumlah_kamar; ?>">
            <input type="hidden" name="total_harga_kamar" value="<?php echo $total_harga_kamar; ?>">
            
            <input type="hidden" name="selected_room_ids" value="<?php echo htmlspecialchars($selected_ids_str); ?>">

            <div class="booking-summary" style="background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                <h4 style="margin-top: 0; border-bottom: 2px solid #ddd; padding-bottom: 10px;">Rincian Pesanan</h4>
                
                <div style="margin-bottom: 15px;">
                    <p><strong>Tipe Kamar:</strong> <?php echo htmlspecialchars($room_type['nama_tipe']); ?></p>
                    <p><strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($check_in)); ?> s/d <?php echo date('d/m/Y', strtotime($check_out)); ?></p>
                    
                    <div style="margin-top:10px; padding:10px; background:#e8f4fd; border-left: 5px solid #2196F3; border-radius:4px;">
                        <strong>Nomor Kamar yang Dipilih:</strong> <br>
                        <span style="font-size: 1.2em; font-weight: bold; color: #0d47a1;">
                            <?php echo $display_nomor_kamar; ?>
                        </span>
                    </div>
                </div>

                <table style="width: 100%; font-size: 14px; border-collapse: collapse; margin-top: 20px;">
                    <tr style="border-bottom: 1px solid #ddd; background: #eee;">
                        <th style="text-align: left; padding: 8px;">Keterangan</th>
                        <th style="text-align: right; padding: 8px;">Harga Satuan</th>
                        <th style="text-align: center; padding: 8px;">Qty</th>
                        <th style="text-align: right; padding: 8px;">Subtotal</th>
                    </tr>
                    
                    <?php if($count_weekday > 0): ?>
                    <tr>
                        <td style="padding: 8px;">Malam Weekday</td>
                        <td style="text-align: right;">Rp <?php echo number_format($room_type['harga_weekdays'], 0, ',', '.'); ?></td>
                        <td style="text-align: center;"><?php echo $count_weekday; ?> malam</td>
                        <td style="text-align: right;">Rp <?php echo number_format($subtotal_weekday, 0, ',', '.'); ?></td>
                    </tr>
                    <?php endif; ?>

                    <?php if($count_weekend > 0): ?>
                    <tr>
                        <td style="padding: 8px; color: #d9534f;">Malam Weekend</td>
                        <td style="text-align: right; color: #d9534f;">Rp <?php echo number_format($room_type['harga_weekend'], 0, ',', '.'); ?></td>
                        <td style="text-align: center; color: #d9534f;"><?php echo $count_weekend; ?> malam</td>
                        <td style="text-align: right; color: #d9534f;">Rp <?php echo number_format($subtotal_weekend, 0, ',', '.'); ?></td>
                    </tr>
                    <?php endif; ?>

                    <tr style="border-top: 2px solid #ddd; font-weight: bold;">
                        <td colspan="3" style="text-align: right; padding: 8px;">Harga 1 Kamar:</td>
                        <td style="text-align: right; padding: 8px;">Rp <?php echo number_format($harga_satu_kamar_total, 0, ',', '.'); ?></td>
                    </tr>
                    <?php if($jumlah_kamar > 1): ?>
                    <tr style="background-color: #f1f1f1;">
                        <td colspan="3" style="text-align: right; padding: 8px;">x <?php echo $jumlah_kamar; ?> Kamar:</td>
                        <td style="text-align: right; padding: 8px;">Rp <?php echo number_format($total_harga_kamar, 0, ',', '.'); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div class="fasilitas-section" style="margin-top: 30px;">
                <h4>Tambah Fasilitas (Opsional)</h4>
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead style="background: #eee;">
                        <tr>
                            <th style="text-align: left; padding: 10px;">Fasilitas</th>
                            <th style="text-align: left; padding: 10px;">Harga</th>
                            <th style="text-align: center; padding: 10px;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fasilitas_list)): ?>
                            <tr><td colspan="3" style="padding:10px;">Tidak ada fasilitas tambahan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($fasilitas_list as $fas): ?>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 10px;">
                                    <strong><?php echo htmlspecialchars($fas['nama_fasilitas']); ?></strong>
                                </td>
                                <td style="padding: 10px;">
                                    Rp <?php echo number_format($fas['harga'], 0, ',', '.'); ?>
                                </td>
                                <td style="padding: 10px; text-align: center;">
                                    <input type="number" 
                                           name="fasilitas[<?php echo $fas['fasilitas_id']; ?>]" 
                                           class="fasilitas-qty" 
                                           value="0" min="0" 
                                           data-price="<?php echo $fas['harga']; ?>"
                                           style="width: 60px; padding: 5px; text-align: center;">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <hr style="margin: 30px 0;">

            <div class="grand-total-box" style="background: #fff3cd; padding: 20px; text-align: right; border-radius: 8px; border: 1px solid #ffeeba;">
                <span style="font-size: 16px; color: #856404;">Total Bayar:</span><br>
                <span id="display-grand-total" style="font-size: 28px; font-weight: bold; color: #d39e00;">
                    Rp <?php echo number_format($total_harga_kamar, 0, ',', '.'); ?>
                </span>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <button type="submit" class="btn-primary full-width" style="padding: 15px; font-size: 18px;">
                    Lanjut ke Pembayaran &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const baseRoomPrice = <?php echo $total_harga_kamar; ?>;
const qtyInputs = document.querySelectorAll('.fasilitas-qty');
const displayTotal = document.getElementById('display-grand-total');

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka).replace('IDR', 'Rp');
}

function calculateTotal() {
    let totalFasilitas = 0;
    qtyInputs.forEach(input => {
        const price = parseFloat(input.dataset.price);
        const qty = parseInt(input.value) || 0;
        if (qty > 0) totalFasilitas += (price * qty);
    });
    displayTotal.innerText = formatRupiah(baseRoomPrice + totalFasilitas);
}

qtyInputs.forEach(input => {
    input.addEventListener('input', calculateTotal);
    input.addEventListener('change', calculateTotal);
});
</script>

<?php include 'includes/footer.php'; ?>