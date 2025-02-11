<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = htmlspecialchars($data['id']);
    $field = htmlspecialchars($data['field']);
    $value = htmlspecialchars($data['value']);

    try {
        $stmt = $pdo->prepare("UPDATE questions SET $field = :value WHERE id = :id");
        $stmt->execute([':value' => $value, ':id' => $id]);

        echo json_encode(['success' => true, 'message' => 'Cập nhật thành công.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}
?>