<?php
session_start();
require_once("aws_db.php");

// Admin olarak giriş yapıp yapmadığını kontrol et
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Kontrol Paneli</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Flowbite CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet">
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(180deg, #f3f4f6, #ffffff);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 200px;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%2360a5fa" fill-opacity="0.1" d="M0,160L48,176C96,192,192,224,288,213.3C384,203,480,149,576,144C672,139,768,181,864,192C960,203,1056,181,1152,165.3C1248,149,1344,139,1392,133.3L1440,128L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>') no-repeat top;
            z-index: -1;
        }
        .container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
            position: relative;
        }
        .dashboard-header {
            font-size: 2.75rem;
            font-weight: 700;
            color: #60a5fa;
            text-align: center;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.8s ease-out;
        }
        .dashboard-subtitle {
            font-size: 1.25rem;
            font-weight: 400;
            color: #6b7280;
            text-align: center;
            margin-bottom: 2rem;
        }
        .panel {
            background: #ffffff;
            border: 2px solid transparent;
            border-radius: 1.25rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            position: relative;
        }
        .panel:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 24px rgba(96, 165, 250, 0.2);
            border-color: #60a5fa;
        }
        .panel-header {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        .btn-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 2rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
            background: #60a5fa;
            color: #ffffff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 10px rgba(96, 165, 250, 0.3);
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(96, 165, 250, 0.5);
        }
        .btn-logout {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            background: #f472b6;
            color: #ffffff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(244, 114, 182, 0.5);
        }
        .card-icon {
            margin-right: 0.5rem;
            color: #60a5fa;
        }
        .shine::before {
            content: '';
            position: absolute;
            top: 0;
            left: -75%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
            transform: skewX(-25deg);
            animation: shine 1.5s ease infinite;
        }
        @media (max-width: 768px) {
            .dashboard-header {
                font-size: 2rem;
            }
            .dashboard-subtitle {
                font-size: 1rem;
            }
            .panel-header {
                font-size: 1.25rem;
            }
            .btn-custom, .btn-logout {
                padding: 0.5rem 1.5rem;
                font-size: 0.75rem;
            }
        }
        .fade-in {
            opacity: 0;
            animation: fadeIn 0.8s ease-out forwards;
        }
        @keyframes fadeIn {
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes shine {
            0% { left: -75%; }
            100% { left: 125%; }
        }
    </style>
</head>
<body>
<div class="container">
    <a href="index.html" class="btn-logout">
        <i class="fas fa-sign-out-alt mr-1"></i> Çıkış Yap
    </a>
    <h1 class="dashboard-header">Admin Kontrol Paneli</h1>
    <p class="dashboard-subtitle">Hoşgeldiniz</p>

    <!-- Admin Panel Panelleri -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Malzeme Yönetimi -->
        <div class="panel fade-in shine">
            <div class="panel-header">
                <i class="fas fa-cogs card-icon"></i> Malzeme Yönetimi
            </div>
            <a href="view_materials.php" class="btn-custom">Malzemeleri Yönet</a>
        </div>

        <!-- Kullanıcı Yönetimi -->
        <div class="panel fade-in shine" style="animation-delay: 0.2s;">
            <div class="panel-header">
                <i class="fas fa-users card-icon"></i> Kullanıcı Yönetimi
            </div>
            <a href="view_users.php" class="btn-custom">Kullanıcıları Yönet</a>
        </div>

        <!-- Kullanım Raporu -->
        <div class="panel fade-in shine" style="animation-delay: 0.4s;">
            <div class="panel-header">
                <i class="fas fa-chart-line card-icon"></i> Kullanım Raporu
            </div>
            <a href="usage_report.php" class="btn-custom">Raporları İncele</a>
        </div>
    </div>
</div>

<!-- Flowbite JS -->
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>