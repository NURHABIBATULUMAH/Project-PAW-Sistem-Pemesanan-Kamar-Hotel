<?php
// /actions/action_login.php
// VERSI KONVERSI KE MySQLi

session_start();
include '../config/database.php'; // Sekarang $mysqli

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi dasar
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Email dan password harus diisi.";
        header('Location: ../login.php');
        exit;
    }

    try {
        // 1. Cari user berdasarkan email (Konversi ke MySQLi)
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $email); // "s" untuk string
        $stmt->execute();
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); // Menggantikan fetch() PDO

        // 2. Cek jika user ada DAN password cocok
        if ($user && password_verify($password, $user['password'])) {
            
            // 3. Password cocok! Simpan data user di SESSION
            session_regenerate_id(true); 
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];

            // 4. Arahkan berdasarkan role
            if ($user['role'] == 'admin') {
                header('Location: ../admin/index.php');
            } else {
                header('Location: ../profile.php');
            }
            exit;

        } else {
            // 5. Jika email/password salah
            $_SESSION['error_message'] = "Email atau password salah.";
            header('Location: ../login.php');
            exit;
        }

    } catch (Exception $e) { // Tangkap 'Exception' umum
        $_SESSION['error_message'] = "Error saat login: " . $e->getMessage();
        header('Location: ../login.php');
        exit;
    }
} else {
    header('Location: ../index.php');
    exit;
}
?>