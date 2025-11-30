<?php

define('BASE_URL', 'http://localhost/project-paw-sistem-pemesanan-kamar-hotel/'); 

$host = 'localhost'; 
$db_name = 'db_hotel';
$username = 'root'; 
$password = ''; 

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli($host, $username, $password, $db_name);
    $mysqli->set_charset("utf8mb4");
} catch(Exception $e) {
    die("Koneksi Gagal: " . $e->getMessage());
}
?>