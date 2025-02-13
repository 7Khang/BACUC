<?php
session_start();
require 'db.php'; // Kết nối cơ sở dữ liệu

// Xác định vai trò người dùng
$is_admin = ($_SESSION['role'] ?? '') === 'admin';

// Kiểm tra kết nối cơ sở dữ liệu
try {
    $pdo->query("SELECT 1");
} catch (Exception $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . htmlspecialchars($e->getMessage()));
}

// Xử lý thêm câu hỏi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);
    $contributor = $_SESSION['username'] ?? 'Ẩn danh';

    if (!empty($question)) {
        try {
            // Thêm câu hỏi vào cơ sở dữ liệu với trạng thái "pending"
            $stmt = $pdo->prepare("
                INSERT INTO questions (question, answer, contributor, status)
                VALUES (:question, :answer, :contributor, 'pending')
            ");
            $stmt->execute([
                ':question' => htmlspecialchars($question),
                ':answer' => htmlspecialchars($answer),
                ':contributor' => $contributor
            ]);

            // Lưu thông báo thành công vào session
            $_SESSION['message'] = "Câu hỏi đã được gửi và đang chờ admin duyệt.";
            $_SESSION['message_type'] = 'success';
        } catch (Exception $e) {
            // Lưu thông báo lỗi vào session
            $_SESSION['message'] = "Lỗi khi thêm câu hỏi: " . htmlspecialchars($e->getMessage());
            $_SESSION['message_type'] = 'error';
        }
    } else {
        // Lưu thông báo lỗi nếu câu hỏi trống
        $_SESSION['message'] = "Vui lòng nhập câu hỏi.";
        $_SESSION['message_type'] = 'error';
    }

    // Chuyển hướng về trang chính để tránh gửi lại form
    header("Location: daidien.php");
    exit;
}

/**
 * Hàm duyệt câu hỏi
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    $approveId = $_POST['approve_id'];
    try {
        // Cập nhật trạng thái câu hỏi thành "approved"
        $stmt = $pdo->prepare("UPDATE questions SET status = 'approved' WHERE id = :id");
        $stmt->execute([':id' => $approveId]);

        // Trả về thông báo thành công
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Câu hỏi đã được duyệt thành công!'
        ]);
        exit;
    } catch (Exception $e) {
        // Trả về thông báo lỗi nếu có
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi duyệt câu hỏi: ' . htmlspecialchars($e->getMessage())
        ]);
        exit;
    }
}

/**
 * Hàm xử lý xóa câu hỏi
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];

    try {
        // Thực hiện xóa câu hỏi
        $stmt = $pdo->prepare("DELETE FROM questions WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);

        // Trả về thông báo thành công
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Câu hỏi đã bị xóa thành công!'
        ]);
        exit;
    } catch (Exception $e) {
        // Trả về thông báo lỗi nếu có
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi xóa câu hỏi: ' . htmlspecialchars($e->getMessage())
        ]);
        exit;
    }
}

/**
 * Hàm xử lý chỉnh sửa câu hỏi hoặc đáp án
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    if (!$is_admin) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện hành động sửa.'
        ]);
        exit;
    }

    $edit_id = $_POST['edit_id'];
    $edit_field = isset($_POST['question']) ? 'question' : 'answer';
    $edit_value = trim($_POST[$edit_field]);

    if (empty($edit_value)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng nhập nội dung.'
        ]);
        exit;
    }

    try {
        // Kiểm tra câu hỏi có tồn tại không
        $stmt = $pdo->prepare("SELECT id FROM questions WHERE id = :id");
        $stmt->execute([':id' => $edit_id]);
        $existingQuestion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingQuestion) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Câu hỏi không tồn tại.'
            ]);
            exit;
        }

        // Cập nhật trường tương ứng
        $stmt = $pdo->prepare("
            UPDATE questions
            SET {$edit_field} = :value
            WHERE id = :id
        ");
        $stmt->execute([
            ':value' => htmlspecialchars($edit_value),
            ':id' => $edit_id
        ]);
        exit;
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi cập nhật: ' . htmlspecialchars($e->getMessage())
        ]);
        exit;
    }
}

?>