<?php
session_start();

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "error" => "Bạn không có quyền thực hiện hành động này."]);
    exit;
}

// Tiếp tục xử lý request...
?>
<?php
header("Content-Type: application/json");

// Kết nối database
require_once "db_connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data['id'] ?? null;
    $field = $data['field'] ?? null;
    $value = trim($data['value'] ?? '');

    if (!$id || !$field || !$value) {
        echo json_encode(["success" => false, "error" => "Dữ liệu không hợp lệ."]);
        exit;
    }

    // Chỉ cho phép cập nhật các trường hợp lệ
    $allowedFields = ['question', 'answer'];
    if (!in_array($field, $allowedFields)) {
        echo json_encode(["success" => false, "error" => "Trường không hợp lệ."]);
        exit;
    }

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
    echo json_encode(["success" => false, "error" => "Phương thức không hợp lệ."]);
}
?>