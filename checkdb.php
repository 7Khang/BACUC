<?php
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT VERSION()");
    $version = $stmt->fetchColumn();
    echo "Kết nối thành công! Phiên bản MySQL: " . $version;
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>