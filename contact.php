<?php
// contact.php
// VERSI: INFORMASI & KONTAK SAJA (TANPA DB)

include 'config/database.php';
include 'core/auth.php'; 
include 'includes/header.php';
?>

<style>
    .contact-header {
        /* Gambar Background Header - Pastikan filenya ada atau ganti url gambar */
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/images/hotel_lobby.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
        padding: 80px 20px;
        margin-bottom: 40px;
    }
    .contact-container {
        max-width: 1100px;
        margin: 0 auto 50px auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1fr 1fr; /* Bagi 2 Kolom */
        gap: 50px;
    }
    .section-title {
        color: #d4af37; /* Warna Emas */
        font-size: 24px;
        margin-bottom: 20px;
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
        display: inline-block;
    }
    .contact-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 25px;
    }
    .icon-box {
        background: #d4af37;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 18px;
        flex-shrink: 0;
    }
    .text-box h4 {
        margin: 0 0 5px 0;
        font-size: 18px;
    }
    .text-box p {
        margin: 0;
        color: #555;
        line-height: 1.6;
    }
    .text-box a {
        color: #d4af37;
        text-decoration: none;
        font-weight: bold;
    }
    .text-box a:hover { text-decoration: underline; }

    /* Kotak Tentang Kami di Kanan */
    .about-box {
        background: #f9f9f9;
        padding: 30px;
        border-radius: 8px;
        border-left: 5px solid #d4af37;
    }

    /* Responsif HP */
    @media (max-width: 768px) {
        .contact-container { grid-template-columns: 1fr; }
    }
</style>

<div class="contact-header">
    <h1 style="font-size: 36px;">Hubungi Kami</h1>
    <p style="font-size: 16px;">Kami siap melayani kebutuhan menginap Anda 24 Jam.</p>
</div>

<div class="contact-container">
    
    <div class="left-col">
        <h3 class="section-title">Kontak Resmi</h3>
        
        <div class="contact-item">
            <div class="icon-box">📍</div>
            <div class="text-box">
                <h4>Alamat Hotel</h4>
                <p>Skyline Hotel & Resort<br>
                Jl. Jendral Sudirman Kav. 52-53<br>
                Jakarta Selatan, Indonesia 12190</p>
            </div>
        </div>

        <div class="contact-item">
            <div class="icon-box">✉️</div>
            <div class="text-box">
                <h4>Email Kami</h4>
                <p>
                    Reservasi: <a href="mailto:reservation@skylinehotel.com">reservation@skylinehotel.com</a><br>
                    General Info: <a href="mailto:info@skylinehotel.com">info@skylinehotel.com</a>
                </p>
            </div>
        </div>

        <div class="contact-item">
            <div class="icon-box">📞</div>
            <div class="text-box">
                <h4>Telepon & WhatsApp</h4>
                <p>Telp: (021) 555-8888 (Hunting)<br>
                WhatsApp: <a href="https://wa.me/6281234567890" target="_blank">+62 812-3456-7890</a></p>
            </div>
        </div>
        
        <div style="margin-top: 30px; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
             <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.303562629596!2d106.8059833!3d-6.2236356!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f1ec2422b0b3%3A0x394f97e26388353e!2sJenderal%20Sudirman!5e0!3m2!1sid!2sid!4v1680000000000!5m2!1sid!2sid" 
                width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy">
            </iframe>
        </div>
    </div>

    <!--
    <div class="right-col">
        <h3 class="section-title">Tentang Skyline Hotel</h3>
        
        <div class="about-box">
            <p style="margin-bottom: 15px;">
                <strong>Selamat Datang di Skyline Hotel.</strong>
            </p>
            <p style="margin-bottom: 15px; color: #444;">
                Terletak di jantung kota Jakarta yang dinamis, Skyline Hotel menawarkan pengalaman menginap bintang lima dengan perpaduan kemewahan modern dan keramahtamahan khas Indonesia.
            </p>
            <p style="margin-bottom: 15px; color: #444;">
                Kami memiliki 200+ kamar eksklusif dengan pemandangan langit kota (city view), kolam renang infinity, spa kelas dunia, dan restoran yang menyajikan kuliner internasional.
            </p>
            <p style="color: #444;">
                Lokasi kami sangat strategis, hanya 5 menit dari pusat perbelanjaan mewah dan distrik bisnis SCBD, menjadikan Skyline pilihan tepat untuk pelancong bisnis maupun liburan keluarga.
            </p>
        </div> -->

        <br>

        <h3 class="section-title">Jam Operasional</h3>
        <ul style="list-style: none; padding: 0; line-height: 2;">
            <li><strong>Check-In:</strong> Mulai pukul 14:00 WIB</li>
            <li><strong>Check-Out:</strong> Maksimal pukul 12:00 WIB</li>
            <li><strong>Layanan Resepsionis:</strong> 24 Jam</li>
            <li><strong>Room Service:</strong> 24 Jam</li>
        </ul>
    </div>

</div>

<?php
include 'includes/footer.php';
?>