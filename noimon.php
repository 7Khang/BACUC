<?php
// Đặt tiêu đề cho trang
$page_title = "Nội Môn - Bổ Thiên Các";
// Include header
include 'header.php';
// Kết nối cơ sở dữ liệu
require 'db.php';

// Xử lý các hành động POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Thêm câu hỏi mới
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
        $question = trim($_POST['question']);
        $answer = trim($_POST['answer']);
        $contributor = $_SESSION['username']; // Lấy tên người dùng hiện tại
    
        if (!empty($question)) {
            try {
                // Kiểm tra xem câu hỏi đã tồn tại chưa
                $stmt = $pdo->prepare("SELECT id FROM questions WHERE question = :question");
                $stmt->execute([':question' => $question]);
                $existingQuestion = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($existingQuestion) {
                    echo "<p>Câu hỏi này đã tồn tại. Vui lòng thêm câu hỏi khác.</p>";
                } else {
                    // Thêm câu hỏi mới
                    $stmt = $pdo->prepare("
                        INSERT INTO questions (question, answer, contributor, status)
                        VALUES (:question, :answer, :contributor, 'pending')
                    ");
                    $stmt->execute([
                        ':question' => htmlspecialchars($question),
                        ':answer' => htmlspecialchars($answer),
                        ':contributor' => $contributor
                    ]);
    
                    // Chuyển hướng để tránh gửi lại form
                    header("Location: noimon.php");
                    exit;
                }
            } catch (Exception $e) {
                echo "<p>Lỗi khi thêm câu hỏi: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p>Vui lòng nhập câu hỏi.</p>";
        }
    }

    // Duyệt câu hỏi
    if (isset($_POST['approve_id']) && $_SESSION['role'] === 'admin') {
        $approveId = $_POST['approve_id'];
        $stmt = $pdo->prepare("
            UPDATE questions
            SET status = 'approved'
            WHERE id = :id
        ");
        $stmt->execute([':id' => $approveId]);
        echo "<p>Câu hỏi đã được duyệt.</p>";
    }

    // Xóa câu hỏi
    if (isset($_POST['delete_id']) && $_SESSION['role'] === 'admin') {
        $deleteId = $_POST['delete_id'];
        $stmt = $pdo->prepare("
            DELETE FROM questions
            WHERE id = :id
        ");
        $stmt->execute([':id' => $deleteId]);
        echo "<p>Câu hỏi đã bị xóa.</p>";
    }

    // Xử lý sửa câu hỏi
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id']) && $_SESSION['role'] === 'admin') {
        $edit_id = $_POST['edit_id'];
        $edit_question = trim($_POST['edit_question']);
        $edit_answer = trim($_POST['edit_answer']);
    
        if (!empty($edit_question)) {
            try {
                // Kiểm tra câu hỏi có tồn tại không
                $stmt = $pdo->prepare("SELECT id FROM questions WHERE id = :id");
                $stmt->execute([':id' => $edit_id]);
                $existingQuestion = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if (!$existingQuestion) {
                    echo json_encode(["success" => false, "error" => "Câu hỏi không tồn tại."]);
                    exit;
                }
    
                // Cập nhật câu hỏi và đáp án
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
    
                echo json_encode(["success" => true]);
            } catch (Exception $e) {
                echo json_encode(["success" => false, "error" => $e->getMessage()]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Vui lòng nhập câu hỏi."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Bạn không có quyền thực hiện hành động này."]);
    }
}

// Truy vấn danh sách câu hỏi
$query = "
    SELECT * FROM questions
    ORDER BY 
        CASE 
            WHEN status = 'pending' THEN 0 
            ELSE 1 
        END,
        question ASC
";

if ($_SESSION['role'] !== 'admin') {
    // Nếu không phải admin, chỉ hiển thị câu hỏi đã được duyệt
    $query = "
        SELECT * FROM questions
        WHERE status = 'approved'
        ORDER BY created_at DESC
    ";
}

$stmt = $pdo->query($query);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Truy vấn top 3 người đóng góp
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
$top_contributors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kiểm tra vai trò người dùng
$is_admin = ($_SESSION['role'] === 'admin');
$current_user = $_SESSION['username'] ?? 'Ẩn danh';

// Truy vấn danh sách câu hỏi
if ($_SESSION['role'] === 'admin') {
    // Admin có thể xem tất cả câu hỏi
    $query = "
        SELECT * FROM questions
        ORDER BY 
            CASE 
                WHEN status = 'pending' THEN 0 
                ELSE 1 
            END,
            question ASC
    ";
} else {
    // Người dùng thường chỉ xem câu hỏi đã duyệt hoặc câu hỏi của chính họ
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
if ($_SESSION['role'] !== 'admin') {
    $stmt->execute([':current_user' => $current_user]);
} else {
    $stmt->execute();
}
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/noimon.css">
    <script src="assets/js/script.js" defer></script> <!-- Liên kết tệp script.js -->
</head>
<body>
<!-- Hero Section -->
<div class="hero-section">
    <video autoplay muted loop id="hero-video">
        <source src="https://img.pikbest.com/best/01/01/90/648132113624d.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <H3>Chào mừng đạo hữu <?php echo htmlspecialchars($_SESSION['username'] ?? 'Ẩn danh'); ?> đến với</H3>
        <H1>ĐẠI ĐIỆN CỦA BỔ THIÊN CÁC</H1>
    </div>
</div>
<!-- Main Content -->
<div class="main-content">
    <!-- Phần Khoáng Mạch và Group Tông Môn -->
    <div class="split-container">
        <!-- Phần Khoáng Mạch -->
        <div class="section khoang-mach" id="khoang-mach">
            <h2>KHOÁNG MẠCH</h2>
            <p>Tiến vào mỏ khoáng TIỂU THIÊN U CẢNH</p>
            <a href="#" class="button">TRUY CẬP KHOÁNG MẠCH</a>
        </div>
        <!-- Phần Group Tông Môn -->
        <div class="section group-tong-mon" id="group-tong-mon">
            <h2>GROUP TÔNG MÔN</h2>
            <p>Tham gia cộng đồng để giao lưu và học hỏi</p>
            <a href="https://zalo.me/g/rpujcs731" class="button">ẤN ĐỂ VÀO GROUP ZALO</a>
        </div>
    </div>
    <!-- Phần Bộ Đáp Án Câu Hỏi Vấn Đáp -->
    <div class="section van-dap" id="van-dap">
        <h2>BỘ ĐÁP ÁN CÂU HỎI VẤN ĐÁP</h2>

        <!-- Ô Tìm Kiếm Nhanh -->
        <div class="search-container">
            <input type="text" id="search-input" placeholder="Nhập từ khóa để tìm đáp án nhanh...">
        </div>

        <!-- Danh Sách Câu Hỏi Vấn Đáp -->
        <h3>DANH SÁCH CÂU HỎI VẤN ĐÁP</h3>
        <table class="question-table" id="question-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Câu Hỏi</th>
                    <th>Câu Trả Lời</th>
                    <th>Người Đóng Góp</th>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <th>Hành Động</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($questions)): ?>
                    <?php foreach ($questions as $index => $question): ?>
                        <tr class="question-row" data-id="<?php echo htmlspecialchars($question['id']); ?>">
                            <td class="serial-number"><?php echo $index + 1; ?></td>
                            <td 
                                contenteditable="true" 
                                class="editable" 
                                data-field="question"
                            >
                                <?php echo htmlspecialchars($question['question']); ?>
                            </td>
                            <?php if ($question['status'] === 'pending'): ?>
                                <span class="pending-label">(Chờ Duyệt)</span>
                            <?php endif; ?>
                            <td 
                                <?php if ($_SESSION['role'] === 'admin'): ?> 
                                    contenteditable="true" 
                                    class="editable" 
                                    data-field="question"
                                <?php endif; ?>
                            >
                                <?php echo htmlspecialchars($question['question']); ?>
                            </td>
                            <td 
                                <?php if ($_SESSION['role'] === 'admin'): ?> 
                                    contenteditable="true" 
                                    class="editable" 
                                    data-field="answer"
                                <?php endif; ?>
                            >
                                <?php 
                                echo !empty($question['answer']) 
                                    ? htmlspecialchars($question['answer']) 
                                    : '<em class="no-answer">Chưa có câu trả lời.</em>'; 
                                ?>
                            </td>
                            <td class="center-align"><?php echo htmlspecialchars($question['contributor'] ?? 'Ẩn danh'); ?></td>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <td class="actions">
                                    <?php if ($question['status'] === 'pending'): ?>
                                        <!-- Nút Duyệt -->
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn duyệt câu hỏi này?');">
                                            <input type="hidden" name="approve_id" value="<?php echo $question['id']; ?>">
                                            <button type="submit" class="approve-button">Duyệt</button>
                                        </form>
                                    <?php endif; ?>
                                    <!-- Nút Xóa -->
                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $question['id']; ?>">
                                        <button type="submit" class="delete-button">Xóa</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $_SESSION['role'] === 'admin' ? 5 : 4; ?>" class="no-data">
                            Không có câu hỏi nào hiện tại.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Nút Thêm Câu Hỏi Mới -->
        <div class="add-question-toggle">
            <button id="toggle-add-question">BỔ SUNG THÊM CÂU HỎI CHƯA CÓ</button>
        </div>

        <!-- Form Thêm Câu Hỏi và Đáp Án (ẩn ban đầu) -->
        <div class="add-question-container" id="add-question-container" style="display: none;">
            <form method="POST" action="" class="add-question-form">
                <div class="form-group">
                    <label for="question">Câu Hỏi:</label>
                    <input 
                        type="text" 
                        name="question" 
                        id="question" 
                        placeholder="Mời đạo hữu nhập câu hỏi vào đây..." 
                        required 
                        maxlength="255"
                    >
                </div>
                <div class="form-group">
                    <label for="answer">Đáp Án:</label>
                    <input 
                        type="text" 
                        name="answer" 
                        id="answer" 
                        placeholder="Mời đạo hữu nhập đáp án vào đây..." 
                        required 
                        maxlength="255"
                    >
                </div>
                <button type="submit" name="add_question" class="submit-button">BỔ SUNG</button>
            </form>
        </div>
    </div>
    
    <!-- Hiển thị bảng xếp hạng -->
    <div class="leaderboard">
        <h2>CÔNG THẦN BẢNG</h2>
        <div class="podium-container">
            <?php if (!empty($top_contributors)): ?>
                <?php foreach ($top_contributors as $index => $contributor): ?>
                    <div class="podium-item rank-<?php echo $index + 1; ?>" data-contributor="<?php echo htmlspecialchars($contributor['contributor']); ?>">
                        <div class="rank">Hạng <?php echo $index + 1; ?></div>
                        <div class="contributor">
                            <h3><?php echo htmlspecialchars($contributor['contributor']); ?></h3>
                            <p><?php echo $contributor['total_contributions']; ?> câu hỏi</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Không có người đóng góp nào.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Footer -->
<div class="footer">
    <p>&copy; 2025 Bổ Thiên Các. All rights reserved.</p>
</div>
</body>
</html>