<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $booking_id = $_POST['booking_id'];
    $user_id_from_form = $_POST['user_id'];
    $rating = $_POST['rating'];
    $komentar = $_POST['komentar'];
    
    $user_id_from_session = $_SESSION['user_id'];

    if ($user_id_from_form != $user_id_from_session) {
        $_SESSION['error_message'] = "Kesalahan keamanan akun.";
        header('Location: ../profile.php');
        exit;
    }

    try {
        
        $sql_check = "SELECT B.bookings_id 
                      FROM bookings B 
                      LEFT JOIN reviews RV ON B.booking_id = RV.booking_id
                      WHERE B.booking_id = ? AND B.user_id = ? AND RV.review_id IS NULL";
        
        $stmt_check = $mysqli->prepare($sql_check);
        $stmt_check->bind_param("ii", $booking_id, $user_id_from_session);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if (!$result_check->fetch_assoc()) {
            $_SESSION['error_message'] = "Booking tidak valid atau Anda sudah memberikan ulasan.";
            header('Location: ../profile.php');
            exit;
        }

        $sql_insert = "INSERT INTO reviews (booking_id, user_id, rating, komentar) VALUES (?, ?, ?, ?)";
        
        $stmt_insert = $mysqli->prepare($sql_insert);
        $stmt_insert->bind_param("iiis", $booking_id, $user_id_from_session, $rating, $komentar);
        
        if ($stmt_insert->execute()) {
            $_SESSION['success_message'] = "Ulasan berhasil dikirim. Terima kasih!";
        } else {
            throw new Exception("Gagal insert ke database.");
        }

        header('Location: ../profile.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Gagal menyimpan ulasan: " . $e->getMessage();
        header('Location: ../profile.php');
        exit;
    }

} else {
    header('Location: ../profile.php');
    exit;
}
?>