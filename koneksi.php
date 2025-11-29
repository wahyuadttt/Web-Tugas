<?php
$servername = "sq***.infinityfree.com"; // Host database
$username = "Username Database";              // Username database (biasanya formatnya seperti ini di InfinityFree)
$password = "Password Database";                  // Password database kamu
$dbname = "user_nama database";   // Nama database (biasanya ada prefix username-nya)

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal euy: " . $conn->connect_error);
}

// Set charset ke UTF-8
$conn->set_charset("utf8mb4");
?>
