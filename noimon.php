<?php
session_start();
require_once 'db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra quyền admin
$is_admin = ($_SESSION['role'] ?? '') === 'admin';

/**
 * Hàm thêm câu hỏi mới
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);
    $contributor = $_SESSION['username'] ?? 'Ẩn danh';

    if (!empty($question)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO questions (question, answer, contributor, status)
                VALUES (:question, :answer, :contributor, 'pending')
            ");
            $stmt->execute([
                ':question' => htmlspecialchars($question),
                ':answer' => htmlspecialchars($answer),
                ':contributor' => $contributor
            ]);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Câu hỏi đã được gửi và đang chờ admin duyệt.',
                'data' => [
                    'id' => $pdo->lastInsertId(),
                    'question' => $question,
                    'answer' => $answer,
                    'contributor' => $contributor,
                    'status' => 'pending'
                ]
            ]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi thêm câu hỏi: ' . htmlspecialchars($e->getMessage())
            ]);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng nhập câu hỏi.'
        ]);
        exit;
    }
}

// Hàm duyệt câu hỏi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    if ($_SESSION['role'] !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện hành động duyệt.'
        ]);
        exit;
    }
    $approveId = $_POST['approve_id'];
    $stmt = $pdo->prepare("UPDATE questions SET status = 'approved' WHERE id = :id");
    $stmt->execute([':id' => $approveId]);
    echo "Câu hỏi đã được duyệt thành công!";
    exit;
}

// Hàm xóa câu hỏi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if ($_SESSION['role'] !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện hành động xóa.'
        ]);
        exit;
    }
    $deleteId = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = :id");
    $stmt->execute([':id' => $deleteId]);
    echo "Câu hỏi đã bị xóa thành công!";
    exit;
}

// Hàm sửa câu hỏi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    if ($_SESSION['role'] !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện hành động sửa.'
        ]);
        exit;
    }
    $edit_id = $_POST['edit_id'];
    $edit_question = trim($_POST['edit_question']);
    $edit_answer = trim($_POST['edit_answer']);
    if (empty($edit_question)) {
        echo "Vui lòng nhập câu hỏi.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM questions WHERE id = :id");
            $stmt->execute([':id' => $edit_id]);
            $existingQuestion = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$existingQuestion) {
                echo "Câu hỏi không tồn tại.";
                exit;
            }
            $stmt = $pdo->prepare("
                UPDATE questions
                SET question = :question, answer = :answer
                WHERE id = :id
            ");
            $stmt->execute([
                ':question' => htmlspecialchars($edit_question),
                ':answer' => htmlspecialchars($edit_answer),
                ':id' => $edit_id
            ]);
            echo "Câu hỏi đã được cập nhật thành công!";
        } catch (Exception $e) {
            echo "Lỗi khi cập nhật câu hỏi: " . htmlspecialchars($e->getMessage());
        }
    }
}

/**
 * Hàm lấy danh sách câu hỏi
 */
function fetchQuestions($pdo) {
    $query = "
        SELECT * FROM questions
        ORDER BY 
        CASE 
        WHEN status = 'pending' THEN 0 
        ELSE 1 
        END,
        question ASC
    ";

    if (($_SESSION['role'] ?? '') !== 'admin') {
        $current_user = $_SESSION['username'] ?? 'Ẩn danh';
        $query = "
            SELECT * FROM questions
            WHERE status = 'approved' OR contributor = :current_user
            ORDER BY 
            CASE 
            WHEN status = 'pending' THEN 0 
            ELSE 1 
            END,
            question ASC
        ";
    }

    $stmt = $pdo->prepare($query);
    if (($_SESSION['role'] ?? '') !== 'admin') {
        $stmt->execute([':current_user' => $current_user]);
    } else {
        $stmt->execute();
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Hàm lấy top 3 người đóng góp
 */
function fetchTopContributors($pdo) {
    $stmt = $pdo->query("
        SELECT 
        contributor, 
        COUNT(*) AS total_contributions
        FROM 
        questions
        WHERE 
        contributor != 'Ẩn danh'
        GROUP BY 
        contributor
        ORDER BY 
        total_contributions DESC
        LIMIT 3
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Trả về danh sách câu hỏi dưới dạng JSON
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => fetchQuestions($pdo)
    ]);
    exit;
}

if (isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']);
    if (!empty($keyword)) {
        // Truy vấn cơ sở dữ liệu để tìm câu hỏi chứa từ khóa
        $query = "
            SELECT * FROM questions
            WHERE question LIKE :keyword
            ORDER BY 
            CASE 
            WHEN status = 'pending' THEN 0 
            ELSE 1 
            END,
            question ASC
        ";
        if (($_SESSION['role'] ?? '') !== 'admin') {
            $current_user = $_SESSION['username'] ?? 'Ẩn danh';
            $query = "
                SELECT * FROM questions
                WHERE (question LIKE :keyword) AND (status = 'approved' OR contributor = :current_user)
                ORDER BY 
                CASE 
                WHEN status = 'pending' THEN 0 
                ELSE 1 
                END,
                question ASC
            ";
        }
        $stmt = $pdo->prepare($query);
        if (($_SESSION['role'] ?? '') !== 'admin') {
            $stmt->execute([':keyword' => "%$keyword%", ':current_user' => $current_user]);
        } else {
            $stmt->execute([':keyword' => "%$keyword%"]);
        }
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Trả về kết quả dưới dạng JSON
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }
}

// Nếu không có từ khóa, trả về danh sách câu hỏi mặc định
header('Content-Type: application/json');
echo json_encode(fetchQuestions($pdo));
exit;

/**
 * Hàm lấy danh sách câu hỏi
 */
function fetchQuestions($pdo) {
    $query = "
        SELECT * FROM questions
        ORDER BY 
        CASE 
        WHEN status = 'pending' THEN 0 
        ELSE 1 
        END,
        question ASC
    ";
    if (($_SESSION['role'] ?? '') !== 'admin') {
        $current_user = $_SESSION['username'] ?? 'Ẩn danh';
        $query = "
            SELECT * FROM questions
            WHERE status = 'approved' OR contributor = :current_user
            ORDER BY 
            CASE 
            WHEN status = 'pending' THEN 0 
            ELSE 1 
            END,
            question ASC
        ";
    }
    $stmt = $pdo->prepare($query);
    if (($_SESSION['role'] ?? '') !== 'admin') {
        $stmt->execute([':current_user' => $current_user]);
    } else {
        $stmt->execute();
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

