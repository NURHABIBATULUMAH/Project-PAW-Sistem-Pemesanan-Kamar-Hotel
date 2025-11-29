<?php
// /actions/action_register.php

include '../config/database.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 4. Validasi sederhana
    if (empty($nama) || empty($email) || empty($password)) {
        session_start();
        $_SESSION['error_message'] = "Semua field harus diisi.";
        header('Location: ../register.php'); 
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        session_start();
        $_SESSION['error_message'] = "Format email tidak valid.";
        header('Location: ../register.php'); 
        exit;
    }

    // 5. HASH PASSWORD (Tidak berubah)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // 6. Buat kueri SQL (Konversi ke MySQLi)
        $sql = "INSERT INTO Users (nama, email, password, role) VALUES (?, ?, ?, 'customer')";
        $stmt = $mysqli->prepare($sql);
        
        $stmt->bind_param("sss", $nama, $email, $hashed_password);
        
        // 7. Eksekusi kueri
        $stmt->execute();

        // 8. Arahkan ke halaman login dengan pesan sukses
        session_start();
        $_SESSION['success_message'] = "Registrasi berhasil! Silakan login.";
        header('Location: ../login.php');
        exit;

    } catch (Exception $e) { 
        // Tangani jika email sudah terdaftar (error UNIQUE constraint)
        session_start();
        
        if ($e->getCode() == 1062) { 
            $_SESSION['error_message'] = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            $_SESSION['error_message'] = "Error saat registrasi: " . $e->getMessage();
        }
        header('Location: ../register.php');
        exit;
    }
} else {
    header('Location: ../index.php');
    exit;
}
?>