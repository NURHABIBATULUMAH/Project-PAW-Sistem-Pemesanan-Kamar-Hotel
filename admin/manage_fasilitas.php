<?php
include '../includes/admin_header.php';

$message = '';
$message_type = '';
$edit_fasilitas = null;

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nama_fasilitas = $_POST['nama_fasilitas'];
        $deskripsi = $_POST['deskripsi'];
        $harga = $_POST['harga'];
        $id_to_update = $_POST['fasilitas_id'] ?? null;
        
        if ($id_to_update) {
            // UPDATE
            $sql = "UPDATE fasilitas_tambahan SET nama_fasilitas=?, deskripsi=?, harga=? WHERE fasilitas_id=?";
            $stmt = $mysqli->prepare($sql);
            // "ssdi" = string, string, double, integer
            $stmt->bind_param("ssdi", $nama_fasilitas, $deskripsi, $harga, $id_to_update);
            $stmt->execute();
            $message = "Fasilitas berhasil diperbarui.";
        } else {
            // CREATE
            $sql = "INSERT INTO fasilitas_tambahan (nama_fasilitas, deskripsi, harga) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            // "ssd" = string, string, double
            $stmt->bind_param("ssd", $nama_fasilitas, $deskripsi, $harga);
            $stmt->execute();
            $message = "Fasilitas baru berhasil ditambahkan.";
        }
        $message_type = 'success';
    }
    
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id_to_delete = $_GET['id'];
        
        try {
            $sql_delete = "DELETE FROM fasilitas_tambahan WHERE fasilitas_id = ?";
            $stmt_delete = $mysqli->prepare($sql_delete);
            $stmt_delete->bind_param("i", $id_to_delete);
            $stmt_delete->execute();
            
            $message = "Fasilitas berhasil dihapus.";
            $message_type = 'success';
        } catch (Exception $e) { 
             if ($e->getCode() == 1451) { 
                $message = "Gagal menghapus! Fasilitas ini sudah pernah dipesan oleh pelanggan.";
            } else {
                $message = "Error database: " . $e->getMessage();
            }
            $message_type = 'error';
        }
    }

    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $id_to_edit = $_GET['id'];
        
        $sql_edit = "SELECT * FROM fasilitas_tambahan WHERE fasilitas_id = ?";
        $stmt_edit = $mysqli->prepare($sql_edit);
        $stmt_edit->bind_param("i", $id_to_edit);
        $stmt_edit->execute();
        $result_edit = $stmt_edit->get_result();
        $edit_fasilitas = $result_edit->fetch_assoc();
    }
    
    $all_fasilitas_result = $mysqli->query("SELECT * FROM fasilitas_tambahan ORDER BY nama_fasilitas ASC");
    $all_fasilitas = $all_fasilitas_result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $message = "Error saat memproses: " . $e->getMessage();
    $message_type = 'error';
}
?>

<div class="content-header">
    <h1>Kelola Fasilitas Tambahan</h1>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo ($message_type == 'success') ? 'alert-success' : 'alert-error'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="admin-card">
    <h3><?php echo $edit_fasilitas ? 'Edit Fasilitas' : 'Tambah Fasilitas Baru'; ?></h3>
    
    <form action="manage_fasilitas.php" method="POST" class="admin-form">
        <input type="hidden" name="fasilitas_id" value="<?php echo $edit_fasilitas['fasilitas_id'] ?? ''; ?>">
        
        <div class="form-group">
            <label for="nama_fasilitas">Nama Fasilitas</label>
            <input type="text" id="nama_fasilitas" name="nama_fasilitas" value="<?php echo htmlspecialchars($edit_fasilitas['nama_fasilitas'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="harga">Harga Satuan (Rp)</label>
            <input type="number" id="harga" name="harga" value="<?php echo htmlspecialchars($edit_fasilitas['harga'] ?? ''); ?>" required min="0">
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
            <label for="deskripsi">Deskripsi Singkat</label>
            <textarea id="deskripsi" name="deskripsi" required><?php echo htmlspecialchars($edit_fasilitas['deskripsi'] ?? ''); ?></textarea>
        </div>
        
        <button type="submit" class="btn-admin"><?php echo $edit_fasilitas ? 'Update Fasilitas' : 'Tambah Fasilitas'; ?></button>
        
        <?php if ($edit_fasilitas): ?>
            <a href="manage_fasilitas.php" style="text-align: center; grid-column: 1 / -1; margin-top: 10px;">Batal Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h3>Daftar Fasilitas Saat Ini</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Fasilitas</th>
                <th>Harga</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_fasilitas as $fas): ?>
            <tr>
                <td><?php echo $fas['fasilitas_id']; ?></td>
                <td><?php echo htmlspecialchars($fas['nama_fasilitas']); ?></td>
                <td>Rp <?php echo number_format($fas['harga'], 0, ',', '.'); ?></td>
                <td style="max-width: 300px; font-size: 14px;"><?php echo htmlspecialchars($fas['deskripsi']); ?></td>
                <td class="action-links">
                    <a href="manage_fasilitas.php?action=edit&id=<?php echo $fas['fasilitas_id']; ?>" class="edit-link">Edit</a>
                    <a href="manage_fasilitas.php?action=delete&id=<?php echo $fas['fasilitas_id']; ?>" class="delete-link" 
                       onclick="return confirm('Yakin menghapus fasilitas ini?');">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
include '../includes/admin_footer.php'; 
?>