<?php

session_start();
include '../config/database.php'; 
include '../core/auth.php';
include '../core/booking_logic.php'; 

require_login(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_POST['user_id'] ?? null;
    $room_type_id = $_POST['room_type_id'] ?? null;
    $check_in = $_POST['check_in'] ?? null;
    $check_out = $_POST['check_out'] ?? null;
    $jumlah_kamar = (int) ($_POST['jumlah_kamar'] ?? 0);
    $total_harga_kamar_asli = (float) ($_POST['total_harga_kamar'] ?? 0); 
    $fasilitas_input = $_POST['fasilitas'] ?? [];
    $selected_ids_str = $_POST['selected_room_ids'] ?? ''; 

    $safe_ids = [];
    if (!empty($selected_ids_str)) {
        $ids_array = explode(',', $selected_ids_str);
        foreach($ids_array as $id) {
            $val = intval($id);
            if($val > 0) $safe_ids[] = $val;
        }
    }

    if ($user_id != $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Terjadi kesalahan keamanan.";
        header('Location: ../index.php'); exit;
    }
    if ($jumlah_kamar <= 0 || count($safe_ids) != $jumlah_kamar) {
         $_SESSION['error_message'] = "Jumlah kamar yang dipilih tidak sesuai.";
         header('Location: ../rooms.php'); exit;
    }

    try {

        $total_harga_fasilitas_global = 0;
        $fasilitas_data_global = []; 

        if (!empty($fasilitas_input)) {
            $sql_fas = "SELECT fasilitas_id, harga FROM fasilitas_tambahan";
            $result_fas = $mysqli->query($sql_fas);
            $fasilitas_db = $result_fas->fetch_all(MYSQLI_ASSOC);
            $harga_fasilitas_asli = array_column($fasilitas_db, 'harga', 'fasilitas_id');

            foreach ($fasilitas_input as $fasilitas_id => $qty) {
                $qty = (int) $qty;
                if ($qty > 0 && isset($harga_fasilitas_asli[$fasilitas_id])) {
                    $subtotal = (float) $harga_fasilitas_asli[$fasilitas_id] * $qty;
                    $total_harga_fasilitas_global += $subtotal;
                    $fasilitas_data_global[] = ['id' => $fasilitas_id, 'qty' => $qty, 'total' => $subtotal];
                }
            }
        }

        $GRAND_TOTAL = $total_harga_kamar_asli + $total_harga_fasilitas_global;
        $kode_transaksi = "TRX-" . date('ymd') . rand(100, 999);

        $harga_per_kamar = $GRAND_TOTAL / $jumlah_kamar; 

        $mysqli->begin_transaction();

        foreach ($safe_ids as $current_room_id) {

            $nomor_kamar_str = '-';
            $q_cek = $mysqli->query("SELECT nomor_kamar FROM rooms WHERE room_id = $current_room_id");
            if ($r = $q_cek->fetch_assoc()) {
                $nomor_kamar_str = $r['nomor_kamar'];
            }

            $sql_booking = "INSERT INTO bookings (booking_code, user_id, room_type_id, room_id, jumlah_kamar, detail_kamar, tanggal_check_in, tanggal_check_out, total_bayar, status_booking) 
                            VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?, 'Confirmed')";
            
            $stmt_booking = $mysqli->prepare($sql_booking);
            $stmt_booking->bind_param("siiisssd", $kode_transaksi, $user_id, $room_type_id, $current_room_id, $nomor_kamar_str, $check_in, $check_out, $harga_per_kamar);
            $stmt_booking->execute();
            $last_id = $mysqli->insert_id;

           if (!empty($fasilitas_data_global)) {
                $sql_fas_insert = "INSERT INTO booking_fasilitas (booking_id, fasilitas_id, jumlah, total_harga_fasilitas) VALUES (?, ?, ?, ?)";
                $stmt_fas_insert = $mysqli->prepare($sql_fas_insert);
                foreach ($fasilitas_data_global as $fas) {
                    $qty_split = max(1, floor($fas['qty'] / $jumlah_kamar)); 
                    $total_split = $fas['total'] / $jumlah_kamar;
                    $stmt_fas_insert->bind_param("iiid", $last_id, $fas['id'], $qty_split, $total_split);
                    $stmt_fas_insert->execute();
                }
            }
        }

        $sql_payment = "INSERT INTO payments (booking_code, jumlah_bayar, status_bayar) VALUES (?, ?, 'Pending')";
        $stmt_payment = $mysqli->prepare($sql_payment);
        $stmt_payment->bind_param("sd", $kode_transaksi, $GRAND_TOTAL);
        $stmt_payment->execute();

        $mysqli->commit();

        $_SESSION['success_message'] = "Booking Berhasil! Kode Transaksi: " . $kode_transaksi;
        header('Location: ../booking_history.php'); 
        exit;

    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error_message'] = "Gagal: " . $e->getMessage();
        header('Location: ../rooms.php'); 
        exit;
    }
} else {
    header('Location: ../index.php');
    exit;
}

?>
