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
    $id = $_POST['id'];
    $question = $_POST['question'];
    $answer = $_POST['answer'];

    $stmt = $pdo->prepare("UPDATE questions SET question = ?, answer = ? WHERE id = ?");
    $stmt->execute([$question, $answer, $id]);
    header("Location: list_questions.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->execute([$id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Câu Hỏi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
    <h1>Chỉnh Sửa Câu Hỏi</h1>
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?= htmlspecialchars($question['id']) ?>">
        <label for="question">Câu hỏi:</label><br>
        <input type="text" name="question" id="question" value="<?= htmlspecialchars($question['question']) ?>" required><br><br>
        <label for="answer">Câu trả lời:</label><br>
        <textarea name="answer" id="answer" required><?= htmlspecialchars($question['answer']) ?></textarea><br><br>
        <button type="submit">Lưu</button>
    </form>
</div>
</body>
</html>