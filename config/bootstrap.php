<?php
declare(strict_types=1);

/* =========================================================
 *  SESIÓN — configurar ANTES de session_start()
 *  Cookie accesible en TODA la ruta del proyecto
 * ========================================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,      // cámbialo a true si usas HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

date_default_timezone_set('America/Mexico_City');

define('BASE_URL',  '/monarka');   // 👈 Ajusta si tu carpeta se llama distinto
define('BASE_PATH', realpath(__DIR__ . '/..'));

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
