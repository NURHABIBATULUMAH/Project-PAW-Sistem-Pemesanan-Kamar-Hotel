<?php
include 'includes/header.php'; 

require_login();
$user_id = $_SESSION['user_id'];
$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['error_message'], $_SESSION['success_message']);

try {
    $sql = "SELECT * FROM Users WHERE user_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); 
    if (!$user) {
        throw new Exception("Data pengguna tidak ditemukan.");
    }
} catch (Exception $e) {
    echo "<div class='container'><p class='alert alert-error'>".$e->getMessage()."</p></div>";
    include 'includes/footer.php';
    exit;
}

$profile_pic = !empty($user['profile_photo']) ? 'assets/images/profile/' . htmlspecialchars($user['profile_photo']) : 'assets/images/placeholder_user.png';
?>

<div class="container profile-page">
    <h2>Profil Saya</h2>
    
    <?php if ($error_message): ?><div class="alert alert-error"><?php echo $error_message; ?></div><?php endif; ?>
    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>

    <div class="profile-layout">
        
        <div class="profile-sidebar">
            <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" class="profile-photo-lg">
            <h3><?php echo htmlspecialchars($user['nama']); ?></h3>
            <p class="role"><?php echo htmlspecialchars($user['role']); ?></p>

            <form action="actions/action_upload_profile_photo.php" method="POST" enctype="multipart/form-data" class="upload-form">
                <label for="profile_file" class="btn-secondary full-width">Tambah Foto</label>
                <input type="file" name="profile_file" id="profile_file" style="display: none;" onchange="this.form.submit()" required>
            </form>
            
            <a href="booking_history.php" class="btn-primary full-width" style="margin-top: 15px;">Lihat Riwayat Pesanan</a>
        </div>
        
        <div class="profile-form-area">
            <form action="actions/action_update_profile.php" method="POST">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="nik">NIK *</label>
                    <input type="text" name="nik" value="<?php echo htmlspecialchars($user['nik'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone *</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="birthday">Birthday</label>
                    <input type="date" name="birthday" value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                <label>Gender</label>
                <div class="gender-radio-group">
                    <input type="radio" id="gender-pria" name="gender" value="Pria" <?php echo ($user['gender'] == 'Pria') ? 'checked' : ''; ?>>
                    <label for="gender-pria" class="gender-radio-label">Pria</label>
                    
                    <input type="radio" id="gender-wanita" name="gender" value="Wanita" <?php echo ($user['gender'] == 'Wanita') ? 'checked' : ''; ?>>
                    <label for="gender-wanita" class="gender-radio-label">Wanita</label>
                </div>
                </div>
                
                <button type="submit" class="btn-primary" style="margin-top: 20px;">Simpan Perubahan</button>
            </form>
        </div>

    </div>
</div>

<?php
include 'includes/footer.php';
?>