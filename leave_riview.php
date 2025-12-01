<?php
include 'includes/header.php';

require_login();
$user_id = $_SESSION['user_id'];
$booking_id = $_GET['booking_id'] ?? 0;


try {

    $sql_check = "SELECT 
                    B.booking_id, 
                    RT.nama_tipe, 
                    RV.review_id
                  FROM bookings B
                  JOIN room_types RT ON B.room_type_id = RT.room_type_id
                  LEFT JOIN reviews RV ON B.booking_id = RV.booking_id
                  WHERE B.booking_id = ? AND B.user_id = ?";
    
    $stmt_check = $mysqli->prepare($sql_check);
    $stmt_check->bind_param("ii", $booking_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $booking_data = $result_check->fetch_assoc();

    $error_redirect = false;

    if (!$booking_data) {
        $_SESSION['error_message'] = "Booking tidak valid.";
        $error_redirect = true;
    } elseif (!empty($booking_data['review_id'])) {
        $_SESSION['error_message'] = "Anda sudah pernah memberi ulasan untuk booking ini.";
        $error_redirect = true;
    }

    if ($error_redirect) {
        header('Location: booking_history.php');
        exit;
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: booking_history.php');
    exit;
}
?>

<div class="form-container">
    <h2>Tulis Ulasan Anda</h2>
    <p>Untuk pesanan kamar: <strong><?php echo htmlspecialchars($booking_data['nama_tipe']); ?></strong></p>

    <form action="actions/action_submit_review.php" method="POST">
        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

        <div class="form-group">
            <label>Rating (Bintang):</label>
            <div class="rating">
                <input type="radio" id="star5" name="rating" value="5" required>
                <label for="star5">★</label>

                <input type="radio" id="star4" name="rating" value="4">
                <label for="star4">★</label>

                <input type="radio" id="star3" name="rating" value="3">
                <label for="star3">★</label>

                <input type="radio" id="star2" name="rating" value="2">
                <label for="star2">★</label>

                <input type="radio" id="star1" name="rating" value="1">
                <label for="star1">★</label>
            </div>
        </div>

        <div class="form-group">
            <label for="komentar">Komentar Anda:</label>
            <textarea id="komentar" name="komentar" rows="5" placeholder="Ceritakan pengalaman menginap Anda..."></textarea>
        </div>

        <div class="form-group">
            <button type="submit" class="btn-primary full-width">Kirim Ulasan</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>