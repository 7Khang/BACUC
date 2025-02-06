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

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
$stmt->execute([$id]);

header("Location: list_questions.php");
exit;
?>
