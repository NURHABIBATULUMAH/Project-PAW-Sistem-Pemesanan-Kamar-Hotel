<?php

session_start();
include '../config/database.php';
include '../core/auth.php'; 

require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil data
    $booking_id = $_POST['booking_id'];
    $user_id_from_form = $_POST['user_id'];
    $rating = $_POST['rating'];
    $komentar = $_POST['komentar'];
    
    $user_id_from_session = $_SESSION['user_id'];

    // 2. Keamanan: Pastikan user_id form = user_id session
    if ($user_id_from_form != $user_id_from_session) {
        $_SESSION['error_message'] = "Kesalahan keamanan akun.";
        header('Location: ../profile.php');
        exit;
    }

    try {
        // 3. Keamanan: Cek sekali lagi apakah booking ini milik user & belum diulas
        $sql_check = "SELECT B.booking_id 
                      FROM Bookings B
                      LEFT JOIN Reviews RV ON B.booking_id = RV.booking_id
                      WHERE B.booking_id = ? AND B.user_id = ? AND RV.review_id IS NULL";
        
        $stmt_check = $mysqli->prepare($sql_check);
        $stmt_check->bind_param("ii", $booking_id, $user_id_from_session); // "ii" = integer, integer
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if (!$result_check->fetch_assoc()) { // Jika tidak ada hasil
            $_SESSION['error_message'] = "Booking ini tidak valid atau sudah diulas.";
            header('Location: ../profile.php');
            exit;
        }

        // 4. Semua aman, masukkan ke DB (Konversi ke MySQLi)
        $sql_insert = "INSERT INTO Reviews (booking_id, user_id, rating, komentar) VALUES (?, ?, ?, ?)";
        $stmt_insert = $mysqli->prepare($sql_insert);
        $stmt_insert->bind_param("iiis", $booking_id, $user_id_from_session, $rating, $komentar);
        $stmt_insert->execute();

        $_SESSION['success_message'] = "Ulasan Anda berhasil dikirim. Terima kasih!";
        header('Location: ../profile.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header('Location: ../profile.php');
        exit;
    }

} else {
    header('Location: ../profile.php');
    exit;
}
?>