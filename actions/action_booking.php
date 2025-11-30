        <?php
        // /actions/action_booking.php

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
            
            // [PENTING] Tangkap array nomor kamar yang dipilih
            $pilihan_kamar = $_POST['pilihan_kamar'] ?? [];

            // 2. Validasi
            if ($user_id != $_SESSION['user_id']) {
                $_SESSION['error_message'] = "Terjadi kesalahan keamanan.";
                header('Location: ../index.php'); exit;
            }
            if ($jumlah_kamar <= 0 || empty($room_type_id)) {
                $_SESSION['error_message'] = "Data pesanan tidak valid.";
                header('Location: ../rooms.php'); exit;
            }

            try {
                // 3. Cek Ketersediaan (Stok Angka)
                if (!check_room_stock($mysqli, $room_type_id, $check_in, $check_out, $jumlah_kamar)) {
                    $_SESSION['error_message'] = "Maaf, stok kamar tidak mencukupi.";
                    header('Location: ../room_detail.php?id=' . $room_type_id); exit;
                }

                // 4. Hitung Total
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
                
                $harga_satu_kamar = calculate_total_price($mysqli, $room_type_id, $check_in, $check_out);
                $total_harga_kamar = $harga_satu_kamar * $jumlah_kamar;
                $GRAND_TOTAL = $total_harga_kamar + $total_harga_fasilitas;


                // 5. MULAI TRANSAKSI
                $mysqli->begin_transaction();

                // 6. Insert Bookings (Status CONFIRMED)
                $sql_booking = "INSERT INTO bookings (user_id, room_type_id, jumlah_kamar, tanggal_check_in, tanggal_check_out, total_bayar, status_booking) 
                                VALUES (?, ?, ?, ?, ?, ?, 'Confirmed')";
                $stmt_booking = $mysqli->prepare($sql_booking);
                $stmt_booking->bind_param("iiisss", $user_id, $room_type_id, $jumlah_kamar, $check_in, $check_out, $GRAND_TOTAL);
                $stmt_booking->execute();
                $new_booking_id = $mysqli->insert_id;

                // 7. Insert Payments
                $sql_payment = "INSERT INTO payments (booking_id, jumlah_bayar, status_bayar) VALUES (?, ?, 'Pending')";
                $stmt_payment = $mysqli->prepare($sql_payment);
                $stmt_payment->bind_param("id", $new_booking_id, $GRAND_TOTAL);
                $stmt_payment->execute();

                // 8. Insert Fasilitas
                if (!empty($fasilitas_to_save)) {
                    $sql_fas_insert = "INSERT INTO booking_fasilitas (booking_id, fasilitas_id, jumlah, total_harga_fasilitas) VALUES (?, ?, ?, ?)";
                    $stmt_fas_insert = $mysqli->prepare($sql_fas_insert);
                    foreach ($fasilitas_to_save as $fas) {
                        $stmt_fas_insert->bind_param("iiid", $new_booking_id, $fas['id'], $fas['qty'], $fas['total']);
                        $stmt_fas_insert->execute();
                    }
                }
                
                // ============================================================
                // [INTI FIX] UPDATE STATUS KAMAR FISIK
                // ============================================================
                
                if (!empty($pilihan_kamar)) {
                    // SKENARIO 1: User MEMILIH kamar spesifik (Data diterima)
                    // Kita update hanya nomor kamar yang ada di dalam array $pilihan_kamar
                    
                    // Buat placeholder dinamis (?,?,?)
                    $placeholders = implode(',', array_fill(0, count($pilihan_kamar), '?'));
                    $types = str_repeat('s', count($pilihan_kamar)); // string

                    $sql_update_spesifik = "UPDATE rooms 
                                            SET status = 'Unavailable' 
                                            WHERE nomor_kamar IN ($placeholders) 
                                            AND room_type_id = ?";
                    
                    $stmt_fisik = $mysqli->prepare($sql_update_spesifik);
                    
                    // Gabungkan nomor kamar dan room_type_id
                    $params = array_merge($pilihan_kamar, [$room_type_id]);
                    
                    // Bind parameter dinamis
                    $stmt_fisik->bind_param($types . 'i', ...$params); 
                    $stmt_fisik->execute();

                } else {
                    // SKENARIO 2: FALLBACK (Jika user tidak milih / data hilang)
                    // Pilih acak (biasanya urutan teratas 101, 102...)
                    $sql_update_acak = "UPDATE rooms 
                                        SET status = 'Unavailable' 
                                        WHERE room_type_id = ? AND status = 'Available' 
                                        LIMIT ?";
                    $stmt_fisik = $mysqli->prepare($sql_update_acak);
                    $stmt_fisik->bind_param("ii", $room_type_id, $jumlah_kamar);
                    $stmt_fisik->execute();
                }
                
                // ============================================================

                $mysqli->commit();

                $_SESSION['success_message'] = "Booking Berhasil! Silakan segera upload bukti pembayaran.";
                header('Location: ../profile.php'); 
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