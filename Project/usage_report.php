<?php
session_start();
require_once("aws_db.php");

// Giriş kontrolü
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Log verilerini çek
try {
    $stmt = $conn->query("SELECT * FROM logview ORDER BY usage_date DESC");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veri çekilirken hata oluştu: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanım Raporu</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Flowbite CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #f9fafb, #e5e7eb);
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1.5rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }
        .table th, .table td {
            text-align: center;
            padding: 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }
        .table th {
            background: #1d4ed8;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: background 0.2s ease;
        }
        .table th:hover {
            background: #1e40af;
        }
        .table td {
            font-size: 0.875rem;
            color: #1f2937;
        }
        .table tr {
            transition: background 0.2s ease;
        }
        .table tr:hover {
            background: #f1f5f9;
        }
        .empty-state {
            text-align: center;
            padding: 2rem;
            background: #f9fafb;
            border-radius: 0.5rem;
            color: #6b7280;
            font-size: 0.875rem;
        }
        h2 {
            font-weight: 600;
            font-size: 1.5rem;
            color: #1f2937;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 768px) {
            .table td {
                padding: 0.5rem;
                font-size: 0.75rem;
            }
            h2 {
                font-size: 1.25rem;
            }
        }
        .fade-in {
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards;
        }
        @keyframes fadeIn {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">📊 Malzeme Kullanım Raporu</h2>

    <?php if (count($logs) > 0): ?>
        <div class="table-container overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Kullanıcı Adı</th>
                        <th>Kullanıcı Rolü</th>
                        <th>Malzeme Adı</th>
                        <th>Miktar</th>
                        <th>Kullanım Sebebi</th>
                        <th>Tarih</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr class="fade-in">
                            <td><?php echo htmlspecialchars($log['log_id']); ?></td>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo htmlspecialchars($log['role']); ?></td>
                            <td><?php echo htmlspecialchars($log['material_name']); ?></td>
                            <td><?php echo htmlspecialchars($log['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($log['usage_reason']); ?></td>
                            <td><?php echo htmlspecialchars($log['usage_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-info-circle mb-3" style="font-size: 2.5rem;"></i>
            <h5 class="mb-2 font-medium">Kayıtlı kullanım verisi bulunmamaktadır</h5>
        </div>
    <?php endif; ?>
</div>

<!-- Flowbite JS -->
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>