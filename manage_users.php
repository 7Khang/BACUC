<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $hashed_password, $role]);

    echo "Người dùng đã được thêm thành công!";
}

$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1>Quản Lý Người Dùng</h1>
    <form method="POST" action="">
        <label for="username">Tên người dùng:</label><br>
        <input type="text" name="username" id="username" required><br><br>
        <label for="password">Mật khẩu:</label><br>
        <input type="password" name="password" id="password" required><br><br>
        <label for="role">Quyền:</label><br>
        <select name="role" id="role" required>
            <option value="admin">Admin</option>
            <option value="guest">Guest</option>
        </select><br><br>
        <button type="submit">Thêm Người Dùng</button>
    </form>

    <h2>Danh Sách Người Dùng</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Tên Người Dùng</th>
            <th>Quyền</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>