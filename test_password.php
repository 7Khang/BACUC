<?php
$password = 'yourpassword'; // Thay thế bằng mật khẩu bạn đã sử dụng
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "Mật khẩu đã mã hóa: " . $hashed_password;
?>