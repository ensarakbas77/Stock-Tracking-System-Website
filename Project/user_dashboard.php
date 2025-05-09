<?php
session_start();
require_once("aws_db.php");

// Giriş kontrolü
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

// Malzeme verilerini çek
try {
    $stmt = $conn->query("SELECT * FROM materials ORDER BY material_name ASC");
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Malzeme verisi alınamadı: " . $e->getMessage());
}

// Form gönderildiğinde malzeme kullanım işlemi
$message = "";
$alertClass = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['material_id'])) {
    $material_id = $_POST['material_id'];
    $quantity = intval($_POST['quantity']);
    $reason = trim($_POST['usage_reason']);

    try {
        // Seçilen malzemenin mevcut miktarını kontrol et
        $stmt = $conn->prepare("SELECT * FROM materials WHERE material_id = ?");
        $stmt->execute([$material_id]);
        $material = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$material) {
            $message = "Malzeme bulunamadı.";
            $alertClass = "alert-danger";
        } elseif ($quantity <= 0) {
            $message = "Lütfen geçerli bir miktar girin.";
            $alertClass = "alert-warning";
        } elseif ($quantity > $material['material_quantity']) {
            $message = "Yeterli stok yok. Mevcut stok: " . $material['material_quantity'];
            $alertClass = "alert-warning";
        } else {
            // Stoktan düş
            $stmt = $conn->prepare("UPDATE materials SET material_quantity = material_quantity - ? WHERE material_id = ?");
            $stmt->execute([$quantity, $material_id]);

            

            $message = "Malzeme başarıyla kullanıldı.";
            $alertClass = "alert-success";
            
            // Sayfayı yenile ve güncel malzeme listesini getir
            $stmt = $conn->query("SELECT * FROM materials ORDER BY material_name ASC");
            $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $message = "İşlem sırasında hata oluştu: " . $e->getMessage();
        $alertClass = "alert-danger";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Paneli</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-color: #f5f7ff;
            --card-bg: #ffffff;
            --text-color: #1f2937;
        }
        
        body {
            background-color: var(--bg-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
        }
        
        .dashboard {
            max-width: 1200px;
            margin: 40px auto;
            background-color: var(--card-bg);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            padding: 20px 30px;
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 25px;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .logout-btn {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #e5e7eb;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            border-color: var(--primary);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary);
            color: white !important;
            border: none;
        }
        
        .low-stock {
            color: #ef4444;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="dashboard">
    <div class="header">
        <div>
            <h2><i class="fas fa-user-circle me-2"></i> Hoşgeldiniz, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            <p class="mb-0"></p>
        </div>
        <a href="index.html" class="btn logout-btn"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
    </div>

    <div class="content">
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-list me-2"></i> Malzeme Listesi
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table id="materialsTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Malzeme Adı</th>
                                        <th>Tipi</th>
                                        <th>Miktar</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materials as $material): ?>
                                        <tr>
                                            <td><?php echo $material['material_id']; ?></td>
                                            <td><?php echo htmlspecialchars($material['material_name']); ?></td>
                                            <td><?php echo htmlspecialchars($material['material_type']); ?></td>
                                            <td class="<?php echo $material['material_quantity'] < 10 ? 'low-stock' : ''; ?>">
                                                <?php echo $material['material_quantity']; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary use-material-btn" 
                                                        data-id="<?php echo $material['material_id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($material['material_name']); ?>"
                                                        data-quantity="<?php echo $material['material_quantity']; ?>">
                                                    <i class="fas fa-box-open me-1"></i> Kullan
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-box-open me-2"></i> Malzeme Kullan
                    </div>
                    <div class="card-body">
                        <form method="post" action="" id="useMaterialForm">
                            <div class="mb-3">
                                <label for="material_id" class="form-label">Malzeme Seç</label>
                                <select name="material_id" id="material_id" class="form-select" required>
                                    <option value="" disabled selected>Seçiniz...</option>
                                    <?php foreach ($materials as $m): ?>
                                        <option value="<?php echo $m['material_id']; ?>">
                                            <?php echo htmlspecialchars($m['material_name']) . " (Stok: " . $m['material_quantity'] . ")"; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Kullanılacak Miktar</label>
                                <input type="number" class="form-control" name="quantity" id="quantity" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="usage_reason" class="form-label">Kullanım Sebebi</label>
                                <textarea name="usage_reason" id="usage_reason" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning text-white">
                                <i class="fas fa-check-circle me-2"></i> Malzemeyi Kullan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery, Bootstrap JS ve DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // DataTable başlat
    $('#materialsTable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/tr.json"
        },
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Tümü"]],
        order: [[1, 'asc']] // İsme göre sırala
    });
    
    // "Kullan" butonuna tıklandığında form alanlarını doldur
    $('.use-material-btn').click(function() {
        const materialId = $(this).data('id');
        const materialName = $(this).data('name');
        const maxQuantity = $(this).data('quantity');
        
        $('#material_id').val(materialId);
        $('#quantity').attr('max', maxQuantity);
        $('#quantity').val(1);
        $('#usage_reason').focus();
        
        // Form alanına kaydır
        $('html, body').animate({
            scrollTop: $("#useMaterialForm").offset().top - 100
        }, 500);
    });
});
</script>

</body>
</html>