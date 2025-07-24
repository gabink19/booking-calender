<?php
// db-mysql.php
// Koneksi MySQL reusable
function getMysqlConnection() {
    $host = 'localhost';
    $db   = 'bookingdb';
    $user = 'root';
    $pass = '';
    $port = '3306';
    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) {
        return null;
    }
    return $conn;
}
