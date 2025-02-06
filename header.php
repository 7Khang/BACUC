<?php

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['role'])) {
    // Nếu chưa đăng nhập, chuyển hướng về trang đăng nhập
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Bổ Thiên Các'; ?></title>
    <link rel="stylesheet" href="assets/css/Responsive.css">
    <link rel="stylesheet" href="assets/css/header.css">
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="header-container">
        <!-- Logo và tiêu đề -->
        <div class="logo">
            <!-- Hình logo -->
            <img src="assets/img/logo btc.png" alt="Logo Bổ Thiên Các" class="logo-image">
            <!-- Tên và mô tả -->
            <div class="logo-text">
                <p>Đại điện</p>
                <h1>BỔ THIÊN CÁC</h1>
            </div>
        </div>

        <!-- Menu điều hướng -->
        <nav class="menu">
            <ul>
                <li><a href="#van-dap">VẤN ĐÁP TÔNG MÔN</a></li>
                <li><a href="#bang-xep-hang">CÔNG THẦN BẢNG</a></li>
                <li><a href="#group-tong-mon">GROUP TÔNG MÔN</a></li>
                <li><a href="logout.php">XUẤT MÔN</a></li>
            </ul>
        </nav>

        <!-- Biểu tượng hamburger cho màn hình nhỏ -->
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</div>

<!-- JavaScript để xử lý menu hamburger -->
<script>
    document.querySelector('.hamburger').addEventListener('click', function () {
        const menu = document.querySelector('.menu ul');
        menu.classList.toggle('active'); // Thêm/xóa class 'active' để hiển thị/ẩn menu
    });
</script>