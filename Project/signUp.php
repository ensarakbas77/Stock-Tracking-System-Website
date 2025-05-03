<?php
// Oturum başlat
session_start();

// Form gönderildi mi kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Formdan gelen verileri al ve güvenli hale getir
    $username = isset($_POST['username']) ? trim(htmlspecialchars($_POST['username'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';
    $userRole = isset($_POST['userRole']) ? trim(htmlspecialchars($_POST['userRole'])) : '';
    
    // Verilerin boş olup olmadığını kontrol et
    if (empty($username) || empty($password) || empty($confirmPassword) || empty($userRole)) {
        header("Location: signUp.html?error=Tüm alanlar doldurulmalıdır!");
        exit();
    }
    
    // Şifrelerin eşleşip eşleşmediğini kontrol et
    if ($password !== $confirmPassword) {
        header("Location: signUp.html?error=Şifreler eşleşmiyor!");
        exit();
    }
    
    // Şifre güvenlik kontrolü
    if (strlen($password) < 8) {
        header("Location: signUp.html?error=Şifre en az 8 karakter uzunluğunda olmalıdır!");
        exit();
    }
    
    // Şifre karmaşıklık kontrolü (büyük harf, küçük harf ve sayı içermeli)
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        header("Location: signUp.html?error=Şifre en az bir büyük harf, bir küçük harf ve bir rakam içermelidir!");
        exit();
    }
    
    // Rol değerinin geçerli olup olmadığını kontrol et
    $validRoles = ['bakimci', 'stokcu', 'vardiya_amiri'];
    if (!in_array($userRole, $validRoles)) {
        header("Location: signUp.html?error=Geçersiz rol seçimi!");
        exit();
    }
    
    // Veritabanı bağlantı parametreleri
    $host = '';
    $dbname = 'stok_takip';
    $dbUsername = 'admin';
    $dbPassword = '';
    
    try {
        // PDO ile veritabanı bağlantısı kur
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbUsername, $dbPassword);
        // Hata modunu ayarla
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Kullanıcı adı veritabanında var mı kontrol et
        $checkStmt = $conn->prepare("SELECT username FROM users WHERE username = :username");
        $checkStmt->bindParam(':username', $username);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            header("Location: signUp.html?error=Bu kullanıcı adı zaten kullanılıyor!");
            exit();
        }
        
        // Kullanıcıyı veritabanına kaydet - şifre doğrudan kaydediliyor
        $insertStmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $insertStmt->bindParam(':username', $username);
        $insertStmt->bindParam(':password', $password); // Şifreyi doğrudan kaydediyoruz
        $insertStmt->bindParam(':role', $userRole);
        
        if ($insertStmt->execute()) {
            // Başarılı kayıt
            header("Location: index.html?success=Kayıt başarılı! Şimdi giriş yapabilirsiniz.");
            exit();
        } else {
            // Kayıt başarısız
            header("Location: signUp.html?error=Kayıt sırasında bir hata oluştu.");
            exit();
        }
    } catch(PDOException $e) {
        // Veritabanı hatası
        header("Location: signUp.html?error=Veritabanı hatası: " . $e->getMessage());
        exit();
    } finally {
        // Bağlantıyı kapat
        $conn = null;
    }
    
} else {
    // POST olmayan istekleri kayıt sayfasına yönlendir
    header("Location: signUp.html");
    exit();
}
?>
