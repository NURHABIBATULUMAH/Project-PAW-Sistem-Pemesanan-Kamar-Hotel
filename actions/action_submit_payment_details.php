<?php
session_start();
include '../config/database.php';
include '../core/auth.php';

require_login();

// folder upload
$target_dir = "../assets/uploads/";

// membuat folder jika belum ada
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $booking_id = $_POST['booking_id'];
    $payment_id = $_POST['payment_id'];
    $file_to_upload = $_FILES["bukti_bayar"];

    $redirect_page = 'Location: ../booking_history.php';

    // memvalidasi apakah file dipilih
    if (!isset($file_to_upload) || $file_to_upload['error'] == UPLOAD_ERR_NO_FILE) {
        $_SESSION['error_message'] = "Anda tidak memilih file untuk di-upload.";
        header($redirect_page);
        exit;
    }

    if ($file_to_upload['error'] != UPLOAD_ERR_OK) {
        $_SESSION['error_message'] = "Terjadi error saat upload: " . $file_to_upload['error'];
        header($redirect_page);
        exit;
    }

    // membuat nama file unik
    $file_extension = strtolower(pathinfo($file_to_upload["name"], PATHINFO_EXTENSION));
    $new_file_name = "payment_" . $payment_id . "_" . time() . "." . $file_extension;
    $target_file_path = $target_dir . $new_file_name;

    if ($file_to_upload["size"] > 2000000) {
        $_SESSION['error_message'] = "Ukuran file terlalu besar. Maksimal 2MB.";
        header($redirect_page);
        exit;
    }

    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_extension, $allowed_types)) {
        $_SESSION['error_message'] = "Hanya file JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
        header($redirect_page);
        exit;
    }

    // memindahkan file upload
    if (move_uploaded_file($file_to_upload["tmp_name"], $target_file_path)) {

        // memperbaharui database dengan nama file (Konversi ke MySQLi)
        try {
            $sql_update = "UPDATE Payments SET bukti_bayar = ? WHERE payment_id = ?";
            $stmt_update = $mysqli->prepare($sql_update);
            $stmt_update->bind_param("si", $new_file_name, $payment_id); // "si" = string, integer
            $stmt_update->execute();

            $_SESSION['success_message'] = "Bukti bayar berhasil di-upload! Menunggu verifikasi admin.";
            header($redirect_page);
            exit;

        } catch (Exception $e) {
            // hapus file yang terlanjur tersimpan
            if (file_exists($target_file_path)) {
                unlink($target_file_path);
            }

            $_SESSION['error_message'] = "Gagal menyimpan ke database: " . $e->getMessage();
            header($redirect_page);
            exit;
        }

    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat memindahkan file.";
        header($redirect_page);
        exit;
    }

} else {
    // akses langsung tanpa POST
    header('Location: ../booking_history.php');
    exit;
}
?>