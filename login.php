<?php
// /login.php
// VERSI DENGAN FIX DOUBLE INCLUDE

// HANYA muat header.php, dia akan memuat config dan auth
include 'includes/header.php';

// Cek jika user sudah login
if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
?>
<div class="form-container">
    <h2>Login</h2>
    <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    <?php if ($error_message): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <form action="actions/action_login.php" method="POST">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-primary full-width">Login</button>
        </div>
    </form>
</div>
<?php include 'includes/footer.php'; ?>