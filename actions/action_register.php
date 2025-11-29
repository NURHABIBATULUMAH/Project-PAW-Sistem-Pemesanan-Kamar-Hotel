<?php

include '../config/database.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

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

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO Users (nama, email, password, role) VALUES (?, ?, ?, 'customer')";
        $stmt = $mysqli->prepare($sql);
        
        $stmt->bind_param("sss", $nama, $email, $hashed_password);

        $stmt->execute();

        session_start();
        $_SESSION['success_message'] = "Registrasi berhasil! Silakan login.";
        header('Location: ../login.php');
        exit;

    } catch (Exception $e) { 
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