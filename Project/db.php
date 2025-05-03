<?php
// Veritabanı bağlantısı için gerekli bilgileri tanımlıyoruz
$host = "localhost";  // AWS RDS'de kullanacaksan, burada RDS endpoint'i olacak
$dbname = "stok_takip";  // Veritabanı adını buraya yaz
$username = "";  // Veritabanı kullanıcı adını buraya yaz
$password = "";  // Veritabanı şifreni buraya yaz

try {
    // PDO ile veritabanı bağlantısını oluşturuyoruz
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Hata ayıklama modunu aktif ediyoruz
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Veritabanına başarıyla bağlanıldı!";
} catch (PDOException $e) {
    // Eğer bağlantı hatası oluşursa buradaki mesajı yazdıracağız
    echo "Bağlantı hatası: " . $e->getMessage();
}
?>
