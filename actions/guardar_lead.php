<?php
require_once __DIR__ . '/../config/bootstrap.php';

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(419);
    die('Token CSRF inválido.');
}

$name          = trim($_POST['name'] ?? '');
$email         = trim($_POST['email'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$propertyTitle = trim($_POST['property_title'] ?? '') ?: 'Consulta General';
$message       = trim($_POST['message'] ?? '');

try {
    $stmt = db()->prepare("INSERT INTO leads (name, email, phone, property_title, message, agent, status, created_at)
                           VALUES (?, ?, ?, ?, ?, 'Carlos Mendoza', 'Pendiente', NOW())");
    $stmt->execute([$name, $email, $phone, $propertyTitle, $message]);
    flash('¡Gracias! Un asesor te contactará pronto.');
} catch (Throwable $e) {
    flash('Error al guardar: ' . $e->getMessage(), 'error');
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('index.php')));
exit;