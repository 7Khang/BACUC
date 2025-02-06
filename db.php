<?php
$host = 'localhost';
$db = 'qa_system';
$user = 'root'; // Mặc định là 'root' trong XAMPP
$pass = '';     // Mặc định không có mật khẩu trong XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Không thể kết nối đến cơ sở dữ liệu: " . $e->getMessage());
}
?>