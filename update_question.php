<?php
session_start();

// Kiểm tra vai trò của người dùng
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    exit;
}

// Kết nối cơ sở dữ liệu
require 'db.php';

// Nhận dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$field = $data['field'];
$value = $data['value'];

// Cập nhật dữ liệu trong cơ sở dữ liệu
$stmt = $pdo->prepare("UPDATE questions SET $field = ? WHERE id = ?");
$stmt->execute([$value, $id]);

// Trả về phản hồi
if ($stmt->rowCount() > 0) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
?>