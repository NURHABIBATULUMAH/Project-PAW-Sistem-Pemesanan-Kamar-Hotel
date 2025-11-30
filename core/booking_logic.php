<?php
// /core/booking_logic.php
// VERSI: SUPPORT PEMILIHAN NOMOR KAMAR SPESIFIK

/**
 * FUNGSI BARU (PENTING UNTUK GRID VIEW):
 * Mengambil daftar kamar spesifik (ID & Nomor) yang tersedia
 * pada rentang tanggal tertentu.
 *
 * @param mysqli $mysqli
 * @param int $room_type_id
 * @param string $check_in
 * @param string $check_out
 * @return array Daftar kamar yang tersedia (associative array)
 */
function get_available_specific_rooms($mysqli, $room_type_id, $check_in, $check_out) {
    $available_rooms = [];

    // QUERY FIX: Cek langsung ke tabel bookings kolom room_id
    // Logika: Ambil kamar yang room_id-nya TIDAK ADA di daftar booking pada rentang tanggal tersebut
    $sql = "
        SELECT r.room_id, r.nomor_kamar 
        FROM rooms r
        WHERE r.room_type_id = ? 
        AND r.status = 'Available' 
        AND r.room_id NOT IN (
            SELECT b.room_id 
            FROM bookings b
            WHERE b.status_booking IN ('Confirmed', 'Pending', 'Paid') 
            AND b.room_id IS NOT NULL 
            AND (
                (b.tanggal_check_in < ? AND b.tanggal_check_out > ?)
            )
        )
        ORDER BY r.nomor_kamar ASC
    ";

    if ($stmt = $mysqli->prepare($sql)) {
        // Urutan binding: room_type_id, check_out, check_in
        $stmt->bind_param("iss", $room_type_id, $check_out, $check_in);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $available_rooms[] = $row;
        }
        $stmt->close();
    }

    return $available_rooms;
}
/**
 * FUNGSI LAMA (Tetap disimpan untuk validasi cepat/fallback):
 * Mengecek ketersediaan berdasarkan jumlah stok global.
 */
function check_room_stock($mysqli, $room_type_id, $start_date, $end_date, $quantity_needed) {
    // 1. Ambil kamar spesifik yang tersedia menggunakan fungsi di atas
    $available_rooms = get_available_specific_rooms($mysqli, $room_type_id, $start_date, $end_date);
    
    // 2. Hitung jumlahnya
    $total_available = count($available_rooms);

    // 3. Bandingkan dengan kebutuhan
    return ($total_available >= $quantity_needed);
}

/**
 * FUNGSI KALKULASI HARGA
 */
function calculate_total_price($mysqli, $room_type_id, $start_date, $end_date) {
    // Pastikan nama tabel konsisten (Room_Types atau room_types)
    $sql_price = "SELECT harga_weekdays, harga_weekend FROM room_types WHERE room_type_id = ?";
    $stmt_price = $mysqli->prepare($sql_price);
    $stmt_price->bind_param("i", $room_type_id);
    $stmt_price->execute();
    $result_price = $stmt_price->get_result();
    $harga = $result_price->fetch_assoc(); 
    
    if (!$harga) {
        throw new Exception("Harga untuk tipe kamar $room_type_id tidak ditemukan.");
    }

    $harga_weekdays = (float) $harga['harga_weekdays'];
    $harga_weekend = (float) $harga['harga_weekend'];
    $total_price = 0;
    
    $current_date = new DateTime($start_date);
    $end_date_obj = new DateTime($end_date);
    
    // Loop per hari untuk cek weekend/weekday
    while ($current_date < $end_date_obj) {
        $day_of_week = (int) $current_date->format('w');
        
        // 0 = Minggu, 6 = Sabtu (Anggap Weekend)
        if ($day_of_week == 0 || $day_of_week == 6) {
            $total_price += $harga_weekend;
        } else {
            $total_price += $harga_weekdays;
        }
        $current_date->modify('+1 day');
    }

    return $total_price;
}

/**
 * FUNGSI VALIDASI TANGGAL
 */
function validate_dates($start_date, $end_date) {
    $today = date('Y-m-d');
    
    if ($start_date < $today) {
        return "Tanggal check-in tidak boleh di masa lalu.";
    }
    if ($end_date <= $start_date) {
        return "Tanggal check-out harus setelah tanggal check-in.";
    }
    return true; 
}
?>