<?php
session_start(); // Khởi tạo session
require 'db.php'; // Kết nối cơ sở dữ liệu
include 'header.php'; // Đảm bảo header.php chứa các thành phần cần thiết như kết nối DB và session.

// Hàm lấy danh sách câu hỏi
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



// Lấy danh sách câu hỏi
$questions = fetchQuestions($pdo);

// Lấy top 3 người đóng góp
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đại Điện Bổ Thiên Các</title>
    <link rel="stylesheet" href="assets/css/daidien.css">
    <link rel="stylesheet" href="assets/css/Responsive.css">
    <script src="assets/js/script.js" defer></script> <!-- Liên kết đến file script.js -->
    
</head>
<body>
<!-- Hero Section -->
<div class="hero-section">
    <video autoplay muted loop id="hero-video">
        <source src="https://img.pikbest.com/best/01/01/90/648132113624d.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h3>Chào mừng đạo hữu <?php echo htmlspecialchars($_SESSION['username'] ?? 'Ẩn danh'); ?> đến với</h3>
        <h1>ĐẠI ĐIỆN CỦA BỔ THIÊN CÁC</h1>
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
    <!-- Hiển thị thông báo -->
    <?php if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
        <div class="message <?php echo htmlspecialchars($_SESSION['message_type']); ?>">
            <?php 
                echo htmlspecialchars($_SESSION['message']); 
                unset($_SESSION['message'], $_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Phần Bộ Đáp Án Câu Hỏi Vấn Đáp -->
    <div class="section van-dap" id="van-dap">
        <h2>BỘ ĐÁP ÁN CÂU HỎI VẤN ĐÁP</h2>
        <div class="search-container">
            <input type="text" id="search-input" placeholder="Nhập từ khóa để tìm đáp án nhanh...">
            <button id="clear-search" class="clear-button">XÓA</button>
        </div>
        <h3>DANH SÁCH CÂU HỎI VẤN ĐÁP</h3>
        <table class="question-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Câu Hỏi</th>
                    <th>Câu Trả Lời</th>
                    <th>Người Đóng Góp</th>
                    <?php if ($is_admin): ?>
                        <th>Hành Động</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($questions)): ?>
                    <?php foreach ($questions as $index => $question): ?>
                    <tr data-id="<?= htmlspecialchars($question['id']) ?>">
                        <td class="serial-number"><?= $index + 1 ?></td>
                        <td 
                            class="editable" 
                            data-field="question" 
                            contenteditable="<?= $is_admin ? 'true' : 'false' ?>"
                        >
                            <?= htmlspecialchars($question['question']) ?>
                        </td>
                        <td 
                            class="editable" 
                            data-field="answer" 
                            contenteditable="<?= $is_admin ? 'true' : 'false' ?>"
                        >
                            <?= htmlspecialchars($question['answer']) ?>
                        </td>
                        <td class="center-align"><?= htmlspecialchars($question['contributor']) ?></td>
                        <?php if ($is_admin): ?>
                            <td>
                                <?php if ($question['status'] === 'pending'): ?>
                                    <span class="pending-label">Chờ Duyệt</span>
                                <?php endif; ?>
                                <form method="POST" action="noimon.php" style="display:inline;">
                                    <input type="hidden" name="approve_id" value="<?= $question['id'] ?>">
                                    <button type="submit" class="approve-button">Duyệt</button>
                                </form>
                                <form method="POST" action="noimon.php" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?= $question['id'] ?>">
                                    <button class="delete-button" data-id="<?= htmlspecialchars($question['id']) ?>">Xóa</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Không có câu hỏi nào hiện tại.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="add-question-toggle">
            <button id="toggle-add-question">BỔ SUNG THÊM CÂU HỎI CHƯA CÓ</button>
        </div>
        <div class="add-question-container" id="add-question-container" style="display: none;">
            <form id="add-question-form" method="POST" action="noimon.php" class="add-question-form">
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