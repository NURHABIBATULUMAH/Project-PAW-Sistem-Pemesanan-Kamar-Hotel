<?php


include '../includes/admin_header.php'; 

$message = '';
$message_type = '';
$edit_room = null;

try {
    // === LOGIKA PROSES FORM (CREATE, UPDATE, DELETE) ===

    // PROSES DELETE (MENGURANGI KUANTITAS)
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id_to_delete = $_GET['id'];
        
        try {
            $sql_delete = "DELETE FROM Rooms WHERE room_id = ?";
            $stmt_delete = $mysqli->prepare($sql_delete);
            $stmt_delete->bind_param("i", $id_to_delete);
            // menjelankan perintah ke database
            $stmt_delete->execute();
            
            $message = "Kamar berhasil dihapus (Stok berkurang).";
            $message_type = 'success';
        } catch (Exception $e) { 
            if ($e->getCode() == 1451) { // Error jika kamar ada di history booking
                $message = "Gagal menghapus! Kamar ini ada di riwayat booking. Ubah statusnya menjadi 'Under Maintenance' saja.";
            } else {
                $message = "Error database: " . $e->getMessage();
            }
            $message_type = 'error';
        }
    }

    // PROSES CREATE (MENAMBAH KUANTITAS) & UPDATE
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $room_type_id = $_POST['room_type_id'];
        $nomor_kamar = $_POST['nomor_kamar'];
        $status = $_POST['status'];
        // jika room id ada maka update jika tidak nambah
        $id_to_update = $_POST['room_id'] ?? null;

        if ($id_to_update) {
            // UPDATE
            $sql = "UPDATE Rooms SET room_type_id = ?, nomor_kamar = ?, status = ? WHERE room_id = ?";
            $stmt = $mysqli->prepare($sql);
            // "issi" = integer, string, string, integer
            $stmt->bind_param("issi", $room_type_id, $nomor_kamar, $status, $id_to_update);
            $stmt->execute();
            $message = "Data kamar berhasil diperbarui.";
        } else {
            // CREATE (INI YANG MENAMBAH STOK)
            $sql = "INSERT INTO Rooms (room_type_id, nomor_kamar, status) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            // "iss" = integer, string, string
            $stmt->bind_param("iss", $room_type_id, $nomor_kamar, $status);
            $stmt->execute();
            $message = "Kamar baru berhasil ditambahkan (Stok bertambah).";
        }
        $message_type = 'success';
    }

    // PROSES EDIT (AMBIL DATA)
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $id_to_edit = $_GET['id'];
        $sql_edit = "SELECT * FROM Rooms WHERE room_id = ?";
        $stmt_edit = $mysqli->prepare($sql_edit);
        $stmt_edit->bind_param("i", $id_to_edit);
        $stmt_edit->execute();
        $result_edit = $stmt_edit->get_result();
        $edit_room = $result_edit->fetch_assoc();
    }

    //  AMBIL DATA UTAMA UNTUK TABEL
    // Query 1: Ambil Tipe Kamar (untuk dropdown)
    $all_types_result = $mysqli->query("SELECT * FROM Room_Types");
    $all_types = $all_types_result->fetch_all(MYSQLI_ASSOC);
    
    // Query 2: Ambil Daftar Kamar Fisik (Inilah Stok Anda)
    $all_rooms_result = $mysqli->query("SELECT Rooms.*, Room_Types.nama_tipe 
                               FROM Rooms 
                               JOIN Room_Types ON Rooms.room_type_id = Room_Types.room_type_id
                               ORDER BY Rooms.nomor_kamar ASC");
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
            <label for="status">Status Kamar</label>
            <select id="status" name="status" required>
                <option value="Available" <?php echo (isset($edit_room) && $edit_room['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                <option value="Under Maintenance" <?php echo (isset($edit_room) && $edit_room['status'] == 'Under Maintenance') ? 'selected' : ''; ?>>Under Maintenance</option>
            </select>
        </div>
        
        <div class="form-group"></div> 

        <button type="submit" class="btn-admin"><?php echo $edit_room ? 'Update' : 'Tambah Kamar'; ?></button>
        
        <?php if ($edit_room): ?>
            <a href="manage_rooms.php" style="text-align: center; grid-column: 1 / -1; margin-top: 10px;">Batal Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h3>Daftar Kamar & Status</h3>
    <p>Jumlah baris di bawah ini adalah total stok kamar Anda.</p>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Nomor Kamar</th>
                <th>Tipe Kamar</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_rooms as $room): ?>
            <tr>
                <td><?php echo htmlspecialchars($room['nomor_kamar']); ?></td>
                <td><?php echo htmlspecialchars($room['nama_tipe']); ?></td>
                <td><?php echo htmlspecialchars($room['status']); ?></td>
                <td class="action-links">
                    <a href="manage_rooms.php?action=edit&id=<?php echo $room['room_id']; ?>" class="edit-link">Edit</a>
                    <a href="manage_rooms.php?action=delete&id=<?php echo $room['room_id']; ?>" class="delete-link" 
                       onclick="return confirm('Yakin ingin menghapus kamar <?php echo htmlspecialchars($room['nomor_kamar']); ?>?');">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
include '../includes/admin_footer.php'; 
?>