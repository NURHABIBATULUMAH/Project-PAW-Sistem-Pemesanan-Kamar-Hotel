<?php

session_start();
include '../config/database.php';
include '../core/auth.php'; 
require_login();

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data yang dikirim dari form profile.php
    $username = $_POST['username'] ?? null;
    $nik = $_POST['nik'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $birthday = $_POST['birthday'] ?? null;
    $address = $_POST['address'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $nama = $_POST['nama'] ?? null;

    try {
        // Query UPDATE data pengguna (Konversi ke MySQLi)
        $sql = "UPDATE Users SET 
                username = ?, 
                nik = ?, 
                phone = ?, 
                birthday = ?, 
                address = ?, 
                gender = ? 
                WHERE user_id = ?";
        
        $stmt = $mysqli->prepare($sql);
        // "ssssssi" = string, string, string, string, string, string, integer
        $stmt->bind_param("ssssssi",
            $username,
            $nik,
            $phone,
            $birthday,
            $address,
            $gender,
            $user_id
        );
        $stmt->execute();
        
        // Update session name (jika diperlukan)
        if ($nama) {
            $_SESSION['user_name'] = $nama;
        }

        $_SESSION['success_message'] = "Profil berhasil diperbarui!";
        header('Location: ../profile.php');
        exit;

    } catch (Exception $e) { // Tangkap 'Exception' umum
        $_SESSION['error_message'] = "Error database saat update: " . $e->getMessage();
        header('Location: ../profile.php');
        exit;
    }

} else {
    header('Location: ../profile.php');
    exit;
}
?>