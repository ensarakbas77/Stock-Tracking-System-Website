<?php
$host = '';
$dbname = 'stok_takip';
$username = 'admin';
$password = '';

try {
    // PDO ile bağlantı oluştur
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Bağlantı hatası: " . $e->getMessage());
}
?>
