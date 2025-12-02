<?php
include '../includes/admin_header.php'; 
$message = '';
$message_type = '';
$edit_type = null;

try {
    $target_dir = "../assets/images/rooms/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // LOGIKA PROSES FORM (CREATE & UPDATE) - Konversi ke MySQLi
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nama_tipe = $_POST['nama_tipe'];
        $deskripsi_tipe = $_POST['deskripsi_tipe'];
        $harga_weekdays = $_POST['harga_weekdays'];
        $harga_weekend = $_POST['harga_weekend'];
        $id_to_update = $_POST['room_type_id'] ?? null;
        $foto_utama_lama = $_POST['foto_lama'] ?? null;
        $foto_file = $_FILES['foto_utama'] ?? null;
        
        $foto_nama = $foto_utama_lama; // Default: pakai foto lama

        // Proses File Upload (Logika tidak berubah)
        if ($foto_file && $foto_file['error'] == UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($foto_file["name"], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png'];
            
            if (!in_array($file_ext, $allowed_types) || $foto_file['size'] > 5000000) { // Max 5MB
                $message = "Gagal upload. File harus JPG/PNG dan maksimal 5MB.";
                $message_type = 'error';
                throw new Exception($message); // Lewatkan proses DB
            }
            
            $new_foto_name = "kamar_" . time() . "." . $file_ext;
            if (move_uploaded_file($foto_file["tmp_name"], $target_dir . $new_foto_name)) {
                $foto_nama = $new_foto_name;
                if ($id_to_update && $foto_utama_lama && file_exists($target_dir . $foto_utama_lama)) {
                    unlink($target_dir . $foto_utama_lama);
                }
            } else {
                throw new Exception("Gagal memindahkan file foto.");
            }
        }
        // Query DB (Konversi ke MySQLi)
        if ($id_to_update) {
            // UPDATE
            $sql = "UPDATE room_types SET nama_tipe=?, deskripsi_tipe=?, harga_weekdays=?, harga_weekend=?, foto_utama=? WHERE room_type_id=?";
            $stmt = $mysqli->prepare($sql);
            // "ssddsi" = string, string, double, double, string, integer
            $stmt->bind_param("ssddsi", $nama_tipe, $deskripsi_tipe, $harga_weekdays, $harga_weekend, $foto_nama, $id_to_update);
            $stmt->execute();
            $message = "Tipe kamar berhasil diperbarui.";
        } else {
            // CREATE
            $sql = "INSERT INTO room_types (nama_tipe, deskripsi_tipe, harga_weekdays, harga_weekend, foto_utama) VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            // "ssdds" = string, string, double, double, string
            $stmt->bind_param("ssdds", $nama_tipe, $deskripsi_tipe, $harga_weekdays, $harga_weekend, $foto_nama);
            $stmt->execute();
            $message = "Tipe kamar baru berhasil ditambahkan.";
        }
        $message_type = 'success';
    }
    // LOGIKA PROSES DELETE (Konversi ke MySQLi Transaction) 
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id_to_delete = $_GET['id'];
        
        // Ambil nama foto lama (Query 1 - MySQLi)
        $sql_old = "SELECT foto_utama FROM room_types WHERE room_type_id = ?";
        $stmt_old = $mysqli->prepare($sql_old);
        $stmt_old->bind_param("i", $id_to_delete);
        $stmt_old->execute();
        $result_old = $stmt_old->get_result();
        $old_foto_row = $result_old->fetch_row();
        $old_foto = $old_foto_row ? $old_foto_row[0] : null;
        
        $mysqli->begin_transaction(); // Mulai Transaksi (Sintaks MySQLi)
        try {
            // Hapus keterkaitan di tabel Rooms dulu (Query 2 - MySQLi)
            $stmt_room = $mysqli->prepare("DELETE FROM rooms WHERE room_type_id = ?");
            $stmt_room->bind_param("i", $id_to_delete);
            $stmt_room->execute();

            // Hapus tipe kamar (Query 3 - MySQLi)
            $stmt = $mysqli->prepare("DELETE FROM room_types WHERE room_type_id = ?");
            $stmt->bind_param("i", $id_to_delete);
            $stmt->execute();
            
            $mysqli->commit(); // Commit Transaksi (Sintaks MySQLi)
            
            // Hapus file fisik (Tidak berubah)
            if ($old_foto && file_exists($target_dir . $old_foto)) {
                unlink($target_dir . $old_foto);
            }
            $message = "Tipe kamar berhasil dihapus (dan semua kamar fisiknya).";
            $message_type = 'success';
            
        } catch (Exception $e) { // Tangkap 'Exception' umum
            $mysqli->rollback(); // Rollback Transaksi (Sintaks MySQLi)
             if ($e->getCode() == 1451) { // 1451 = Error Foreign Key Constraint
                $message = "Gagal menghapus! Ada booking aktif yang menggunakan tipe kamar ini.";
            } else {
                $message = "Error database: " . $e->getMessage();
            }
            $message_type = 'error';
        }
    }
    // LOGIKA PROSES EDIT (Konversi ke MySQLi) 
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $id_to_edit = $_GET['id'];
        $sql_edit = "SELECT * FROM room_types WHERE room_type_id = ?";
        $stmt_edit = $mysqli->prepare($sql_edit);
        $stmt_edit->bind_param("i", $id_to_edit);
        $stmt_edit->execute();
        $result_edit = $stmt_edit->get_result();
        $edit_type = $result_edit->fetch_assoc(); // Menggantikan fetch() PDO
    }
    
    // Ambil data utama (Konversi ke MySQLi)
    $all_types_result = $mysqli->query("SELECT * FROM room_types ORDER BY harga_weekdays ASC");
    $all_types = $all_types_result->fetch_all(MYSQLI_ASSOC); // Menggantikan fetchAll() PDO

} catch (Exception $e) {
    $message = "Error saat memproses: " . $e->getMessage();
    $message_type = 'error';
}
?>

<div class="content-header">
    <h1>Kelola Tipe Kamar</h1>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo ($message_type == 'success') ? 'success' : 'error'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="admin-card">
    <h3><?php echo $edit_type ? 'Edit Tipe Kamar' : 'Tambah Tipe Kamar Baru'; ?></h3>
    
    <form action="manage_room_types.php" method="POST" class="admin-form" enctype="multipart/form-data">
        <input type="hidden" name="room_type_id" value="<?php echo $edit_type['room_type_id'] ?? ''; ?>">
        <input type="hidden" name="foto_lama" value="<?php echo $edit_type['foto_utama'] ?? ''; ?>">
        
        <div class="form-group">
            <label for="nama_tipe">Nama Tipe Kamar</label>
            <input type="text" id="nama_tipe" name="nama_tipe" value="<?php echo htmlspecialchars($edit_type['nama_tipe'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="deskripsi_tipe">Deskripsi</label>
            <textarea id="deskripsi_tipe" name="deskripsi_tipe" required><?php echo htmlspecialchars($edit_type['deskripsi_tipe'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="harga_weekdays">Harga Weekdays (Rp)</label>
            <input type="number" id="harga_weekdays" name="harga_weekdays" value="<?php echo htmlspecialchars($edit_type['harga_weekdays'] ?? ''); ?>" required min="0">
        </div>

        <div class="form-group">
            <label for="harga_weekend">Harga Weekend (Rp)</label>
            <input type="number" id="harga_weekend" name="harga_weekend" value="<?php echo htmlspecialchars($edit_type['harga_weekend'] ?? ''); ?>" required min="0">
        </div>
        
        <div class="form-group">
            <label for="foto_utama">Foto Utama</label>
            <input type="file" id="foto_utama" name="foto_utama" <?php echo $edit_type ? '' : 'required'; ?>>
            <?php if ($edit_type && $edit_type['foto_utama']): ?>
                <p style="margin-top: 5px; font-size: 12px;">Foto saat ini: <?php echo htmlspecialchars($edit_type['foto_utama']); ?> (Biarkan kosong untuk mempertahankan)</p>
            <?php endif; ?>
        </div>
        
        <div class="form-group"></div> 

        <button type="submit" class="btn-admin"><?php echo $edit_type ? 'Update Tipe Kamar' : 'Tambah Tipe Kamar'; ?></button>
        
        <?php if ($edit_type): ?>
            <a href="manage_room_types.php" style="text-align: center; grid-column: 1 / -1; margin-top: 10px;">Batal Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h3>Daftar Tipe Kamar</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Foto</th>
                <th>Nama Tipe</th>
                <th>Harga Weekdays</th>
                <th>Harga Weekend</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_types as $type): ?>
            <tr>
                <td><img src="../assets/images/rooms/<?php echo htmlspecialchars($type['foto_utama']); ?>" style="width: 80px; height: 50px; object-fit: cover;"></td>
                <td><?php echo htmlspecialchars($type['nama_tipe']); ?></td>
                <td>Rp <?php echo number_format($type['harga_weekdays'], 0, ',', '.'); ?></td>
                <td>Rp <?php echo number_format($type['harga_weekend'], 0, ',', '.'); ?></td>
                <td style="max-width: 200px; font-size: 14px;"><?php echo htmlspecialchars(substr($type['deskripsi_tipe'], 0, 50)) . '...'; ?></td>
                <td class="action-links">
                    <a href="manage_room_types.php?action=edit&id=<?php echo $type['room_type_id']; ?>" class="edit-link">Edit</a>
                    <a href="manage_room_types.php?action=delete&id=<?php echo $type['room_type_id']; ?>" class="delete-link" 
                       onclick="return confirm('Yakin menghapus tipe kamar ini? Ini akan menghapus semua kamar fisik yang terkait!');">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
include '../includes/admin_footer.php'; 
?>