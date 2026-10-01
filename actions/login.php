<?php
require_once __DIR__ . '/../config/bootstrap.php';

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

$stmt = db()->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// ✅ Comparación en texto plano (sin hash)
if ($user && $pass === $user['password']) {
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    redirect('admin/index.php');
} else {
    header('Location: ' . url('admin/login.php?error=1'));
}
exit;