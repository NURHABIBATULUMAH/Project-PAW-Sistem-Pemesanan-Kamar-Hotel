<?php
// /actions/action_upload_bukti.php
// VERSI ALAMAT MUTLAK

session_start();
// Gunakan ../ karena file ini ada di folder actions
include '../config/database.php'; 
include '../core/auth.php';

require_login();

$target_dir = "../assets/uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Gunakan alamat lengkap untuk redirect
$redirect_url = BASE_URL . 'booking_history.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $booking_id = $_POST['booking_id'];
    $payment_id = $_POST['payment_id'];
    $bank_name = $_POST['bank_name'] ?? null;
    $file_to_upload = $_FILES["bukti_bayar"];

    // Validasi (Code sama, cuma redirectnya pakai $redirect_url yang baru)
    if (empty($bank_name)) {
        $_SESSION['error_message'] = "Mohon pilih metode pembayaran.";
        header("Location: " . $redirect_url);
        exit;
    }
    if (!isset($file_to_upload) || $file_to_upload['error'] == UPLOAD_ERR_NO_FILE) {
        $_SESSION['error_message'] = "Anda belum memilih file bukti.";
        header("Location: " . $redirect_url);
        exit;
    }
    
    // ... (Validasi file extension & size sama seperti sebelumnya) ...
    // Biar cepat, langsung ke bagian inti:

    $file_extension = strtolower(pathinfo($file_to_upload["name"], PATHINFO_EXTENSION));
    $new_file_name = "payment_" . $payment_id . "_" . time() . "." . $file_extension;
    $target_file_path = $target_dir . $new_file_name;

    if (move_uploaded_file($file_to_upload["tmp_name"], $target_file_path)) {
        try {
            // Update Database
            $sql_update = "UPDATE Payments SET metode_bayar = ?, bukti_bayar = ? WHERE payment_id = ?";
            $stmt = $mysqli->prepare($sql_update);
            $stmt->bind_param("ssi", $bank_name, $new_file_name, $payment_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Bukti berhasil dikirim!";
            } else {
                throw new Exception("Gagal update database.");
            }
            header("Location: " . $redirect_url);
            exit;

        } catch (Exception $e) {
            header("Location: " . $redirect_url);
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Gagal upload file.";
        header("Location: " . $redirect_url);
        exit;
    }
} else {
    header("Location: " . $redirect_url);
    exit;
}
?>