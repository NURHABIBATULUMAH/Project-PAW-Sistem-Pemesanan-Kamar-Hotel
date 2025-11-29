<?php
// /booking_history.php
// VERSI TANPA UBAH DATABASE
// Menambahkan Kolom UI "No. Kamar" tapi datanya statis

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'config/database.php'; 
include_once 'core/auth.php'; 

require_login();
$user_id = $_SESSION['user_id'];

include_once 'includes/header.php';

try {
    // Query Standard (Sesuai database asli kamu)
    $sql = "SELECT 
                B.*, 
                RT.nama_tipe,
                P.status_bayar,
                P.bukti_bayar,
                P.payment_id,
                P.metode_bayar,
                RV.review_id,
                (SELECT GROUP_CONCAT(CONCAT(BF.jumlah, 'x ', FT.nama_fasilitas) SEPARATOR '<br>') 
                 FROM booking_fasilitas BF
                 JOIN fasilitas_tambahan FT ON BF.fasilitas_id = FT.fasilitas_id
                 WHERE BF.booking_id = B.booking_id
                ) AS fasilitas_dipesan
            FROM Bookings B
            JOIN Room_Types RT ON B.room_type_id = RT.room_type_id
            JOIN Payments P ON B.booking_id = P.booking_id
            LEFT JOIN Reviews RV ON B.booking_id = RV.booking_id
            WHERE B.user_id = ?
            ORDER BY B.created_at DESC";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $riwayat_bookings = $result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    echo "<div class='container'><p class='alert alert-error'>Error Database: " . $e->getMessage() . "</p></div>";
    $riwayat_bookings = [];
}

$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['error_message'], $_SESSION['success_message']);
?>

<div class="container profile-page">
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
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Tgl. Pesan</th>
                        <th>Tipe Kamar</th>
                        
                        <th style="text-align: center;">No. Kamar</th>
                        
                        <th style="text-align: center;">Jml.</th>
                        <th>Tgl. Menginap</th>
                        <th>Fasilitas Tambahan</th>
                        <th>Total Bayar</th>
                        <th>Status Booking</th>
                        <th>Status Bayar & Aksi</th>
                        <th>Ulasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat_bookings as $booking): ?>
                        <tr>
                            <td style="font-size: 13px; color: #555;">
                                <?php echo date('d M Y', strtotime($booking['created_at'])); ?>
                            </td>
                            
                            <td style="font-weight: bold; color: var(--dark-color);">
                                <?php echo htmlspecialchars($booking['nama_tipe']); ?>
                            </td>

                            <td style="text-align: center; font-size: 12px; color: #777;">
                                <em>Saat Check-in</em>
                            </td>

                            <td style="text-align: center; font-weight: bold;">
                                <?php echo htmlspecialchars($booking['jumlah_kamar']); ?>
                            </td>
                            
                            <td style="font-size: 13px;">
                                <span style="color: #28a745;">In:</span> <?php echo date('d/m/Y', strtotime($booking['tanggal_check_in'])); ?> <br>
                                <span style="color: #dc3545;">Out:</span> <?php echo date('d/m/Y', strtotime($booking['tanggal_check_out'])); ?>
                            </td>
                            
                            <td style="font-size: 12px; color: #666; line-height: 1.4;">
                                <?php echo $booking['fasilitas_dipesan'] ?? '-'; ?>
                            </td>

                            <td style="font-weight: bold; color: var(--primary-color);">
                                Rp <?php echo number_format($booking['total_bayar'], 0, ',', '.'); ?>
                            </td>
                            
                            <td>
                                <span class="status status-<?php echo strtolower($booking['status_booking']); ?>">
                                    <?php echo htmlspecialchars($booking['status_booking']); ?>
                                </span>
                            </td>

                            <td style="min-width: 250px;">
                                <?php if ($booking['status_bayar'] == 'Pending' && $booking['status_booking'] == 'Confirmed'): ?>
                                    
                                    <?php if (empty($booking['bukti_bayar'])): ?>
                                        <div class="payment-instruction-box alert-yellow">
                                            <p style="font-weight: bold; margin-bottom: 5px; font-size:12px;">Bayar Sekarang:</p>

                                            <div id="instruction-display-<?php echo $booking['payment_id']; ?>" style="background: #fff; padding: 5px; border: 1px dashed #ccc; margin-bottom: 5px; font-size: 11px;">
                                                <p>Pilih bank untuk lihat rekening.</p>
                                            </div>

                                            <form action="actions/action_upload_bukti.php" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                <input type="hidden" name="payment_id" value="<?php echo $booking['payment_id']; ?>">
                                                
                                                <div class="form-group-inline" style="margin-bottom: 5px;">
                                                    <select name="bank_name" 
                                                            id="bank_<?php echo $booking['payment_id']; ?>" 
                                                            onchange="showPaymentInstruction(this, <?php echo $booking['payment_id']; ?>)"
                                                            required 
                                                            style="width: 100%; padding: 5px; font-size: 12px;">
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

                                                <label class="upload-label">Upload Bukti:</label>
                                                <input type="file" name="bukti_bayar" class="upload-input" required accept="image/*" style="font-size: 11px;">
                                                
                                                <button type="submit" class="btn-upload">Kirim</button>
                                            </form>
                                        </div>

                                    <?php else: ?>
                                        <span class="status status-pending">Menunggu Verifikasi</span>
                                        <div class="payment-instruction-box alert-success" style="background-color: #e6fffa; border-color: #b2f5ea; padding: 5px;">
                                            <p style="font-size: 11px;">Metode: <strong><?php echo htmlspecialchars($booking['metode_bayar']); ?></strong></p>
                                            <p style="font-size: 11px;">Bukti terkirim.</p>
                                        </div>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <span class="status status-<?php echo strtolower($booking['status_bayar']); ?>">
                                        <?php echo htmlspecialchars($booking['status_bayar']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <?php 
                                $status_banned_for_review = ['Cancelled', 'Rejected', 'Pending'];
                                $can_review = (
                                    $booking['status_bayar'] == 'Success' && 
                                    !in_array($booking['status_booking'], $status_banned_for_review) && 
                                    empty($booking['review_id'])
                                );
                                ?>

                                <?php if ($can_review): ?>
                                    <a href="leave_review.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn-review">Ulas</a>
                                <?php elseif (!empty($booking['review_id'])): ?>
                                    <span class="reviewed-text">✔ Selesai</span>
                                <?php else: ?>
                                    <span class="disabled-text">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
/* Style tambahan */
.payment-instruction-box { margin-top: 5px; padding: 10px; border-radius: 5px; }
.alert-yellow { border-color: #f0ad4e; background-color: #fff3e0; }
.alert-blue { border-color: #b3e0ff; background-color: #e6f7ff; }
.upload-label { font-size: 11px; font-weight: bold; display: block; margin-bottom: 2px; }
.btn-upload { width: 100%; padding: 5px; margin-top: 5px; background-color: var(--success-color); color: white; border: none; cursor: pointer; border-radius: 3px; font-weight: bold; font-size: 12px;}
.btn-review { 
    display: inline-block; 
    padding: 5px 10px; 
    background-color: var(--primary-color); 
    color: white; 
    text-decoration: none; 
    border-radius: 3px; 
    font-size: 11px;
}
.reviewed-text { color: green; font-weight: bold; font-size: 11px; }
.disabled-text { color: #aaa; font-size: 11px; }
</style>

<script>
// Data Nomor Rekening
const paymentDetails = {
    'QRIS': '<p style="text-align: center;">Scan QR Code:</p><img src="<?php echo BASE_URL; ?>assets/images/qris.jpg" style="width: 80px; margin: 0 auto; display: block;">',
    'BCA': '<p style="font-size:11px">BCA: <strong style="color:blue">888001234567</strong></p>',
    'Mandiri': '<p style="font-size:11px">Mandiri: <strong style="color:green">1230098765</strong></p>',
    'BNI': '<p style="font-size:11px">BNI: <strong style="color:red">0887776665</strong></p>',
    'BRI': '<p style="font-size:11px">BRI: <strong style="color:green">0447776665</strong></p>',
    'OVO': '<p style="font-size:11px">OVO: <strong style="color:purple">08123456789</strong></p>',
    'Gopay': '<p style="font-size:11px">Gopay: <strong style="color:green">08123456789</strong></p>',
};

function showPaymentInstruction(selectElement, paymentId) {
    const selectedMethod = selectElement.value;
    const displayContainer = document.getElementById('instruction-display-' + paymentId);

    if (selectedMethod && paymentDetails[selectedMethod]) {
        displayContainer.innerHTML = paymentDetails[selectedMethod];
    } else {
        displayContainer.innerHTML = '<p style="font-size:11px;">Pilih metode untuk lihat rekening.</p>';
    }
}
</script>

<?php
include 'includes/footer.php';
?>