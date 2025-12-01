<?php

include '../config/database.php'; 
include '../core/auth.php';    
require_admin();

include '../includes/admin_header.php'; 

$message = '';
$message_type = '';
$edit_room = null;

try {
    // === 1. LOGIKA PROSES DELETE (VERSI FIX) ===
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id_to_delete = $_GET['id'];
        
        // Cek apakah ada tamu AKTIF atau AKAN DATANG
        // Kita gunakan tanggal hari ini sebagai patokan
        $today = date('Y-m-d');
        
        $sql_cek = "SELECT booking_code FROM bookings 
                    WHERE room_id = ? 
                    AND status_booking IN ('Confirmed', 'Paid', 'Pending') 
                    AND tanggal_check_out >= ?";
                    
        $stmt_cek = $mysqli->prepare($sql_cek);
        $stmt_cek->bind_param("is", $id_to_delete, $today);
        $stmt_cek->execute();
        $result_cek = $stmt_cek->get_result();
        
        if ($result_cek->num_rows > 0) {
            // Jika ada tamu aktif/future -> Blokir
            $message = "Gagal hapus! Kamar sedang ada penghuninya atau terikat pesanan aktif (Check-out di masa depan).";
            $message_type = 'error';
        } else {
            // Jika aman (kosong atau history masa lalu) -> Hapus
            try {
                $sql_delete = "DELETE FROM rooms WHERE room_id = ?";
                $stmt_delete = $mysqli->prepare($sql_delete);
                $stmt_delete->bind_param("i", $id_to_delete);
                $stmt_delete->execute();
                
                $message = "Kamar berhasil dihapus.";
                $message_type = 'success';
            } catch (Exception $e) { 
                 $message = "Gagal menghapus. Error: " . $e->getMessage();
                 $message_type = 'error';
            }
        }
    }

    // === 2. LOGIKA PROSES CREATE & UPDATE ===
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $room_type_id = $_POST['room_type_id'];
        $nomor_kamar = $_POST['nomor_kamar'];
        $status = $_POST['status'];
        $id_to_update = $_POST['room_id'] ?? null;

        if ($id_to_update) {
            // UPDATE
            $sql = "UPDATE rooms SET room_type_id = ?, nomor_kamar = ?, status = ? WHERE room_id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("issi", $room_type_id, $nomor_kamar, $status, $id_to_update);
            $stmt->execute();
            $message = "Data kamar berhasil diperbarui.";
        } else {
            // CREATE (TAMBAH STOK)
            $sql = "INSERT INTO rooms (room_type_id, nomor_kamar, status) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("iss", $room_type_id, $nomor_kamar, $status);
            $stmt->execute();
            $message = "Kamar baru berhasil ditambahkan.";
        }
        $message_type = 'success';
    }

    // PROSES EDIT (AMBIL DATA UTK FORM) 
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $id_to_edit = $_GET['id'];
        $sql_edit = "SELECT * FROM rooms WHERE room_id = ?";
        $stmt_edit = $mysqli->prepare($sql_edit);
        $stmt_edit->bind_param("i", $id_to_edit);
        $stmt_edit->execute();
        $result_edit = $stmt_edit->get_result();
        $edit_room = $result_edit->fetch_assoc();
    }

    // AMBIL DATA UTAMA (DENGAN CEK PENGHUNI)
    // Query 1: Ambil Tipe Kamar (untuk dropdown)
    $all_types_result = $mysqli->query("SELECT * FROM room_types");
    $all_types = $all_types_result->fetch_all(MYSQLI_ASSOC);
    
    // Query 2: Ambil Daftar Kamar + Status Huni (Subquery Pintar)
    $today = date('Y-m-d');
    $sql_rooms = "SELECT rooms.*, room_types.nama_tipe,
                  (
                    SELECT COUNT(*) FROM bookings b 
                    WHERE b.room_id = rooms.room_id 
                    AND b.status_booking IN ('Confirmed', 'Paid') 
                    AND '$today' >= b.tanggal_check_in 
                    AND '$today' < b.tanggal_check_out
                  ) as sedang_diinap
                  FROM rooms 
                  JOIN room_types ON rooms.room_type_id = room_types.room_type_id
                  ORDER BY rooms.nomor_kamar ASC";

    $all_rooms_result = $mysqli->query($sql_rooms);
    $all_rooms = $all_rooms_result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $message = "Error database: " . $e->getMessage();
    $message_type = 'error';
}
?>

<div class="content-header">
    <h1>Kelola Stok Kamar Fisik</h1>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo ($message_type == 'success') ? 'alert-success' : 'alert-error'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="admin-card">
    <h3><?php echo $edit_room ? 'Edit Kamar' : 'Tambah Kamar Baru (Tambah Stok)'; ?></h3>
    
    <form action="manage_rooms.php" method="POST" class="admin-form">
        <input type="hidden" name="room_id" value="<?php echo $edit_room['room_id'] ?? ''; ?>">
        
        <div class="form-group">
            <label for="nomor_kamar">Nomor Kamar (Contoh: 101, 201A)</label>
            <input type="text" id="nomor_kamar" name="nomor_kamar" value="<?php echo htmlspecialchars($edit_room['nomor_kamar'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="room_type_id">Tipe Kamar</label>
            <select id="room_type_id" name="room_type_id" required>
                <option value="">-- Pilih Tipe Kamar --</option>
                <?php foreach ($all_types as $type): ?>
                    <option value="<?php echo $type['room_type_id']; ?>" 
                        <?php echo (isset($edit_room) && $edit_room['room_type_id'] == $type['room_type_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['nama_tipe']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status Kamar (Fisik)</label>
            <select id="status" name="status" required>
                <option value="Available" <?php echo (isset($edit_room) && $edit_room['status'] == 'Available') ? 'selected' : ''; ?>>Available (Siap Jual)</option>
                <option value="Unavailable" <?php echo (isset($edit_room) && $edit_room['status'] == 'Unavailable') ? 'selected' : ''; ?>>Unavailable (Sedang Dipakai)</option>
                <option value="Under Maintenance" <?php echo (isset($edit_room) && $edit_room['status'] == 'Under Maintenance') ? 'selected' : ''; ?>>Under Maintenance (Rusak)</option>
            </select>
        </div>
        
        <div class="form-group"></div> 

        <button type="submit" class="btn-admin"><?php echo $edit_room ? 'Update Data' : 'Tambah Kamar'; ?></button>
        
        <?php if ($edit_room): ?>
            <a href="manage_rooms.php" style="display:inline-block; margin-top: 10px; color: red;">Batal Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h3>Daftar Kamar & Status Penghuni</h3>
    <p>Lihat stok kamar dan apakah ada tamu yang sedang menginap.</p>
    
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Tipe Kamar</th>
                    <th>Status Fisik</th>
                    <th>Penghuni (Hari Ini)</th> <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_rooms as $room): ?>
                <tr>
                    <td style="font-weight:bold; font-size:1.1rem;"><?php echo htmlspecialchars($room['nomor_kamar']); ?></td>
                    <td><?php echo htmlspecialchars($room['nama_tipe']); ?></td>
                    
                    <td>
                        <?php if($room['status'] == 'Available'): ?>
                             <span style="color:green; font-weight:bold;">✔ Siap Jual</span>
                        <?php elseif($room['status'] == 'Unavailable'): ?>
                             <span style="color:orange; font-weight:bold;">⚠ Dipakai</span>
                        <?php else: ?>
                             <span style="color:red; font-weight:bold;">🛠 Perbaikan</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php 
                            if ($room['status'] == 'Under Maintenance') {
                                echo '<span style="background:#ccc; color:#333; padding:3px 8px; border-radius:4px; font-size:12px;">Non-Aktif</span>';
                            } elseif ($room['sedang_diinap'] > 0) {
                                echo '<span style="background:#ff4757; color:white; padding:3px 8px; border-radius:4px; font-size:12px;">⛔ Ada Tamu</span>';
                            } else {
                                echo '<span style="background:#2ed573; color:white; padding:3px 8px; border-radius:4px; font-size:12px;">🟢 Kosong</span>';
                            }
                        ?>
                    </td>

                    <td class="action-links">
                        <a href="manage_rooms.php?action=edit&id=<?php echo $room['room_id']; ?>" class="edit-link" style="color:blue;">Edit</a>
                        |
                        <a href="manage_rooms.php?action=delete&id=<?php echo $room['room_id']; ?>" class="delete-link" 
                           style="color:red;"
                           onclick="return confirm('Yakin ingin menghapus kamar <?php echo htmlspecialchars($room['nomor_kamar']); ?>?');">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include '../includes/admin_footer.php'; 
?>