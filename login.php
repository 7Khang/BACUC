<?php
session_start();
require 'db.php';

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    // Lưu tên người dùng vào session để hiển thị lại
    $_SESSION['saved_username'] = $username;

    // Kiểm tra nếu tên người dùng là admin
    if ($username === 'admin') {
        if (isset($_POST['password'])) {
            $password = $_POST['password'];
            // Kiểm tra thông tin đăng nhập
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Đăng nhập thành công với quyền Admin
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = 'admin';
                $_SESSION['username'] = $username; // Lưu tên đăng nhập
                header("Location: daidien.php"); // Chuyển hướng đến trang đại diện
                exit;
            } else {
                $login_error = "Mật khẩu không đúng!";
            }
        } else {
            // Hiển thị ô mật khẩu nếu chưa nhập
            $show_password = true;
        }
    } else {
        // Đăng nhập với quyền Guest
        $_SESSION['user_id'] = null;
        $_SESSION['role'] = 'guest';
        $_SESSION['username'] = $username; // Lưu tên đăng nhập
        header("Location: daidien.php"); // Chuyển hướng đến trang đại diện
        exit;
    }
}
?>
<!-- Liên kết CSS -->
<link rel="stylesheet" href="assets/css/login.css">

<div class="center-container">
    <!-- Phần văn bản mở đầu -->
    <div class="intro-text">
        <p class="white-text">ĐẠO HỮU ĐANG BƯỚC VÀO LINH MÔN CỦA</p>
        <p class="highlight">BỔ THIÊN CÁC</p>
    </div>

    <!-- Box đăng nhập -->
    <div class="auth-box">
        <p class="subtext">HÃY ĐỂ LẠI CAO DANH QUÝ TÍNH</p>
        <?php if (isset($login_error)): ?>
            <p class="error"><?php echo htmlspecialchars($login_error); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Ô nhập tên người dùng -->
            <input type="text" name="username" placeholder="NHẬP CAO DANH QUÝ TÍNH" required 
                   value="<?php echo htmlspecialchars($_SESSION['saved_username'] ?? ''); ?>">

            <!-- Ô mật khẩu (hiển thị nếu tên người dùng là admin) -->
            <?php if (isset($show_password)): ?>
                <input type="password" name="password" placeholder="Mật khẩu" required>
            <?php endif; ?>

            <button type="submit">TIẾN VÀO NỘI MÔN</button>
        </form>
    </div>
</div>