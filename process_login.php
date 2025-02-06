<?php
session_start();

// Lấy giá trị từ form
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Lưu tên đăng nhập vào session để hiển thị lại
$_SESSION['saved_username'] = $username;

// Kiểm tra thông tin đăng nhập (giả sử kiểm tra với cơ sở dữ liệu)
if ($username === "admin" && $password === "123456") {
    // Đăng nhập thành công
    $_SESSION['username'] = $username; // Lưu tên đăng nhập chính thức
    header("Location: noimon.php");
    exit;
} else {
    // Đăng nhập thất bại, chuyển hướng về trang đăng nhập
    $_SESSION['error_message'] = "Tên đăng nhập hoặc mật khẩu không đúng!";
    header("Location: login.php");
    exit;
}
?>