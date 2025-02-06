<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/script.js"></script>

<?php
include 'header.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require 'db.php';
?>

<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = $_POST['question'];
    $answer = $_POST['answer'];

    if (!empty($question) && !empty($answer)) {
        $stmt = $pdo->prepare("INSERT INTO questions (question, answer) VALUES (?, ?)");
        $stmt->execute([$question, $answer]);
        echo "Câu hỏi đã được thêm thành công!";
    } else {
        echo "Vui lòng nhập cả câu hỏi và câu trả lời.";
    }
}
?>
<form method="POST" action="">
    <label for="question">Câu hỏi:</label><br>
    <input type="text" name="question" id="question" required><br><br>
    <label for="answer">Câu trả lời:</label><br>
    <textarea name="answer" id="answer" required></textarea><br><br>
    <button type="submit">Thêm</button>
</form>