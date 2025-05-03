<?php
session_start();

// Veritabanı bağlantısı
$host = '';
$dbname = 'stok_takip';
$username = 'admin';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

// Bağlantı hatası kontrolü
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Formdan gelen veriler
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // İlk olarak users tablosunda kontrol edelim
    $sql_user = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("ss", $user, $pass);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    // Eğer users tablosunda bir kullanıcı varsa
    if ($result_user->num_rows > 0) {
        $user_data = $result_user->fetch_assoc();
        $_SESSION['username'] = $user;
        
        // Role kontrolü yap
        if (isset($user_data['role']) && $user_data['role'] == 'stokçu') {
            $_SESSION['role'] = 'stokçu';
            header("Location: stokcu.php"); // Stokçu paneline yönlendir
            exit();
        } else {
            $_SESSION['role'] = 'user';
            header("Location: user_dashboard.php"); // Normal kullanıcı paneline yönlendir
            exit();
        }
    }

    // Eğer users tablosunda kullanıcı bulunamadıysa, admins tablosunda kontrol edelim
    $sql_admin = "SELECT * FROM admins WHERE username = ? AND password = ?";
    $stmt_admin = $conn->prepare($sql_admin);
    $stmt_admin->bind_param("ss", $user, $pass);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    // Eğer admins tablosunda bir admin varsa
    if ($result_admin->num_rows > 0) {
        $admin_data = $result_admin->fetch_assoc();
        $_SESSION['username'] = $user;
        $_SESSION['role'] = 'admin';
        header("Location: admin_dashboard.php"); // Admin paneline yönlendir
        exit();
    }

    // Eğer hem users hem de admins tablosunda kullanıcı yoksa
    echo "<script>alert('Geçersiz kullanıcı adı veya şifre'); window.location.href = 'login.html';</script>";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Stok Yönetim Sistemi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: bold;
            padding: 20px;
            text-align: center;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-control {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px;
            font-weight: bold;
            border-radius: 8px;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .alert-danger {
            background-color: var(--danger-color);
            color: white;
            border: none;
            border-radius: 8px;
        }
        
        .login-icon {
            font-size: 60px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-boxes login-icon"></i>
                <h3>Stok Yönetim Sistemi</h3>
                <p class="mb-0">Giriş Yapın</p>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    Geçersiz kullanıcı adı veya şifre!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Kullanıcı Adı:</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Şifre:</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt"></i> Giriş Yap
                    </button>
                </form>
            </div>
        </div>
        <div class="text-center mt-3 text-muted">
            <p>© <?php echo date('Y'); ?> Stok Yönetim Sistemi | Tüm Hakları Saklıdır</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Veritabanı bağlantısını kapat
$conn->close();
?>
