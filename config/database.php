<?php
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host    = 'localhost';
    $name    = 'inmobiliaria_db';
    $user    = 'root';
    $pass    = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$name;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        die('Error de conexión: ' . $e->getMessage());
    }
    return $pdo;
}