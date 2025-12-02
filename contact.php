<?php

include 'config/database.php';
include 'core/auth.php';
include 'includes/header.php';
?>

<style>
    .contact-header {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/hotel_lobby.jpg');
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
        grid-template-columns: 1fr 1fr;
        gap: 50px;
    }

    .section-title {
        color: #d4af37;
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

    .text-box a:hover {
        text-decoration: underline;
    }

    .about-box {
        background: #f9f9f9;
        padding: 30px;
        border-radius: 8px;
        border-left: 5px solid #d4af37;
    }

    @media (max-width: 768px) {
        .contact-container {
            grid-template-columns: 1fr;
        }
    }

    .map-responsive {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .map-responsive iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
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
                    Jl. Raya Telang, Perumahan Telang Inda<br>
                    Telang, Kec. Kamal, Kabupaten Bangkalan<br>
                    Jawa Timur 69162</p>
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
        <div class="map-responsive">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4105.800509531812!2d112.72213390728469!3d-7.128339925953903!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd803dd886bbff5%3A0x9777ca139b28195d!2sUniversitas%20Trunojoyo%20Madura!5e0!3m2!1sid!2sid!4v1764358478138!5m2!1sid!2sid"
                loading="lazy"
                allowfullscreen=""
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

    </div>

    <br>

    <h3 class="section-title">Jam Operasional</h3><br>
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