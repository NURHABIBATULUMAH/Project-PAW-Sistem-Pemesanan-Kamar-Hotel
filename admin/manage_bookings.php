<?php

include '../config/database.php';
include '../core/auth.php';
require_admin();

$message = '';
$message_type = '';

try {
    // === LOGIKA PROSES AKSI (UPDATE STATUS) ===
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $booking_id = $_GET['id'];
        $action = $_GET['action'];
        
        $sql = null;
        
        if ($action == 'confirm') {
            $sql = "UPDATE Bookings SET status_booking = 'Confirmed' WHERE booking_id = ?";
            $message = "Booking berhasil dikonfirmasi.";
            $message_type = 'success';
        } elseif ($action == 'cancel') {
            $sql = "UPDATE Bookings SET status_booking = 'Cancelled' WHERE booking_id = ?";
            $message = "Booking berhasil dibatalkan.";
            $message_type = 'success';
        } elseif ($action == 'pay_success') {
            $sql = "UPDATE Payments SET status_bayar = 'Success' WHERE booking_id = ?";
            $message = "Pembayaran ditandai Lunas.";
            $message_type = 'success';
        } elseif ($action == 'checkout') {
            $sql = "UPDATE Bookings SET status_booking = 'Completed' WHERE booking_id = ?";
            $message = "Tamu berhasil Check-Out. Pesanan Selesai.";
            $message_type = 'success';
        }
        
        if (isset($sql)) {
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
        }
    }

    // === AMBIL SEMUA DATA BOOKING ===
    $sql_select = "SELECT 
                        B.*, 
                        U.nama AS nama_pelanggan, 
                        RT.nama_tipe, 
                        P.status_bayar,
                        P.bukti_bayar
                   FROM Bookings B
                   JOIN Users U ON B.user_id = U.user_id
                   JOIN Room_Types RT ON B.room_type_id = RT.room_type_id
                   JOIN Payments P ON B.booking_id = P.booking_id
                   ORDER BY B.created_at DESC";
                   
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
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Tipe Kamar & Total Kamar</th>
                            <th>Tanggal Bookings</th> 
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
                            <td><?php echo $booking['booking_id']; ?></td>
                            <td><?php echo htmlspecialchars($booking['nama_pelanggan']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($booking['nama_tipe']); ?> 
                                (<?php echo htmlspecialchars($booking['jumlah_kamar']); ?> Kamar)
                            </td>
                            
                            <td>
                                <?php 
                                    echo date('d/m/Y H:i', strtotime($booking['created_at'])); 
                                ?>
                            </td>

                            <td>
                                <?php echo date('d/m/Y', strtotime($booking['tanggal_check_in'])); ?> 
                            </td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($booking['tanggal_check_out'])); ?>
                            </td>

                            <td>Rp <?php echo number_format($booking['total_bayar'], 0, ',', '.'); ?></td>
                            
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
                                if ($booking['status_booking'] == 'Pending') {
                                ?>
                                    <a href="manage_bookings.php?action=confirm&id=<?php echo $booking['booking_id']; ?>" class="edit-link">Konfirmasi</a><br>
                                    <a href="manage_bookings.php?action=cancel&id=<?php echo $booking['booking_id']; ?>" class="delete-link" onclick="return confirm('Yakin batalkan?');">Batalkan</a>
                                
                                <?php
                                } elseif ($booking['status_booking'] == 'Confirmed') {
                                    if ($booking['status_bayar'] != 'Success') {
                                        echo '<a href="manage_bookings.php?action=cancel&id='.$booking['booking_id'].'" class="delete-link" style="font-size:12px;" onclick="return confirm(\'Tamu belum bayar. Yakin batalkan?\');">Batalkan Pesanan</a><br>';
                                    }
                                    if ($booking['status_bayar'] == 'Pending') {
                                        if (!empty($booking['bukti_bayar'])) {
                                            echo '<a href="manage_bookings.php?action=pay_success&id='.$booking['booking_id'].'" class="edit-link" style="color:green;">Tandai Lunas</a>';
                                        } else {
                                            echo '<span class="disabled-text">Menunggu Bukti</span>';
                                        }
                                    } else {
                                        echo '<a href="manage_bookings.php?action=checkout&id='.$booking['booking_id'].'" class="edit-link" style="color: darkblue;">Check Out (Selesai)</a>';
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