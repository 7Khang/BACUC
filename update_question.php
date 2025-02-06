<?php
header("Content-Type: application/json");

// Kết nối database
require_once "db_connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data['id'];
    $field = $data['field'];
    $value = trim($data['value']);

    if (!empty($id) && !empty($field) && !empty($value)) {
        try {
            // Cập nhật trường tương ứng trong cơ sở dữ liệu
            $stmt = $pdo->prepare("
                UPDATE questions
                SET $field = :value
                WHERE id = :id
            ");
            $stmt->execute([
                ':value' => htmlspecialchars($value),
                ':id' => $id
            ]);

            echo json_encode(["success" => true]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Dữ liệu không hợp lệ."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Phương thức không hợp lệ."]);
}
?>