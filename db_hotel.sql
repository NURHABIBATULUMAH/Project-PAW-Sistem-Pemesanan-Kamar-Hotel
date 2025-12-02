-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 01 Des 2025 pada 13.07
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_hotel`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `booking_code` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `jumlah_kamar` int(11) NOT NULL DEFAULT 1,
  `detail_kamar` varchar(255) DEFAULT NULL,
  `tanggal_check_in` date NOT NULL,
  `tanggal_check_out` date NOT NULL,
  `total_bayar` decimal(10,2) NOT NULL,
  `status_booking` enum('Pending','Confirmed','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking_fasilitas`
--

CREATE TABLE `booking_fasilitas` (
  `booking_fasilitas_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `fasilitas_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `total_harga_fasilitas` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `fasilitas_tambahan`
--

CREATE TABLE `fasilitas_tambahan` (
  `fasilitas_id` int(11) NOT NULL,
  `nama_fasilitas` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `fasilitas_tambahan`
--

INSERT INTO `fasilitas_tambahan` (`fasilitas_id`, `nama_fasilitas`, `harga`, `deskripsi`) VALUES
(1, 'Breakfast (per orang)', 75000.00, 'Breakfast Untuk satu Orang'),
(2, 'Extra Room Service', 50000.00, 'Extra Room Jika room kurang luas'),
(4, 'Extra Bed (Single)', 150000.00, 'Kasur tambahan nyaman untuk 1 orang tamu ekstra (termasuk bantal & selimut).'),
(5, 'Airport Pickup (Jemputan)', 200000.00, 'Layanan penjemputan eksklusif dari bandara/stasiun langsung ke hotel.'),
(6, 'Romantic Room Setup', 350000.00, 'Dekorasi kamar spesial dengan kelopak mawar, coklat, dan angsa handuk untuk momen romantis.'),
(7, 'Late Check-out (s/d 15:00)', 100000.00, 'Perpanjangan waktu menginap hingga pukul 15:00 WIB (tergantung ketersediaan kamar).'),
(8, 'Welcome Fruit Basket', 85000.00, 'Keranjang berisi buah-buahan segar premium yang disajikan di kamar saat kedatangan.');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `booking_code` varchar(50) DEFAULT NULL,
  `metode_bayar` enum('BCA','Mandiri','BNI','BRI','OVO','Gopay') DEFAULT NULL,
  `jumlah_bayar` decimal(10,2) NOT NULL,
  `status_bayar` enum('Pending','Success','Failed') NOT NULL DEFAULT 'Pending',
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `tanggal_bayar` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `komentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_review` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `nomor_kamar` varchar(10) NOT NULL,
  `status` enum('Available','Unavailable','Maintenance') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_type_id`, `nomor_kamar`, `status`) VALUES
(1, 1, '101', 'Available'),
(2, 1, '102', 'Available'),
(3, 2, '201', 'Available'),
(4, 3, '301', 'Available'),
(5, 1, '103', 'Available'),
(6, 1, '104', 'Available'),
(7, 1, '105', 'Available'),
(8, 1, '106', 'Available'),
(9, 1, '107', 'Available'),
(10, 1, '108', 'Available'),
(11, 1, '109', 'Available'),
(12, 1, '110', 'Available'),
(13, 2, '202', 'Available'),
(14, 2, '203', 'Available'),
(15, 2, '204', 'Available'),
(16, 2, '205', 'Available'),
(17, 2, '206', 'Available'),
(18, 2, '207', 'Available'),
(19, 2, '208', 'Available'),
(20, 2, '209', 'Available'),
(21, 2, '210', 'Available'),
(22, 3, '302', 'Available'),
(23, 3, '303', 'Available'),
(24, 3, '304', 'Available'),
(25, 3, '305', 'Available'),
(26, 3, '306', 'Available'),
(27, 3, '307', 'Available'),
(28, 3, '308', 'Available'),
(29, 3, '309', 'Available'),
(30, 3, '310', 'Available');

-- --------------------------------------------------------

--
-- Struktur dari tabel `room_types`
--

CREATE TABLE `room_types` (
  `room_type_id` int(11) NOT NULL,
  `nama_tipe` varchar(100) NOT NULL,
  `harga_weekdays` decimal(10,2) NOT NULL,
  `harga_weekend` decimal(10,2) NOT NULL,
  `deskripsi_tipe` text DEFAULT NULL,
  `foto_utama` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `room_types`
--

INSERT INTO `room_types` (`room_type_id`, `nama_tipe`, `harga_weekdays`, `harga_weekend`, `deskripsi_tipe`, `foto_utama`) VALUES
(1, 'Standard Room', 350000.00, 400000.00, 'Kamar standar dengan fasilitas dasar, TV, AC, dan kamar mandi pribadi.', 'kamar 2 kasur.jpg'),
(2, 'Deluxe Room', 550000.00, 700000.00, 'Kamar yang lebih luas dengan pemandangan kota, sofa kecil, dan bathtub.', 'kamar solo.jpg'),
(3, 'Presidential Suite', 1200000.00, 1500000.00, 'Fasilitas termewah kami dengan ruang tamu terpisah, pemandangan terbaik, dan layanan premium.', 'kamar keluarga.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gender` enum('Pria','Wanita') DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `nama`, `username`, `nik`, `phone`, `birthday`, `address`, `gender`, `email`, `password`, `profile_photo`, `role`, `created_at`) VALUES
(6, 'adminfix', 'admin', '12345678', '123456789', '2025-11-20', 'bangkalan', 'Pria', 'adminfix@gmail.com', '$2y$10$D6z/mB1sKACuKoFBAbL8COnTL3f.RstbE/RHEETJCoyyhk6d1kqvO', NULL, 'admin', '2025-11-14 12:19:01'),
(10, 'cust1', 'Customer 1', '123456789', '123456789', '2006-12-09', 'Telang', 'Wanita', 'cust1@gmail.com', '$2y$10$ZKoQ3Kw.XrKpemYBdyIXkOu6fobSp1QFw9wfCtRCDdJJxQg9bOEw6', 'user_10_1764407380.png', 'customer', '2025-11-23 04:02:06');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_booking_room` (`room_id`);

--
-- Indeks untuk tabel `booking_fasilitas`
--
ALTER TABLE `booking_fasilitas`
  ADD PRIMARY KEY (`booking_fasilitas_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `fasilitas_id` (`fasilitas_id`);

--
-- Indeks untuk tabel `fasilitas_tambahan`
--
ALTER TABLE `fasilitas_tambahan`
  ADD PRIMARY KEY (`fasilitas_id`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `nomor_kamar` (`nomor_kamar`),
  ADD KEY `room_type_id` (`room_type_id`);

--
-- Indeks untuk tabel `room_types`
--
ALTER TABLE `room_types`
  ADD PRIMARY KEY (`room_type_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `booking_fasilitas`
--
ALTER TABLE `booking_fasilitas`
  MODIFY `booking_fasilitas_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `fasilitas_tambahan`
--
ALTER TABLE `fasilitas_tambahan`
  MODIFY `fasilitas_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `room_types`
--
ALTER TABLE `room_types`
  MODIFY `room_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_booking_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `booking_fasilitas`
--
ALTER TABLE `booking_fasilitas`
  ADD CONSTRAINT `booking_fasilitas_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`),
  ADD CONSTRAINT `booking_fasilitas_ibfk_2` FOREIGN KEY (`fasilitas_id`) REFERENCES `fasilitas_tambahan` (`fasilitas_id`);

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);

--
-- Ketidakleluasaan untuk tabel `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`room_type_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
