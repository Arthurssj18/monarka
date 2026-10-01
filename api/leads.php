<?php
require_once __DIR__ . '/../config/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$name    = trim($data['name'] ?? '');
$email   = trim($data['email'] ?? '');
$phone   = trim($data['phone'] ?? '');
$message = trim($data['message'] ?? '');
$title   = trim($data['property_title'] ?? 'Consulta General');

if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos o email inválido']);
    exit;
}

try {
    $agent = 'Carlos Mendoza';
    if ($title && $title !== 'Consulta General') {
        $stmt = db()->prepare("SELECT agent_name FROM properties WHERE title = ? LIMIT 1");
        $stmt->execute([$title]);
        $found = $stmt->fetchColumn();
        if ($found) $agent = $found;
    }

    $stmt = db()->prepare(
        "INSERT INTO leads (name, email, phone, message, property_title, agent, status)
         VALUES (?, ?, ?, ?, ?, ?, 'Pendiente')"
    );
    $stmt->execute([$name, $email, $phone, $message, $title, $agent]);

    echo json_encode(['ok' => true, 'id' => db()->lastInsertId()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al guardar la solicitud']);
}