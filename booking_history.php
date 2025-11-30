<?php
// /booking_history.php
// VERSI FINAL: KOLOM REVIEW ADA + REKENING LENGKAP + GROUP BOOKING

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config/database.php'; 
include 'core/auth.php'; 

require_login();
$user_id = $_SESSION['user_id'];

// === QUERY KHUSUS (GROUP BOOKING + REVIEW) ===
$sql = "SELECT 
            B.booking_code,
            B.booking_id,
            B.tanggal_check_in,
            B.tanggal_check_out,
            B.status_booking,
            B.created_at, 
            RT.nama_tipe,
            
            -- DATA GABUNGAN
            GROUP_CONCAT(R.nomor_kamar ORDER BY R.nomor_kamar ASC SEPARATOR ', ') AS daftar_nomor_kamar,
            COUNT(B.room_id) AS jumlah_kamar, 
            
            -- DATA PEMBAYARAN
            P.status_bayar,
            P.bukti_bayar,
            P.metode_bayar,
            P.jumlah_bayar AS total_bayar_group,
            
            -- DATA REVIEW
            RV.review_id,
            RV.rating
            
        FROM Bookings B
        JOIN Room_Types RT ON B.room_type_id = RT.room_type_id
        LEFT JOIN Rooms R ON B.room_id = R.room_id
        LEFT JOIN Payments P ON B.booking_code = P.booking_code
        LEFT JOIN Reviews RV ON B.booking_id = RV.booking_id 
        
        WHERE B.user_id = ?
        GROUP BY B.booking_code 
        ORDER BY B.created_at DESC";

try {
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $riwayat_bookings = $result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    echo "<div class='container'><p class='alert alert-error'>Error Database: " . $e->getMessage() . "</p></div>";
    $riwayat_bookings = [];
}

// Bersihkan pesan session
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

include 'includes/header.php';
?>

<div class="container profile-page" style="margin-top: 30px; margin-bottom: 50px;">
    <h2>Riwayat Pemesanan Anda</h2>
    
    <?php if ($error_message): ?><div class="alert alert-error"><?php echo $error_message; ?></div><?php endif; ?>
    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>

    <div class="booking-history-list">
        <?php if (empty($riwayat_bookings)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <p>Anda belum memiliki riwayat pemesanan.</p>
                <a href="rooms.php" class="btn-primary" style="display:inline-block; width:auto; margin-top:10px;">Pesan Kamar Sekarang</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="history-table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead style="background: #f8f9fa; border-bottom: 2px solid #ddd;">
                        <tr>
                            <th style="padding: 10px;">Tgl. Pesan</th>
                            <th style="padding: 10px;">Kode & Tipe</th>
                            <th style="padding: 10px; text-align: center;">No. Kamar</th>
                            <th style="padding: 10px;">Tgl. Menginap</th>
                            <th style="padding: 10px;">Total Bayar</th>
                            <th style="padding: 10px;">Status</th>
                            <th style="padding: 10px; min-width: 250px;">Pembayaran</th>
                            <th style="padding: 10px; text-align: center;">Ulasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat_bookings as $booking): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px; color: #555;">
                                    <?php echo date('d M Y', strtotime($booking['created_at'])); ?>
                                </td>
                                
                                <td style="padding: 10px;">
                                    <span style="font-weight: bold; color: #333;">
                                        <?php echo htmlspecialchars($booking['nama_tipe']); ?>
                                    </span>
                                    <br>
                                    <small style="color: #007bff;">#<?php echo $booking['booking_code']; ?></small>
                                </td>

                                <td style="padding: 10px; text-align: center; font-weight: bold; color: #007bff;">
                                    <?php 
                                        if (!empty($booking['daftar_nomor_kamar'])) {
                                            echo $booking['daftar_nomor_kamar'];
                                            echo "<br><small style='color:#666; font-weight:normal;'>(" . $booking['jumlah_kamar'] . " Kamar)</small>";
                                        } else {
                                            echo "-";
                                        }
                                    ?>
                                </td>
                                
                                <td style="padding: 10px;">
                                    <span style="color: #28a745;">In:</span> <?php echo date('d/m/y', strtotime($booking['tanggal_check_in'])); ?> <br>
                                    <span style="color: #dc3545;">Out:</span> <?php echo date('d/m/y', strtotime($booking['tanggal_check_out'])); ?>
                                </td>

                                <td style="padding: 10px; font-weight: bold;">
                                    Rp <?php echo number_format((float)($booking['total_bayar_group'] ?? 0), 0, ',', '.'); ?>
                                </td>
                                
                                <td style="padding: 10px;">
                                    <?php 
                                        $sb = $booking['status_booking'];
                                        $color = 'orange';
                                        if($sb=='Confirmed') $color='green';
                                        if($sb=='Cancelled') $color='red';
                                        if($sb=='Completed') $color='blue';
                                    ?>
                                    <span style="color:<?php echo $color; ?>; font-weight:bold;">
                                        <?php echo htmlspecialchars($sb); ?>
                                    </span>
                                </td>

                                <td style="padding: 10px;">
                                    
                                    <?php if ($booking['status_bayar'] == 'Pending' && $booking['status_booking'] == 'Confirmed'): ?>
                                        
                                        <?php if (!empty($booking['bukti_bayar'])): ?>
                                            <div class="payment-instruction-box alert-success" style="background-color: #e6fffa; border: 1px solid #b2f5ea; padding: 10px; border-radius: 5px;">
                                                <p style="font-size: 11px; margin:0;">Metode: <strong><?php echo htmlspecialchars($booking['metode_bayar'] ?? 'Transfer'); ?></strong></p>
                                                <p style="font-size: 11px; margin:0;">Bukti terkirim. Tunggu Admin.</p>
                                            </div>
                                        
                                        <?php else: ?>
                                            <div class="payment-instruction-box alert-yellow" style="background-color: #fff3e0; border: 1px solid #f0ad4e; padding: 10px; border-radius: 5px;">
                                                <p style="font-weight: bold; margin-bottom: 5px; font-size:12px;">Bayar Sekarang:</p>

                                                <div id="instruction-display-<?php echo $booking['booking_code']; ?>" style="background: #fff; padding: 5px; border: 1px dashed #ccc; margin-bottom: 5px; font-size: 11px;">
                                                    <p>Pilih bank untuk lihat rekening.</p>
                                                </div>

                                                <form action="actions/action_upload_bukti.php" method="POST" enctype="multipart/form-data">
                                                    <input type="hidden" name="booking_code" value="<?php echo $booking['booking_code']; ?>">
                                                    
                                                    <div class="form-group-inline" style="margin-bottom: 5px;">
                                                        <select name="bank_name" 
                                                                onchange="showPaymentInstruction(this, '<?php echo $booking['booking_code']; ?>')"
                                                                required 
                                                                style="width: 100%; padding: 5px; font-size: 12px; border:1px solid #ddd;">
                                                            <option value="">-- Metode Bayar --</option>
                                                            <option value="BCA">BCA (VA)</option>
                                                            <option value="Mandiri">Mandiri</option>
                                                            <option value="BNI">BNI</option>
                                                            <option value="BRI">BRI</option>
                                                            <option value="QRIS">QRIS (Scan)</option>
                                                            <option value="OVO">OVO</option>
                                                            <option value="Gopay">Gopay</option>
                                                        </select>
                                                    </div>

                                                    <label class="upload-label" style="font-size:11px; font-weight:bold;">Upload Bukti:</label>
                                                    <input type="file" name="bukti_bayar" required accept="image/*,.pdf" style="font-size: 11px; width:100%;">
                                                    
                                                    <button type="submit" class="btn-upload" style="margin-top:5px; width:100%; padding:5px; background:#28a745; color:white; border:none; cursor:pointer;">Kirim Bukti</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>

                                    <?php elseif ($booking['status_bayar'] == 'Success'): ?>
                                        <span style="color: green; font-weight:bold;">✔ LUNAS</span>
                                    <?php elseif ($booking['status_booking'] == 'Cancelled'): ?>
                                        <span style="color: red;">Dibatalkan</span>
                                    <?php else: ?>
                                        <span style="color: orange;"><?php echo $booking['status_bayar']; ?></span>
                                    <?php endif; ?>
                                </td>
                                
                                <td style="padding: 10px; text-align: center;">
                                    <?php if (!empty($booking['review_id'])): ?>
                                        <span style="color: #28a745; font-weight: bold; font-size:12px;">✔ Selesai</span>
                                        <br>
                                        <small>Rating: <?php echo $booking['rating']; ?>/5</small>
                                    
                                    <?php elseif ($booking['status_bayar'] == 'Success'): ?>
                                        <a href="leave_review.php?booking_id=<?php echo $booking['booking_id']; ?>" 
                                           style="display:inline-block; padding:5px 10px; background:#007bff; color:white; text-decoration:none; border-radius:4px; font-size:12px;">
                                           Beri Ulasan
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #aaa;">-</span>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// DAFTAR REKENING LENGKAP (SESUAI REQUEST)
const paymentDetails = {
    'QRIS': '<p style="text-align: center;">Scan QR Code:</p><img src="<?php echo BASE_URL; ?>assets/images/qris.jpg" style="width: 100px; margin: 0 auto; display: block;">',
    'BCA': '<p style="font-size:11px">BCA: <strong style="color:blue">888001234567</strong><br>A.n Hotel Madura</p>',
    'Mandiri': '<p style="font-size:11px">Mandiri: <strong style="color:green">1230098765</strong></p>',
    'BNI': '<p style="font-size:11px">BNI: <strong style="color:red">0887776665</strong></p>',
    'BRI': '<p style="font-size:11px">BRI: <strong style="color:green">0447776665</strong></p>',
    'OVO': '<p style="font-size:11px">OVO: <strong style="color:purple">08123456789</strong></p>',
    'Gopay': '<p style="font-size:11px">Gopay: <strong style="color:green">08123456789</strong></p>'
};

function showPaymentInstruction(selectElement, bookingCode) {
    const selectedMethod = selectElement.value;
    const displayContainer = document.getElementById('instruction-display-' + bookingCode);

    if (selectedMethod && paymentDetails[selectedMethod]) {
        displayContainer.innerHTML = paymentDetails[selectedMethod];
    } else {
        displayContainer.innerHTML = '<p style="font-size:11px;">Pilih metode untuk lihat rekening.</p>';
    }
}
</script>

<?php include 'includes/footer.php'; ?>