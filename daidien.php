<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/daidien.css">
    <link rel="stylesheet" href="assets/css/Responsive.css">
    <script src="assets/js/sweetalert211.js"></script>
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
        <form id="add-question-form" method="POST" action="" class="add-question-form">
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