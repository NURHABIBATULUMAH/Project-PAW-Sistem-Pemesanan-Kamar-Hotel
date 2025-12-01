<?php
session_start();
include '../config/database.php';
include '../core/auth.php';

require_login();

// mendefinisikan Folder Upload
$target_dir = "../assets/uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

$redirect_url = '../booking_history.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // menangkap data
    $booking_code = $_POST['booking_code'] ?? null;
    $bank_name    = $_POST['bank_name'] ?? 'Transfer';
    $file_to_upload = $_FILES["bukti_bayar"];

    // mengavalidasi inputan
    if (empty($booking_code)) {
        $_SESSION['error_message'] = "Terjadi kesalahan: Kode Booking tidak ditemukan.";
        header("Location: " . $redirect_url);
        exit;
    }

    // memastikan file ada
    if (!isset($file_to_upload) || $file_to_upload['error'] == UPLOAD_ERR_NO_FILE) {
        $_SESSION['error_message'] = "Anda belum memilih file foto bukti bayar.";
        header("Location: " . $redirect_url);
        exit;
    }

    //mengavalidasi ukuran file dan tipe
    if ($file_to_upload["size"] > 2097152) {
        $_SESSION['error_message'] = "Ukuran file terlalu besar (Max 2MB). Silakan kompres gambar.";
        header("Location: " . $redirect_url);
        exit;
    }

    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    $file_ext = strtolower(pathinfo($file_to_upload["name"], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_types)) {
        $_SESSION['error_message'] = "Format file tidak didukung. Harap upload JPG, PNG, atau PDF.";
        header("Location: " . $redirect_url);
        exit;
    }

    // rename dan upload
    $safe_code = preg_replace('/[^A-Za-z0-9\-]/', '', $booking_code); 
    $new_file_name = "bukti_" . $safe_code . "_" . time() . "." . $file_ext;
    $target_file_path = $target_dir . $new_file_name;

    if (move_uploaded_file($file_to_upload["tmp_name"], $target_file_path)) {
        
        try { // update database dengan MySQLi            
            $sql_update = "UPDATE payments 
                           SET metode_bayar = ?, 
                               bukti_bayar = ?, 
                               tanggal_bayar = NOW() 
                           WHERE booking_code = ?";
            
            $stmt = $mysqli->prepare($sql_update);
            
            $stmt->bind_param("sss", $bank_name, $new_file_name, $booking_code);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Bukti pembayaran berhasil dikirim! Mohon tunggu verifikasi admin.";
            } else {
                throw new Exception("Gagal update database.");
            }
            
            header("Location: " . $redirect_url);
            exit;

        } catch (Exception $e) {
            // hapus file jika gagal update database agar tidak jadi sampah
            if (file_exists($target_file_path)) unlink($target_file_path);

            $_SESSION['error_message'] = "Error Database: " . $e->getMessage();
            header("Location: " . $redirect_url);
            exit;
        }
        
    } else {
        $_SESSION['error_message'] = "Gagal mengupload file ke folder server.";
        header("Location: " . $redirect_url);
        exit;
    }

} else {
    // akses tanpa post
    header("Location: " . $redirect_url);
    exit;
}
?>