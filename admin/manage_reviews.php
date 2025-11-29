<?php
include '../includes/admin_header.php'; // Ini sudah memuat $mysqli

$message = '';
$message_type = '';

try {
    // === LOGIKA HAPUS ULASAN (Konversi ke MySQLi) ===
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $review_id_to_delete = $_GET['id'];
        
        $sql_delete = "DELETE FROM Reviews WHERE review_id = ?";
        $stmt_delete = $mysqli->prepare($sql_delete);
        $stmt_delete->bind_param("i", $review_id_to_delete); // "i" untuk integer
        $stmt_delete->execute();
        
        $message = "Ulasan berhasil dihapus.";
        $message_type = 'success';
    }

    // === Ambil semua data ulasan (Konversi ke MySQLi) ===
    $sql_select = "SELECT 
                        R.review_id, R.rating, R.komentar, R.tanggal_review,
                        U.nama AS nama_pelanggan,
                        RT.nama_tipe
                   FROM Reviews R
                   JOIN Users U ON R.user_id = U.user_id
                   JOIN Bookings B ON R.booking_id = B.booking_id
                   /* Perubahan JOIN: Dari Rooms ke Bookings -> Room_Types */
                   JOIN Room_Types RT ON B.room_type_id = RT.room_type_id
                   ORDER BY R.tanggal_review DESC"; 
                   
    $result_select = $mysqli->query($sql_select);
    $all_reviews = $result_select->fetch_all(MYSQLI_ASSOC); // Menggantikan fetchAll() PDO

} catch (Exception $e) { // Tangkap 'Exception' umum
    $message = "Error database: " . $e->getMessage();
    $message_type = 'error';
}

// === TAMPILAN HTML (TIDAK BERUBAH) ===
?>

<div class="content-header">
    <h1>Kelola Ulasan Pelanggan</h1>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo ($message_type == 'success') ? 'alert-success' : 'alert-error'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="admin-card">
    <h3>Daftar Seluruh Ulasan</h3>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Tipe Kamar</th>
                    <th>Rating (Bintang)</th>
                    <th>Komentar</th>
                    <th>Tanggal Ulas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($all_reviews)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Belum ada ulasan yang masuk.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($all_reviews as $review): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($review['nama_pelanggan']); ?></td>
                        <td><?php echo htmlspecialchars($review['nama_tipe']); ?></td>
                        
                        <td>
                            <span class="rating-stars">
                                <?php echo str_repeat('★', $review['rating']); // Ulangi bintang sebanyak rating ?>
                            </span>
                        </td>
                        
                        <td style="min-width: 300px;"><?php echo nl2br(htmlspecialchars($review['komentar'])); ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($review['tanggal_review'])); ?></td>
                        
                        <td class="action-links">
                            <a href="manage_reviews.php?action=delete&id=<?php echo $review['review_id']; ?>" 
                               class="delete-link" 
                               onclick="return confirm('Yakin ingin menghapus ulasan ini?');">
                               Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include '../includes/admin_footer.php'; 
?>