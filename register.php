<?php
include 'includes/header.php';

// Cek jika user sudah login
if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}
?>
<div class="form-container">
    <h2>Registrasi Akun Baru</h2>
    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    <form action="actions/action_register.php" method="POST">
        <div class="form-group">
            <label for="nama">Nama Lengkap:</label>
            <input type="text" id="nama" name="nama" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-primary full-width">Daftar</button>
        </div>
    </form>
</div>
<?php include 'includes/footer.php'; ?>