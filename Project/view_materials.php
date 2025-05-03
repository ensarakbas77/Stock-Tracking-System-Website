<?php
require_once("aws_db.php");

// Güvenlikli sorgu kullanımı
$stmt = $conn->prepare("SELECT * FROM materials");
$stmt->execute();
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malzeme Yönetim Paneli</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Flowbite CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #f9fafb, #e5e7eb);
            min-height: 100vh;
        }
        .dashboard-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1.5rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }
        .material-table th, .material-table td {
            text-align: center;
            padding: 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }
        .material-table th {
            background: #1d4ed8;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: background 0.2s ease;
        }
        .material-table th:hover {
            background: #1e40af;
        }
        .material-table td {
            font-size: 0.875rem;
            color: #1f2937;
        }
        .material-table tr {
            transition: background 0.2s ease;
        }
        .material-table tr:hover {
            background: #f1f5f9;
        }
        .action-buttons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.4rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: transform 0.2s ease, background 0.2s ease;
        }
        .action-buttons a:hover {
            transform: translateY(-1px);
        }
        .action-edit {
            background: #16a34a;
            color: white;
        }
        .action-edit:hover {
            background: #15803d;
        }
        .action-delete {
            background: #dc2626;
            color: white;
        }
        .action-delete:hover {
            background: #b91c1c;
        }
        .empty-state {
            text-align: center;
            padding: 2rem;
            background: #f9fafb;
            border-radius: 0.5rem;
            color: #6b7280;
        }
        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .empty-state h5 {
            font-size: 1.125rem;
            font-weight: 500;
        }
        .empty-state p {
            font-size: 0.875rem;
        }
        h2 {
            font-weight: 600;
            font-size: 1.5rem;
            color: #1f2937;
        }
        .btn-back {
            padding: 0.4rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            background: #6b7280;
            color: white;
            transition: background 0.2s ease;
        }
        .btn-back:hover {
            background: #4b5563;
        }
        @media (max-width: 768px) {
            .material-table td {
                padding: 0.5rem;
            }
            .action-buttons a {
                padding: 0.3rem 0.8rem;
                font-size: 0.75rem;
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
<div class="dashboard-container">
    <!-- Başlık ve Geri Dön Butonu -->
    <div class="flex items-center mb-6">
        <h2 class="me-auto">Malzeme Yönetim Paneli</h2>
    </div>

    <!-- Tablo ve Eylemler -->
    <div class="material-table overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr>
                    <th scope="col">Malzeme Numarası</th>
                    <th scope="col">Malzeme Adı</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Stok</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($materials) > 0): ?>
                    <?php foreach ($materials as $material): ?>
                        <tr class="fade-in">
                            <td><?php echo htmlspecialchars($material['material_id']); ?></td>
                            <td><?php echo htmlspecialchars($material['material_name']); ?></td>
                            <td><?php echo htmlspecialchars($material['material_type']); ?></td>
                            <td><?php echo htmlspecialchars($material['material_quantity']); ?></td>
                            <td>
                                <div class="action-buttons flex justify-center gap-2">
                                    
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="empty-state">
                        <td colspan="5">
                            <i class="fas fa-box-open mb-3"></i>
                            <h5 class="mb-2">Henüz malzeme yok</h5>
                            <p>Lütfen en az bir malzeme ekleyin</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Flowbite JS -->
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>