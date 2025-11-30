<?php

include '../config/database.php';
include '../core/auth.php';
require_admin();

$message = '';
$message_type = '';

try {
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $booking_code = $_GET['id']; 
        $action = $_GET['action'];
        $sql = null;
        
        if ($action == 'confirm') {
            $sql = "UPDATE bookings SET status_booking = 'Confirmed' WHERE booking_code = ?";
            $message = "Booking berhasil dikonfirmasi.";
            $message_type = 'success';
        } elseif ($action == 'cancel') {
            $sql = "UPDATE bookings SET status_booking = 'Cancelled' WHERE booking_code = ?";
            $message = "Booking berhasil dibatalkan.";
            $message_type = 'success';
        } elseif ($action == 'checkout') {
            $sql = "UPDATE bookings SET status_booking = 'Completed' WHERE booking_code = ?";
            $message = "Tamu berhasil Check-Out. Pesanan Selesai.";
            $message_type = 'success';
        } 
        
        elseif ($action == 'pay_success') {
            $sql = "UPDATE payments SET status_bayar = 'Success' WHERE booking_code = ?";
            $message = "Pembayaran ditandai Lunas.";
            $message_type = 'success';
        }

        if (isset($sql)) {
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("s", $booking_code); 
            $stmt->execute();
        }
    }

    $sql_select = "SELECT 
                    B.booking_code,
                    MIN(B.tanggal_check_in) AS tanggal_check_in,
                    MIN(B.tanggal_check_out) AS tanggal_check_out,
                    MIN(B.status_booking) AS status_booking,
                    MIN(B.created_at) AS created_at,
                    MIN(U.nama) AS nama_pelanggan,
                    MIN(RT.nama_tipe) AS nama_tipe,

                    GROUP_CONCAT(R.nomor_kamar ORDER BY R.nomor_kamar ASC SEPARATOR ', ') AS daftar_nomor_kamar,
                    COUNT(B.room_id) AS jumlah_kamar_group,

                    MIN(P.status_bayar) AS status_bayar,
                    MIN(P.bukti_bayar) AS bukti_bayar,
                    SUM(P.jumlah_bayar) AS total_bayar_group

                FROM bookings B
                JOIN users U ON B.user_id = U.user_id
                JOIN room_types RT ON B.room_type_id = RT.room_type_id
                LEFT JOIN rooms R ON B.room_id = R.room_id
                LEFT JOIN payments P ON B.booking_code = P.booking_code

                GROUP BY B.booking_code
                ORDER BY created_at DESC";
                    
    $result_select = $mysqli->query($sql_select);
    $all_bookings = $result_select->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $message = "Error database: " . $e->getMessage();
    $message_type = 'error';
}

include '../includes/admin_header.php';
?>

<div class="admin-layout">

    <main class="admin-content">
        <div class="content-header">
            <h1>Kelola Pesanan Pelanggan</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="admin-card">
            <h3>Daftar Seluruh Pesanan</h3>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Pelanggan</th>
                            <th>Tipe Kamar & Total</th>
                            
                            <th>No. Kamar</th>
                            
                            <th>Tanggal Pesan</th> 
                            <th>Check in</th> 
                            <th>Check Out</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Bayar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_bookings as $booking): ?>
                        <tr>
                            <td style="font-weight: bold; color: #007bff;"><?php echo $booking['booking_code']; ?></td>
                            <td><?php echo htmlspecialchars($booking['nama_pelanggan']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($booking['nama_tipe']); ?> 
                                <br>
                                <small>(<?php echo htmlspecialchars($booking['jumlah_kamar_group']); ?> Kamar)</small>
                            </td>
                            
                            <td style="font-weight: bold; color: #007bff;">
                                <?php echo htmlspecialchars($booking['daftar_nomor_kamar']); ?>
                            </td>
                            
                            <td>
                                <?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?> 
                            </td>

                            <td>
                                <?php echo date('d/m/Y', strtotime($booking['tanggal_check_in'])); ?> 
                            </td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($booking['tanggal_check_out'])); ?>
                            </td>

                            <td>Rp <?php echo number_format($booking['total_bayar_group'] ?? 0, 0, ',', '.'); ?></td>
                            
                            <td>
                                <?php 
                                    $status_class = '';
                                    if($booking['status_booking'] == 'Pending') $status_class = 'text-warning';
                                    elseif($booking['status_booking'] == 'Confirmed') $status_class = 'text-success';
                                    elseif($booking['status_booking'] == 'Cancelled') $status_class = 'text-danger';
                                    elseif($booking['status_booking'] == 'Completed') $status_class = 'text-muted';
                                ?>
                                <span class="<?php echo $status_class; ?>" style="font-weight:bold;">
                                    <?php echo htmlspecialchars($booking['status_booking']); ?>
                                </span>
                            </td>
                            
                            <td>
                                <?php if ($booking['status_bayar'] == 'Success'): ?>
                                    <span style="color: green; font-weight: bold;">LUNAS</span>
                                <?php else: ?>
                                    <span style="color: orange;">PENDING</span>
                                <?php endif; ?>
                                
                                <?php if (!empty($booking['bukti_bayar'])): ?>
                                    <br>
                                    <a href="<?php echo BASE_URL . 'assets/uploads/' . htmlspecialchars($booking['bukti_bayar']); ?>" 
                                       target="_blank" class="view-proof-link">Lihat Bukti</a>
                                <?php endif; ?>
                            </td>

                            <td class="action-links">
                                <?php
                                $code = $booking['booking_code']; // Menggunakan code sebagai identifier
                                
                                if ($booking['status_booking'] == 'Pending') {
                                ?>
                                    <a href="manage_bookings.php?action=confirm&id=<?php echo $code; ?>" class="edit-link">Konfirmasi</a><br>
                                    <a href="manage_bookings.php?action=cancel&id=<?php echo $code; ?>" class="delete-link" onclick="return confirm('Yakin batalkan?');">Batalkan</a>
                                
                                <?php
                                } elseif ($booking['status_booking'] == 'Confirmed') {
                                    if ($booking['status_bayar'] != 'Success') {
                                        echo '<a href="manage_bookings.php?action=cancel&id='.$code.'" class="delete-link" style="font-size:12px;" onclick="return confirm(\'Yakin batalkan?\');">Batalkan Pesanan</a><br>';
                                    }
                                    if ($booking['status_bayar'] == 'Pending') {
                                        if (!empty($booking['bukti_bayar'])) {
                                            echo '<a href="manage_bookings.php?action=pay_success&id='.$code.'" class="edit-link" style="color:green;">Tandai Lunas</a>';
                                        } else {
                                            echo '<span class="disabled-text">Menunggu Bukti</span>';
                                        }
                                    } else {
                                        echo '<a href="manage_bookings.php?action=checkout&id='.$code.'" class="edit-link" style="color: darkblue;">Check Out (Selesai)</a>';
                                    }
                                } elseif ($booking['status_booking'] == 'Completed') {
                                    echo '<span style="color:gray;">✔ Selesai</span>';
                                } elseif ($booking['status_booking'] == 'Cancelled') {
                                    echo '<span class="disabled-text">- Dibatalkan -</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>