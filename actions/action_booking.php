<?php
// /actions/action_booking.php
// VERSI FINAL FIX: Menyimpan Room ID ke Database agar status terbaca sistem

session_start();
include '../config/database.php'; 
include '../core/auth.php';
include '../core/booking_logic.php'; 

require_login(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil Data
    $user_id = $_POST['user_id'] ?? null;
    $room_type_id = $_POST['room_type_id'] ?? null;
    $check_in = $_POST['check_in'] ?? null;
    $check_out = $_POST['check_out'] ?? null;
    $jumlah_kamar = (int) ($_POST['jumlah_kamar'] ?? 0);
    $fasilitas_input = $_POST['fasilitas'] ?? [];
    
    // [FIX] Tangkap string ID kamar dan pecah jadi array
    $pilihan_kamar_str = $_POST['pilihan_kamar_str'] ?? '';
    $detail_nomor_kamar_str = $_POST['detail_nomor_kamar'] ?? ''; // Untuk kolom detail_kamar
    
    if (empty($pilihan_kamar_str)) {
        $_SESSION['error_message'] = "Terjadi kesalahan: Nomor kamar tidak terpilih.";
        header('Location: ../rooms.php'); exit;
    }
    
    // Konversi "1,5,7" menjadi array [1, 5, 7]
    $pilihan_kamar_ids = explode(',', $pilihan_kamar_str);
    
    // Validasi Keamanan
    if ($user_id != $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Terjadi kesalahan keamanan.";
        header('Location: ../index.php'); exit;
    }

    try {
        // 2. Hitung Ulang Total Harga (Backend Validation)
        $harga_satu_kamar = calculate_total_price($mysqli, $room_type_id, $check_in, $check_out);
        $total_harga_semua_kamar = $harga_satu_kamar * $jumlah_kamar;
        
        // Hitung Fasilitas
        $total_harga_fasilitas = 0;
        $fasilitas_to_save = []; 

        if (!empty($fasilitas_input)) {
            $sql_fas = "SELECT fasilitas_id, harga FROM fasilitas_tambahan";
            $result_fas = $mysqli->query($sql_fas);
            $fasilitas_db = $result_fas->fetch_all(MYSQLI_ASSOC);
            $harga_fasilitas_asli = array_column($fasilitas_db, 'harga', 'fasilitas_id');

            foreach ($fasilitas_input as $fasilitas_id => $qty) {
                $qty = (int) $qty;
                if ($qty > 0 && isset($harga_fasilitas_asli[$fasilitas_id])) {
                    $subtotal = (float) $harga_fasilitas_asli[$fasilitas_id] * $qty;
                    $total_harga_fasilitas += $subtotal;
                    $fasilitas_to_save[] = ['id' => $fasilitas_id, 'qty' => $qty, 'total' => $subtotal];
                }
            }
        }
        
        $GRAND_TOTAL = $total_harga_semua_kamar + $total_harga_fasilitas;

        // 3. GENERATE BOOKING CODE (Satu kode untuk rombongan)
        $booking_code = 'TRX-' . date('ymd') . rand(100, 999);

        // 4. MULAI TRANSAKSI
        $mysqli->begin_transaction();

        // 5. INSERT DATA BOOKING (LOOPING per Kamar)
        // [PENTING] Kita insert 1 baris per kamar agar 'room_id' tersimpan
        // dan sistem bisa mendeteksi kamar itu 'Unavailable' di tanggal tsb.
        
        // Harga per baris booking (Harga 1 kamar + (Fasilitas / Jumlah Kamar))
        // Kita simpan Grand Total di baris pertama saja atau dibagi rata, 
        // tapi untuk Payment nanti pakai Booking Code, jadi aman.
        
        // Strategi: Simpan harga kamar di masing-masing row. Simpan harga fasilitas di table fasilitas.
        
        $booking_ids_created = [];
        
        // Ambil array nomor kamar (text) untuk disimpan di detail_kamar
        $nomor_kamar_array = explode(',', str_replace(' ', '', $detail_nomor_kamar_str)); // Bersihkan spasi

        $counter = 0;
        foreach ($pilihan_kamar_ids as $room_id_fisik) {
            $nomor_kamar_ini = $nomor_kamar_array[$counter] ?? '-';
            
            // Harga per booking row = Harga 1 kamar saja (Fasilitas disimpan terpisah)
            // Tapi total_bayar di tabel bookings biasanya total keseluruhan.
            // Agar tidak double counting saat sum, kita taruh Total Fasilitas di baris pertama saja,
            // atau bagi rata. Mari kita simpan Harga Kamar saja, nanti Payment yang catat total bayar.
            // TAPI: Struktur tabel bookings Anda punya 'total_bayar'. 
            // Kita bagi rata saja biar rapi.
            
            $share_fasilitas = $total_harga_fasilitas / count($pilihan_kamar_ids);
            $total_per_row = $harga_satu_kamar + $share_fasilitas;

            $sql_booking = "INSERT INTO bookings (booking_code, user_id, room_type_id, room_id, jumlah_kamar, detail_kamar, tanggal_check_in, tanggal_check_out, total_bayar, status_booking) 
                            VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?, 'Confirmed')";
            
            $stmt_booking = $mysqli->prepare($sql_booking);
            // "siiisssd"
            $stmt_booking->bind_param("siiisssd", $booking_code, $user_id, $room_type_id, $room_id_fisik, $nomor_kamar_ini, $check_in, $check_out, $total_per_row);
            $stmt_booking->execute();
            
            $booking_ids_created[] = $mysqli->insert_id; // Simpan ID baru
            $counter++;
        }

        // 6. INSERT PAYMENTS (Satu Payment untuk Satu Kode Booking)
        $sql_payment = "INSERT INTO payments (booking_code, jumlah_bayar, status_bayar) VALUES (?, ?, 'Pending')";
        $stmt_payment = $mysqli->prepare($sql_payment);
        $stmt_payment->bind_param("sd", $booking_code, $GRAND_TOTAL);
        $stmt_payment->execute();

        // 7. INSERT FASILITAS (Dikaitkan ke salah satu booking ID saja, atau semua. Kita kaitkan ke ID pertama)
        if (!empty($fasilitas_to_save) && !empty($booking_ids_created)) {
            $main_booking_id = $booking_ids_created[0]; // ID pertama
            
            $sql_fas_insert = "INSERT INTO booking_fasilitas (booking_id, fasilitas_id, jumlah, total_harga_fasilitas) VALUES (?, ?, ?, ?)";
            $stmt_fas_insert = $mysqli->prepare($sql_fas_insert);
            
            foreach ($fasilitas_to_save as $fas) {
                $stmt_fas_insert->bind_param("iiid", $main_booking_id, $fas['id'], $fas['qty'], $fas['total']);
                $stmt_fas_insert->execute();
            }
        }
        
        // 8. UPDATE STATUS KAMAR FISIK (Agar 'Unavailable' di dashboard admin)
        // Sebenarnya sistem filter tanggal sudah otomatis, tapi kita update status fisik juga
        $placeholders = implode(',', array_fill(0, count($pilihan_kamar_ids), '?'));
        $types = str_repeat('i', count($pilihan_kamar_ids)); 
        
        $sql_update_fisik = "UPDATE rooms SET status = 'Unavailable' WHERE room_id IN ($placeholders)";
        $stmt_fisik = $mysqli->prepare($sql_update_fisik);
        $stmt_fisik->bind_param($types, ...$pilihan_kamar_ids);
        $stmt_fisik->execute();
        
        // COMMIT
        $mysqli->commit();

        $_SESSION['success_message'] = "Booking Berhasil! Silakan segera upload bukti pembayaran.";
        header('Location: ../booking_history.php'); 
        exit;

    } catch (Exception $e) {
        if ($mysqli->errno) $mysqli->rollback();
        $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
        header('Location: ../booking.php'); 
        exit;
    }
} else {
    header('Location: ../index.php');
    exit;
}
?>