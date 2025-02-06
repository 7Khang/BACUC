<?php
require 'db.php';

$username = 'admin'; // Tên người dùng admin
$password = '123'; // Thay thế bằng mật khẩu bạn muốn
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Kiểm tra xem tài khoản admin đã tồn tại chưa
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "Tài khoản admin đã tồn tại!";
} else {
    // Thêm tài khoản admin nếu chưa tồn tại
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $hashed_password, 'admin']);
    echo "Tài khoản admin đã được tạo!";
}
?>