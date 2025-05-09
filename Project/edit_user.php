<?php
require_once("aws_db.php");

if (isset($_GET['id'], $_GET['username'], $_GET['role'])) {
    $id = $_GET['id'];
    $username = $_GET['username'];
    $role = $_GET['role'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE user_id = ?");
    $stmt->execute([$username, $role, $id]);

    header("Location: view_users.php"); // geri dönmek için sayfa adını güncelle
    exit();
} else {
    echo "Geçersiz veri!";
}
