<?php
require_once __DIR__ . '/../config/bootstrap.php';

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(419);
    die('Token CSRF inválido.');
}

try {
    $stmt = db()->prepare("INSERT INTO messages (name, email, phone, message, created_at)
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([
        trim($_POST['name'] ?? ''),
        trim($_POST['email'] ?? ''),
        trim($_POST['phone'] ?? ''),
        trim($_POST['message'] ?? ''),
    ]);
    flash('Mensaje enviado. Te contactaremos pronto.');
} catch (Throwable $e) {
    flash('Error al enviar: ' . $e->getMessage(), 'error');
}

redirect('contacto.php');