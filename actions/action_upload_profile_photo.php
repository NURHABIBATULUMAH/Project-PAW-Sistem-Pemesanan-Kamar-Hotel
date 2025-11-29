<?php

session_start();
include '../config/database.php';
include '../core/auth.php'; 
require_login();

$user_id = $_SESSION['user_id'];
$target_dir = "../assets/images/profile/";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_file"])) {
    
    $file = $_FILES["profile_file"];
    
    if ($file['error'] != UPLOAD_ERR_OK) {
        $_SESSION['error_message'] = "Terjadi error saat upload.";
        header('Location: ../profile.php');
        exit;
    }
    
    // Cek tipe dan ukuran
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'png', 'jpeg'];
    
    if (!in_array($file_extension, $allowed_types) || $file["size"] > 2000000) { // Max 2MB
        $_SESSION['error_message'] = "File harus JPG/PNG dan maksimal 2MB.";
        header('Location: ../profile.php');
        exit;
    }

    // Tentukan nama file unik
    $new_file_name = "user_" . $user_id . "_" . time() . "." . $file_extension;
    $target_file_path = $target_dir . $new_file_name;

    // memindahkan file
    if (move_uploaded_file($file["tmp_name"], $target_file_path)) {
        
        // Update nama foto di database (Konversi ke MySQLi)
        try {
            // Ambil nama foto lama untuk dihapus (Query 1 - MySQLi)
            $sql_old = "SELECT profile_photo FROM Users WHERE user_id = ?";
            $stmt_old = $mysqli->prepare($sql_old);
            $stmt_old->bind_param("i", $user_id);
            $stmt_old->execute();
            $result_old = $stmt_old->get_result();
            $old_photo_row = $result_old->fetch_row(); // Ambil baris pertama
            $old_photo = $old_photo_row ? $old_photo_row[0] : null; // Ambil kolom pertama

            // Update nama foto baru (Query 2 - MySQLi)
            $sql_update = "UPDATE Users SET profile_photo = ? WHERE user_id = ?";
            $stmt_update = $mysqli->prepare($sql_update);
            $stmt_update->bind_param("si", $new_file_name, $user_id); // "si" = string, integer
            $stmt_update->execute();

            // Hapus foto lama dari server
            if ($old_photo && file_exists($target_dir . $old_photo) && $old_photo != 'placeholder_user.png') {
                 unlink($target_dir . $old_photo);
            }

            $_SESSION['success_message'] = "Foto profil berhasil diunggah.";
            header('Location: ../profile.php');
            exit;

        } catch (Exception $e) {
            $_SESSION['error_message'] = "Gagal menyimpan data ke database: " . $e->getMessage();
            header('Location: ../profile.php');
            exit;
        }

    } else {
        $_SESSION['error_message'] = "Gagal memindahkan file ke folder server.";
        header('Location: ../profile.php');
        exit;
    }

} else {
    header('Location: ../profile.php');
    exit;
}
?>