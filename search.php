<?php
session_start();
require 'db.php';

if (isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']);
    if (!empty($keyword)) {
        // Tìm kiếm theo từ khóa
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
    } else {
        // Nếu từ khóa rỗng, lấy toàn bộ câu hỏi
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
    }
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Trả về kết quả dưới dạng JSON
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}
?>