# Project PAW – Sistem Pemesanan Kamar Hotel

Aplikasi berbasis web untuk melakukan pemesanan kamar hotel secara online. Sistem ini dibangun menggunakan **PHP Native**, **MySQL**, dan **HTML/CSS**, serta menyediakan fitur pemesanan, upload bukti pembayaran, manajemen kamar, pengelolaan user, dan dashboard admin.

## Teknologi yang Digunakan
- **PHP Native**
- **MySQL**
- **HTML, CSS**
- **XAMPP / Laragon**

## Struktur Folder

```bash
/sistem_hotel/
│
├── index.php
├── login.php
├── register.php  
├── logout.php
├── rooms.php
├── room_detail.php
├── booking.php
├── profile.php
├── booking_history.php
├── facilities.php
├── leave_review.php
│
├── admin/
│   ├── index.php
│   ├── manage_rooms.php
│   ├── manage_room_types.php
│   ├── manage_bookings.php
│   ├── manage_users.php
│
├── config/
│   ├── database.php
│
├── core/
│   ├── auth.php
│   ├── booking_logic.php
│
├── actions/
│   ├── action_login.php
│   ├── action_register.php
│   ├── action_booking.php
│   ├── action_submit_review.php
│   ├── action_update_profile.php
│   ├── action_upload_bukti.php
│   ├── action_update_profile_photo.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── admin_header.php
│   ├── admin_footer.php
│   ├── admin_sidebar.php
│
└── assets/
    ├── css/
    │   ├── style.css
    │   ├── admin_style.css
    ├── images/
    │   ├── profile/
    │   ├── rooms/
    └── uploads/
        ├── payment.png
