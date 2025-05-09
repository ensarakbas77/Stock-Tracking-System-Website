<?php
// Oturum başlatma
session_start();

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['username'])) {
    // Kullanıcı giriş yapmamış, login sayfasına yönlendir
    header("Location: login.php");
    exit();
}

// Veritabanı bağlantısı
$servername = "database-1.c34kakm0cfzi.eu-north-1.rds.amazonaws.com";
$username = "admin";
$password = "7046!Ensar";
$dbname = "stok_takip";

$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı kontrolü
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// Malzeme ekleme işlemi
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_material'])) {
    $material_name = $conn->real_escape_string($_POST['material_name']);
    $material_type = $conn->real_escape_string($_POST['material_type']);
    $material_quantity = intval($_POST['material_quantity']);
    
    // Gerekli alanların kontrolü
    if (empty($material_name) || empty($material_type) || $material_quantity <= 0) {
        $message = "Lütfen tüm alanları doğru şekilde doldurun!";
        $messageType = "error";
    } else {
        // Önce aynı malzemenin olup olmadığını kontrol et
        $check_sql = "SELECT * FROM materials WHERE material_name = '$material_name'";
        $result = $conn->query($check_sql);
        
        if ($result->num_rows > 0) {
            // Malzeme zaten varsa, miktarını güncelle
            $update_sql = "UPDATE materials SET material_quantity = material_quantity + $material_quantity WHERE material_name = '$material_name'";
            
            if ($conn->query($update_sql) === TRUE) {
                $message = "Malzeme miktarı başarıyla güncellendi!";
                $messageType = "success";
            } else {
                $message = "Hata: " . $update_sql . "<br>" . $conn->error;
                $messageType = "error";
            }
        } else {
            // Malzeme yoksa yeni ekle
            $insert_sql = "INSERT INTO materials (material_name, material_type, material_quantity) VALUES ('$material_name', '$material_type', $material_quantity)";
            
            if ($conn->query($insert_sql) === TRUE) {
                $message = "Yeni malzeme başarıyla eklendi!";
                $messageType = "success";
            } else {
                $message = "Hata: " . $insert_sql . "<br>" . $conn->error;
                $messageType = "error";
            }
        }
    }
}

// Malzeme güncelleme işlemi - ID'ye göre güncelleme yapacak şekilde değiştirildi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_material'])) {
    $material_id = intval($_POST['material_id']);
    $material_type = $conn->real_escape_string($_POST['material_type']);
    $material_quantity = intval($_POST['material_quantity']);
    
    // Gerekli alanların kontrolü
    if ($material_id <= 0) {
        $message = "Lütfen güncellemek istediğiniz malzeme ID'sini girin!";
        $messageType = "error";
    } else {
        // Malzemenin var olup olmadığını kontrol et
        $check_sql = "SELECT * FROM materials WHERE material_id = $material_id";
        $result = $conn->query($check_sql);
        
        if ($result->num_rows > 0) {
            // Güncelleme SQL'i oluştur
            $update_sql = "UPDATE materials SET ";
            $updates = [];
            
            // Hangi alanların güncelleneceğini kontrol et
            if (!empty($material_type)) {
                $updates[] = "material_type = '$material_type'";
            }
            
            if ($material_quantity > 0) {
                $updates[] = "material_quantity = $material_quantity";
            }
            
            // Güncellenecek alan var mı kontrol et
            if (count($updates) > 0) {
                $update_sql .= implode(", ", $updates);
                $update_sql .= " WHERE material_id = $material_id";
                
                if ($conn->query($update_sql) === TRUE) {
                    $message = "Malzeme başarıyla güncellendi!";
                    $messageType = "success";
                } else {
                    $message = "Hata: " . $conn->error;
                    $messageType = "error";
                }
            } else {
                $message = "Güncellenecek bilgi girilmedi!";
                $messageType = "error";
            }
        } else {
            $message = "Belirtilen ID'ye sahip malzeme bulunamadı!";
            $messageType = "error";
        }
    }
}

// Malzeme silme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_material'])) {
    $material_id = intval($_POST['material_id']);
    
    $delete_sql = "DELETE FROM materials WHERE material_id = $material_id";
    
    if ($conn->query($delete_sql) === TRUE) {
        $message = "Malzeme başarıyla silindi!";
        $messageType = "success";
    } else {
        $message = "Hata: " . $delete_sql . "<br>" . $conn->error;
        $messageType = "error";
    }
}

// Tüm malzemeleri getir
$sql = "SELECT material_id, material_name, material_type, material_quantity FROM materials ORDER BY material_name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Yönetim Paneli</title>
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
            --warning-color: #f39c12;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: bold;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
            border-color: #e67e22;
            color: white;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        
        .table thead {
            background-color: var(--primary-color);
            color: white;
        }
        
        .alert-success {
            background-color: var(--success-color);
            color: white;
            border: none;
        }
        
        .alert-danger {
            background-color: var(--danger-color);
            color: white;
            border: none;
        }
        
        .dashboard-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            flex: 1;
            min-width: 200px;
            padding: 20px;
            border-radius: 10px;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-card h3 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .stat-card p {
            margin: 5px 0 0;
            opacity: 0.8;
        }
        
        .user-welcome {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .user-welcome h2 {
            color: var(--dark-color);
            margin-bottom: 0;
        }
        
        .logout-btn {
            background-color: var(--light-color);
            color: var(--dark-color);
            border: none;
            transition: all 0.2s;
        }
        
        .logout-btn:hover {
            background-color: var(--danger-color);
            color: white;
        }
        
        .nav-tabs .nav-link {
            color: var(--dark-color);
            border: 1px solid #dee2e6;
            border-bottom: none;
            margin-right: 5px;
            border-radius: 10px 10px 0 0;
        }
        
        .nav-tabs .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .tab-content {
            border: 1px solid #dee2e6;
            border-top: none;
            padding: 20px;
            border-radius: 0 0 10px 10px;
            background-color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-boxes"></i> Stok Yönetim Sistemi
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- User Welcome Message -->
        <div class="user-welcome d-flex justify-content-between align-items-center">
            <h2>
                <i class="fas fa-user-circle"></i> Hoş Geldiniz
            </h2>
            <a href="index.html" class="btn logout-btn">
                <i class="fas fa-sign-out-alt"></i> Çıkış Yap
            </a>
        </div>

        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <?php
            // Toplam malzeme sayısı
            $total_items_query = "SELECT COUNT(*) as total FROM materials";
            $total_items_result = $conn->query($total_items_query);
            $total_items = $total_items_result->fetch_assoc()['total'];
            
            // Toplam malzeme miktarı
            $total_quantity_query = "SELECT SUM(material_quantity) as total FROM materials";
            $total_quantity_result = $conn->query($total_quantity_query);
            $total_quantity = $total_quantity_result->fetch_assoc()['total'];
            if ($total_quantity === NULL) $total_quantity = 0;
            
            // Malzeme türü sayısı
            $material_types_query = "SELECT COUNT(DISTINCT material_type) as total FROM materials";
            $material_types_result = $conn->query($material_types_query);
            $material_types = $material_types_result->fetch_assoc()['total'];
            ?>
            
            <div class="stat-card" style="background-color: #3498db;">
                <i class="fas fa-cubes"></i>
                <h3><?php echo $total_items; ?></h3>
                <p>Toplam Malzeme</p>
            </div>
            
            <div class="stat-card" style="background-color: #2ecc71;">
                <i class="fas fa-box"></i>
                <h3><?php echo $total_quantity; ?></h3>
                <p>Toplam Stok Adedi</p>
            </div>
            
            <div class="stat-card" style="background-color: #9b59b6;">
                <i class="fas fa-tags"></i>
                <h3><?php echo $material_types; ?></h3>
                <p>Malzeme Türü</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($message)) : ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Add/Update Material Form -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-edit"></i> Malzeme İşlemleri
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button" role="tab" aria-controls="add" aria-selected="true">Ekle</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="update-tab" data-bs-toggle="tab" data-bs-target="#update" type="button" role="tab" aria-controls="update" aria-selected="false">Güncelle</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <!-- Add Material Tab -->
                            <div class="tab-pane fade show active" id="add" role="tabpanel" aria-labelledby="add-tab">
                                <form method="post" action="" class="mt-3">
                                    <div class="mb-3">
                                        <label for="material_name" class="form-label">Malzeme Adı:</label>
                                        <input type="text" class="form-control" id="material_name" name="material_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="material_type" class="form-label">Malzeme Türü:</label>
                                        <input type="text" class="form-control" id="material_type" name="material_type" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="material_quantity" class="form-label">Miktar:</label>
                                        <input type="number" class="form-control" id="material_quantity" name="material_quantity" min="1" required>
                                    </div>
                                    <button type="submit" name="add_material" class="btn btn-primary w-100">
                                        <i class="fas fa-plus-circle"></i> Malzeme Ekle
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Update Material Tab - ID'ye göre güncelleme yapacak şekilde değiştirildi -->
                            <div class="tab-pane fade" id="update" role="tabpanel" aria-labelledby="update-tab">
                                <form method="post" action="" class="mt-3">
                                    <div class="mb-3">
                                        <label for="update_material_id" class="form-label">Malzeme ID:</label>
                                        <input type="number" class="form-control" id="update_material_id" name="material_id" required>
                                        <small class="text-muted">Güncellemek istediğiniz malzemenin ID numarasını girin</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="update_material_type" class="form-label">Yeni Malzeme Türü:</label>
                                        <input type="text" class="form-control" id="update_material_type" name="material_type">
                                        <small class="text-muted">Değiştirmek istemiyorsanız boş bırakabilirsiniz</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="update_material_quantity" class="form-label">Yeni Miktar:</label>
                                        <input type="number" class="form-control" id="update_material_quantity" name="material_quantity" min="1">
                                        <small class="text-muted">Değiştirmek istemiyorsanız boş bırakabilirsiniz</small>
                                    </div>
                                    <button type="submit" name="update_material" class="btn btn-warning w-100">
                                        <i class="fas fa-sync-alt"></i> Malzeme Güncelle
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Materials List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list"></i> Malzeme Listesi
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Malzeme Adı</th>
                                        <th>Malzeme Türü</th>
                                        <th>Miktar</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row["material_id"] . "</td>";
                                            echo "<td>" . htmlspecialchars($row["material_name"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["material_type"]) . "</td>";
                                            echo "<td>" . $row["material_quantity"] . "</td>";
                                            echo "<td>
                                                    <button type='button' class='btn btn-warning btn-sm me-1 fill-update-form' 
                                                        data-id='" . $row["material_id"] . "' 
                                                        data-type='" . htmlspecialchars($row["material_type"]) . "' 
                                                        data-quantity='" . $row["material_quantity"] . "'>
                                                        <i class='fas fa-edit'></i>
                                                    </button>
                                                    <form method='post' action='' style='display: inline;'>
                                                        <input type='hidden' name='material_id' value='" . $row["material_id"] . "'>
                                                      
                                                    </form>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>Henüz malzeme bulunmamaktadır.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-5 mt-5">
        <div class="container">
            <p class="mb-0"></p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript for Update Functionality - ID'ye göre güncelleme için değiştirildi -->
    <script>
        // Listeden güncelleme formuna veri aktarma işlemi
        document.addEventListener('DOMContentLoaded', function() {
            const updateButtons = document.querySelectorAll('.fill-update-form');
            
            updateButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Form alanlarını doldur
                    const materialId = this.getAttribute('data-id');
                    const materialType = this.getAttribute('data-type');
                    const materialQuantity = this.getAttribute('data-quantity');
                    
                    document.getElementById('update_material_id').value = materialId;
                    document.getElementById('update_material_type').value = materialType;
                    document.getElementById('update_material_quantity').value = materialQuantity;
                    
                    // Güncelleme sekmesine geçiş yap
                    const updateTab = document.getElementById('update-tab');
                    const tabInstance = bootstrap.Tab.getOrCreateInstance(updateTab);
                    tabInstance.show();
                });
            });
        });
    </script>
</body>
</html>

<?php
// Veritabanı bağlantısını kapat
$conn->close();
?>