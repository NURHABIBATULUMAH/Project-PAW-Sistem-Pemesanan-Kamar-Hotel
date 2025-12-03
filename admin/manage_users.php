<?php

include '../includes/admin_header.php'; 

$message = '';
$message_type = '';
$edit_user = null;

try {
    // PROSES FORM (UPDATE) 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user_id = $_POST['user_id'];
        $nama = $_POST['nama'];
        $username = $_POST['username'];
        $nik = $_POST['nik'];
        $phone = $_POST['phone'];
        $role = $_POST['role']; 

        // update data pengguna
        $sql = "UPDATE Users SET nama=?, username=?, nik=?, phone=?, role=? WHERE user_id=?";
        $stmt = $mysqli->prepare($sql);
        // "sssssi" = string, string, string, string, string, integer
        $stmt->bind_param("sssssi", $nama, $username, $nik, $phone, $role, $user_id);
        $stmt->execute();
        
        $message = "Data pengguna berhasil diperbarui.";
        $message_type = 'success';
    }
    
    // PROSES DELETE 
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id_to_delete = $_GET['id'];
        
        if ($id_to_delete == $_SESSION['user_id']) {
            $message = "Tidak bisa menghapus akun admin yang sedang login.";
            $message_type = 'error';
        } else {
             try {
                $sql_delete = "DELETE FROM Users WHERE user_id = ?";
                $stmt_delete = $mysqli->prepare($sql_delete);
                $stmt_delete->bind_param("i", $id_to_delete);
                $stmt_delete->execute();
                
                $message = "Pengguna berhasil dihapus.";
                $message_type = 'success';
            } catch (Exception $e) { // Tangkap 'Exception' umum
                if ($e->getCode() == 1451) { // 1451 = Error Foreign Key
                    $message = "Gagal menghapus! Pengguna ini memiliki riwayat pemesanan aktif.";
                } else {
                    $message = "Error database: " . $e->getMessage();
                }
                $message_type = 'error';
            }
        }
    }

    // PROSES EDIT 
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $id_to_edit = $_GET['id'];
        
        $sql_edit = "SELECT * FROM Users WHERE user_id = ?";
        $stmt_edit = $mysqli->prepare($sql_edit);
        $stmt_edit->bind_param("i", $id_to_edit);
        $stmt_edit->execute();
        $result_edit = $stmt_edit->get_result();
        $edit_user = $result_edit->fetch_assoc(); 
    }
    
    // Ambil data utama 
    $all_users_result = $mysqli->query("SELECT * FROM Users ORDER BY created_at DESC");
    $all_users = $all_users_result->fetch_all(MYSQLI_ASSOC); 
} catch (Exception $e) { 
    $message = "Error saat memproses: " . $e->getMessage();
    $message_type = 'error';
}
?>

<div class="content-header">
    <h1>Kelola Pengguna Sistem</h1>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo ($message_type == 'success') ? 'success' : 'error'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if ($edit_user): ?>
<div class="admin-card" style="margin-bottom: 20px;">
    <h3>Edit Pengguna: <?php echo htmlspecialchars($edit_user['nama']); ?></h3>
    
    <form action="manage_users.php" method="POST" class="admin-form">
        <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
        
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($edit_user['nama'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email (Tidak Dapat Diubah)</label>
            <input type="text" id="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($edit_user['username'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="nik">NIK</label>
            <input type="text" id="nik" name="nik" value="<?php echo htmlspecialchars($edit_user['nik'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($edit_user['phone'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="role">Role (Penting!)</label>
            <select id="role" name="role" required>
                <option value="customer" <?php echo ($edit_user['role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                <option value="admin" <?php echo ($edit_user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        
        <button type="submit" class="btn-admin">Update Pengguna</button>
        
        <a href="manage_users.php" style="text-align: center; grid-column: 1 / -1; margin-top: 10px;">Batal Edit</a>
    </form>
</div>
<?php endif; ?>

<div class="admin-card">
    <h3>Daftar Seluruh Pengguna</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Phone</th>
                <th>NIK</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['nama']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($user['nik'] ?? '-'); ?></td>
                <td>
                    <span style="font-weight: bold; color: <?php echo ($user['role'] == 'admin') ? '#1abc9c' : '#3498db'; ?>;">
                        <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                    </span>
                </td>
                <td class="action-links">
                    <a href="manage_users.php?action=edit&id=<?php echo $user['user_id']; ?>" class="edit-link">Edit</a>
                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                        <a href="manage_users.php?action=delete&id=<?php echo $user['user_id']; ?>" class="delete-link" 
                           onclick="return confirm('Yakin menghapus <?php echo htmlspecialchars($user['nama']); ?>?');">Hapus</a>
                    <?php else: ?>
                        <span class="disabled-text">Akun Anda</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
include '../includes/admin_footer.php'; 
?>