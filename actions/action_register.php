<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include '../config/database.php'; // Pastikan $mysqli ada di sini

session_start(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi input
    if (empty($nama) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Semua field harus diisi.";
        header('Location: ../register.php'); 
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Format email tidak valid.";
        header('Location: ../register.php'); 
        exit;
    }

    // Hash Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {    
        $sql = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'customer')";
        
        $stmt = $mysqli->prepare($sql);

        // Cek apakah prepare berhasil
        if (!$stmt) {
            throw new Exception("Gagal mempersiapkan query: " . $mysqli->error);
        }
        
        // Binding parameter
        $stmt->bind_param("sss", $nama, $email, $hashed_password);
        
        // Eksekusi
        $stmt->execute();

        // Jika berhasil, redirect ke login
        $_SESSION['success_message'] = "Registrasi berhasil! Silakan login.";
        header('Location: ../login.php');
        exit;

    } catch (mysqli_sql_exception $e) {
        // Menangkap Error spesifik MySQLi
        if ($e->getCode() == 1062) { // Duplicate entry (Email sudah ada)
            $_SESSION['error_message'] = "Email tersebut sudah terdaftar.";
        } else {
            // Tampilkan error teknis untuk debugging (bisa dihapus nanti saat production)
            $_SESSION['error_message'] = "Database Error: " . $e->getMessage();
        }
        header('Location: ../register.php');
        exit;
    } catch (Exception $e) {
        // Menangkap Error umum lainnya
        $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
        header('Location: ../register.php');
        exit;
    }
} else {
    header('Location: ../index.php');
    exit;
}
?>