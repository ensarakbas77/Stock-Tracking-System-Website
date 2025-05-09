<?php
require_once("aws_db.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$id]);

    header("Location: view_users.php"); // geri dönmek için sayfa adını güncelle
    exit();
} else {
    echo "Geçersiz istek!";
}
