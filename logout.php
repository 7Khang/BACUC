<?php
session_start(); // Khởi tạo session
session_destroy(); // Xóa toàn bộ session
header("Location: login.php"); // Chuyển hướng về trang đăng nhập
exit;
?>