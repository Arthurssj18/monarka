<?php
declare(strict_types=1);

/* =========================================================
 *  FUNCIONES AUXILIARES BÁSICAS
 * ========================================================= */

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function money($amount): string
{
    return '$' . number_format((float)$amount, 0, '.', ',') . ' MXN';
}

/* =========================================================
 *  SESIÓN / AUTENTICACIÓN
 * ========================================================= */

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('admin/login.php');
    }
}

function currentUser(): ?array
{
    if (!isLoggedIn()) return null;
    return [
        'id'   => (int)$_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'Usuario',
        'role' => $_SESSION['user_role'] ?? 'Asesor',
    ];
}

/* =========================================================
 *  CONFIGURACIÓN GLOBAL DEL SITIO (tabla settings)
 * ========================================================= */

function getSettings(): array
{
    static $settings = null;
    if ($settings !== null) return $settings;

    $defaults = [
        'company_name' => 'LuxeSpace Inmobiliaria',
        'site_phone'   => '+52 (55) 8000-5000',
        'site_email'   => 'contacto@luxespace.com',
        'site_address' => 'Av. Paseo de la Reforma 483, Cuauhtémoc, CDMX',
        'site_logo'    => '',
        'logo'         => '',
    ];

    try {
        $rows = db()->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        foreach ($rows as $row) {
            $defaults[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Throwable $e) { /* usa defaults */ }

    return $settings = $defaults;
}

/* =========================================================
 *  LOGO CON PRIORIDAD
 * ========================================================= */

function getLogoSrc(array $settings): ?string
{
    foreach (['site_logo', 'logo'] as $key) {
        $rel = trim((string)($settings[$key] ?? ''));
        if ($rel === '') continue;
        if (preg_match('#^https?://#i', $rel)) return $rel;

        $full = BASE_PATH . '/' . ltrim($rel, '/');
        if (is_file($full)) {
            return url($rel) . '?v=' . (@filemtime($full) ?: time());
        }
    }

    $local = BASE_PATH . '/images/logo.png';
    if (is_file($local)) {
        return url('images/logo.png') . '?v=' . (@filemtime($local) ?: time());
    }

    return null;
}

/* =========================================================
 *  IMÁGENES DE PROPIEDAD
 * ========================================================= */

function getPropertyImages(int $propertyId): array
{
    $stmt = db()->prepare("SELECT url FROM property_images WHERE property_id = ? ORDER BY id ASC");
    $stmt->execute([$propertyId]);
    return array_column($stmt->fetchAll(), 'url');
}

function imageUrl(?string $path, string $fallback = 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80'): string
{
    if (!$path) return $fallback;
    if (preg_match('#^https?://#i', $path)) return $path;
    return url($path);
}

/**
 * Prepara propiedades para pasar a JS (JSON).
 */
function propertiesForJs(array $props): array
{
    $out = [];
    foreach ($props as $p) {
        $images = getPropertyImages((int)$p['id']);
        if (!$images && !empty($p['image_main'])) $images = [$p['image_main']];
        $images = array_map(fn($u) => imageUrl($u), $images);

        $out[] = [
            'id'            => (int)$p['id'],
            'title'         => $p['title'],
            'description'   => $p['description'],
            'price'         => (float)$p['price'],
            'location'      => $p['location'],
            'city'          => $p['city'],
            'type'          => $p['type'],
            'listingType'   => $p['listing_type'],
            'bedrooms'      => (int)$p['bedrooms'],
            'bathrooms'     => (int)$p['bathrooms'],
            'areaSqm'       => (float)$p['area_sqm'],
            'floors'        => (int)($p['floors'] ?? 0),
            'hasParking'    => (int)($p['has_parking'] ?? 0),
            'parkingSpaces' => (int)($p['parking_spaces'] ?? 0),
            'agentName'     => $p['agent_name'],
            'images'        => $images,
        ];
    }
    return $out;
}

/* =========================================================
 *  SUBIDA DE ARCHIVOS
 * ========================================================= */

function uploadImages(array $files, string $subdir = 'properties'): array
{
    $result = ['ok' => true, 'files' => [], 'errors' => []];

    $allowedMime = [
        'image/jpeg'    => 'jpg',
        'image/png'     => 'png',
        'image/webp'    => 'webp',
        'image/gif'     => 'gif',
        'image/svg+xml' => 'svg',
    ];
    $maxBytes = 5 * 1024 * 1024;

    $baseDir = BASE_PATH . '/uploads/' . $subdir;
    if (!is_dir($baseDir)) {
        @mkdir($baseDir, 0775, true);
    }

    $names    = (array)($files['name']     ?? []);
    $tmpNames = (array)($files['tmp_name'] ?? []);
    $errors   = (array)($files['error']    ?? []);
    $sizes    = (array)($files['size']     ?? []);

    foreach ($names as $i => $originalName) {
        if (($errors[$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        if ($errors[$i] !== UPLOAD_ERR_OK) {
            $result['errors'][] = "Error al subir «{$originalName}».";
            continue;
        }
        if ($sizes[$i] > $maxBytes) {
            $result['errors'][] = "«{$originalName}» supera los 5 MB.";
            continue;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($tmpNames[$i]);

        if (!isset($allowedMime[$realMime])) {
            $result['errors'][] = "«{$originalName}» no es una imagen válida.";
            continue;
        }

        $ext      = $allowedMime[$realMime];
        $safeName = bin2hex(random_bytes(8)) . '-' . time() . '.' . $ext;
        $dest     = $baseDir . '/' . $safeName;

        if (move_uploaded_file($tmpNames[$i], $dest)) {
            $result['files'][] = 'uploads/' . $subdir . '/' . $safeName;
        } else {
            $result['errors'][] = "No se pudo guardar «{$originalName}».";
        }
    }

    if (!empty($result['errors'])) $result['ok'] = false;
    return $result;
}

function uploadSingleImage(array $fileArray, string $subdir): ?string
{
    if (empty($fileArray['name'])) return null;

    $normalized = [
        'name'     => [$fileArray['name']],
        'tmp_name' => [$fileArray['tmp_name']],
        'error'    => [$fileArray['error']],
        'size'     => [$fileArray['size']],
        'type'     => $fileArray['type'] ?? '',
    ];
    $r = uploadImages($normalized, $subdir);
    foreach ($r['errors'] as $err) flash($err, 'error');
    return $r['files'][0] ?? null;
}

function deleteLocalImage(?string $relativePath): void
{
    if (!$relativePath) return;
    if (preg_match('#^https?://#i', $relativePath)) return;

    $full = BASE_PATH . '/' . ltrim($relativePath, '/');
    if (is_file($full)) @unlink($full);
}

/* =========================================================
 *  CSRF
 * ========================================================= */

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . csrfToken() . '">';
}

function verifyCsrf(): void
{
    $sessionToken = $_SESSION['csrf'] ?? '';
    $postedToken  = $_POST['csrf'] ?? '';

    if ($sessionToken === '') {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        flash('Tu sesión expiró. Recarga la página e intenta de nuevo.', 'error');
        $back = $_SERVER['HTTP_REFERER'] ?? url('index.php');
        header('Location: ' . $back);
        exit;
    }

    if (!hash_equals($sessionToken, $postedToken)) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        flash('Token de seguridad inválido. Intenta enviar el formulario otra vez.', 'error');
        $back = $_SERVER['HTTP_REFERER'] ?? url('index.php');
        header('Location: ' . $back);
        exit;
    }
}

/* =========================================================
 *  FLASH MESSAGES
 * ========================================================= */

function flash(?string $msg = null, string $type = 'success')
{
    if ($msg === null) {
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
    return null;
}

/* =========================================================
 *  FOOTER DINÁMICO
 * ========================================================= */

function getFooterSettings(): array
{
    static $footer = null;
    if ($footer !== null) return $footer;

    $defaults = [
        'footer_description'          => 'Descripción breve de la empresa.',
        'footer_hours_label'          => 'Horario',
        'footer_hours'                => 'Lunes a Sábado · 09:00 - 19:00',
        'footer_address_label'        => 'Oficinas',
        'footer_address'              => 'Av. Reforma 483, CDMX',
        'footer_email_label'          => 'Correo',
        'footer_email'                => 'contacto@ejemplo.com',
        'footer_social_facebook'      => '',
        'footer_social_whatsapp'      => '',
        'footer_social_twitter'       => '',
        'footer_social_instagram'     => '',
        'footer_social_tiktok'        => '',
        'footer_social_youtube'       => '',
        'footer_social_linkedin'      => '',
        'footer_links_menu_title'     => 'Navegación',
        'footer_links_menu'           => '',
        'footer_links_services_title' => 'Servicios',
        'footer_links_services'       => '',
        'footer_legal_terms'          => 'Términos y Condiciones',
        'footer_legal_terms_url'      => '#',
        'footer_legal_privacy'        => 'Aviso de Privacidad',
        'footer_legal_privacy_url'    => '#',
        'footer_copyright'            => '© ' . date('Y') . ' Mi Empresa',
    ];

    try {
        $rows = db()->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'footer_%'")->fetchAll();
        foreach ($rows as $row) {
            $defaults[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Throwable $e) {}

    return $footer = $defaults;
}

function parseFooterLinks(string $raw): array
{
    $links = [];
    foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = array_map('trim', explode('|', $line, 2));
        if (count($parts) === 2 && $parts[0] !== '') {
            $links[] = ['label' => $parts[0], 'url' => $parts[1] ?: '#'];
        }
    }
    return $links;
}

function socialIconMap(): array
{
    return [
        'facebook'  => 'fa-facebook-f',
        'whatsapp'  => 'fa-whatsapp',
        'instagram' => 'fa-instagram',
        'tiktok'    => 'fa-tiktok',
        'youtube'   => 'fa-youtube',
        'twitter'   => 'fa-x-twitter',
        'linkedin'  => 'fa-linkedin-in',
    ];
}

/* =========================================================
 *  HEADER DINÁMICO
 * ========================================================= */

function getHeaderSettings(): array
{
    static $header = null;
    if ($header !== null) return $header;

    $defaults = [
        'header_topbar_enabled'   => '1',
        'header_location'         => '',
        'header_location_url'     => '',
        'header_hours'            => '',
        'header_phone'            => '',
        'header_phone_label'      => 'Llamar',
        'header_social_facebook'  => '',
        'header_social_whatsapp'  => '',
        'header_social_youtube'   => '',
        'header_social_tiktok'    => '',
        'header_social_instagram' => '',
        'header_social_twitter'   => '',
        'header_social_linkedin'  => '',
    ];

    try {
        $rows = db()->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'header_%'")->fetchAll();
        foreach ($rows as $row) {
            $defaults[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Throwable $e) {}

    return $header = $defaults;
}

function headerSocialIconMap(): array
{
    return [
        'facebook'  => 'fa-facebook-f',
        'whatsapp'  => 'fa-whatsapp',
        'youtube'   => 'fa-youtube',
        'tiktok'    => 'fa-tiktok',
        'instagram' => 'fa-instagram',
        'twitter'   => 'fa-x-twitter',
        'linkedin'  => 'fa-linkedin-in',
    ];
}

/* =========================================================
 *  SISTEMA DE PERMISOS POR ROL
 * ========================================================= */

function getRolePermissions(): array
{
    return [
        'Administrador' => ['*'],
        'Asesor Senior' => [
            'dashboard.view',
            'properties.view', 'properties.create', 'properties.edit', 'properties.toggle',
            'leads.view', 'leads.respond', 'leads.update_status',
            'messages.view', 'messages.mark_read',
            'users.view', 'settings.view', 'header.view', 'footer.view',
        ],
        'Asesor Elite' => [
            'dashboard.view',
            'properties.view',
            'leads.view', 'leads.respond', 'leads.update_status',
            'messages.view', 'messages.mark_read',
            'users.view', 'settings.view', 'header.view', 'footer.view',
        ],
    ];
}

function can(string $permission): bool
{
    $user = currentUser();
    if (!$user) return false;

    $role   = $user['role'] ?? 'Asesor Elite';
    $matrix = getRolePermissions();

    if (!isset($matrix[$role])) return false;
    if (in_array('*', $matrix[$role], true)) return true;

    return in_array($permission, $matrix[$role], true);
}

function requirePermission(string $permission): void
{
    if (!can($permission)) {
        flash('No tienes permiso para realizar esta acción.', 'error');
        redirect('admin/index.php');
    }
}

function isAdmin(): bool
{
    return can('*') || (currentUser()['role'] ?? '') === 'Administrador';
}

function getRoleBadgeClass(): string
{
    $role = currentUser()['role'] ?? '';
    return match ($role) {
        'Administrador' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
        'Asesor Senior' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
        'Asesor Elite'  => 'bg-sky-500/20 text-sky-400 border-sky-500/30',
        default         => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
    };
}

/* =========================================================
 *  PALETA DE COLORES DINÁMICA (COMPLETA)
 * ========================================================= */

function hexToHsl(string $hex): array
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    $r = hexdec(substr($hex, 0, 2)) / 255;
    $g = hexdec(substr($hex, 2, 2)) / 255;
    $b = hexdec(substr($hex, 4, 2)) / 255;

    $max = max($r, $g, $b); $min = min($r, $g, $b);
    $l = ($max + $min) / 2;
    $h = $s = 0;

    if ($max !== $min) {
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        switch ($max) {
            case $r: $h = ($g - $b) / $d + ($g < $b ? 6 : 0); break;
            case $g: $h = ($b - $r) / $d + 2; break;
            case $b: $h = ($r - $g) / $d + 4; break;
        }
        $h /= 6;
    }
    return [$h * 360, $s * 100, $l * 100];
}

function _hue2rgb($p, $q, $t)
{
    if ($t < 0) $t += 1;
    if ($t > 1) $t -= 1;
    if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
    if ($t < 1/2) return $q;
    if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
    return $p;
}

function hslToHex(float $h, float $s, float $l): string
{
    $h /= 360; $s /= 100; $l /= 100;
    if ($s === 0) { $r = $g = $b = $l; }
    else {
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $r = _hue2rgb($p, $q, $h + 1/3);
        $g = _hue2rgb($p, $q, $h);
        $b = _hue2rgb($p, $q, $h - 1/3);
    }
    return sprintf('#%02x%02x%02x',
        (int)round($r * 255), (int)round($g * 255), (int)round($b * 255));
}

function generatePalette(string $baseHex): array
{
    [$h, $s, $baseL] = hexToHsl($baseHex);

    $tones = [
        50  => 96,
        100 => 91,
        200 => 82,
        300 => 72,
        400 => 62,
        500 => $baseL,
        600 => max(15, $baseL - 12),
        700 => max(10, $baseL - 22),
        800 => max(8,  $baseL - 30),
        900 => max(5,  $baseL - 38),
        950 => max(3,  $baseL - 45),
    ];

    $palette = [];
    foreach ($tones as $key => $lightness) {
        $sat = $s;
        if ($key <= 100) $sat = max(30, $s - 20);
        if ($key >= 700) $sat = max(40, $s - 15);
        $palette[$key] = hslToHex($h, $sat, $lightness);
    }
    return $palette;
}

function sanitizeHex(string $hex, string $fallback = '#f59e0b'): string
{
    $hex = trim($hex);
    if (!preg_match('/^#?[0-9a-f]{6}$/i', $hex)) return $fallback;
    if ($hex[0] !== '#') $hex = '#' . $hex;
    return strtolower($hex);
}

function getAllPalettes(): array
{
    static $palettes = null;
    if ($palettes !== null) return $palettes;

    $s = getSettings();

    $map = [
        'amber'   => ['color_primary', '#f59e0b'],
        'slate'   => ['color_neutral', '#64748b'],
        'emerald' => ['color_success', '#10b981'],
        'sky'     => ['color_info',    '#0ea5e9'],
        'rose'    => ['color_danger',  '#f43f5e'],
        'violet'  => ['color_accent',  '#8b5cf6'],
    ];

    $palettes = [];
    foreach ($map as $twName => [$settingKey, $fallback]) {
        $base = sanitizeHex($s[$settingKey] ?? $fallback, $fallback);
        $palettes[$twName] = generatePalette($base);
    }
    return $palettes;
}

function getPrimaryPalette(): array
{
    return getAllPalettes()['amber'];
}

function getPrimaryColor(): string
{
    return sanitizeHex(getSettings()['color_primary'] ?? '#f59e0b');
}

function getBaseColor(string $key): string
{
    $s = getSettings();
    $map = [
        'primary' => ['color_primary', '#f59e0b'],
        'neutral' => ['color_neutral', '#64748b'],
        'success' => ['color_success', '#10b981'],
        'info'    => ['color_info',    '#0ea5e9'],
        'danger'  => ['color_danger',  '#f43f5e'],
        'accent'  => ['color_accent',  '#8b5cf6'],
    ];
    if (!isset($map[$key])) return '#000000';
    [$settingKey, $fallback] = $map[$key];
    return sanitizeHex($s[$settingKey] ?? $fallback, $fallback);
}

/* =========================================================
 *  GOOGLE MAPS — convertir link a URL embebible
 * ========================================================= */

function googleMapsEmbedUrl(?string $input): string
{
    $input = trim((string)$input);
    if ($input === '') return '';

    /* 1) Iframe completo → extraer src */
    if (preg_match('/<iframe[^>]+src=["\']([^"\']+)["\']/i', $input, $m)) {
        return html_entity_decode($m[1]);
    }

    /* 2) Ya contiene /maps/embed → usar tal cual */
    if (strpos($input, 'google.com/maps/embed') !== false) {
        return $input;
    }

    /* 3) Coordenadas @lat,lng en el link */
    if (preg_match('#@(-?\d+\.\d+),(-?\d+\.\d+)#', $input, $m)) {
        return "https://www.google.com/maps?q={$m[1]},{$m[2]}&hl=es&z=17&output=embed";
    }

    /* 4) Parámetro ?q= o ?query= */
    if (preg_match('#[?&](?:q|query)=([^&]+)#', $input, $m)) {
        return "https://www.google.com/maps?q=" . urlencode(urldecode($m[1])) . "&hl=es&z=17&output=embed";
    }

    /* 5) /maps/place/Nombre */
    if (preg_match('#/maps/place/([^/?]+)#', $input, $m)) {
        $q = urldecode(str_replace('+', ' ', $m[1]));
        return "https://www.google.com/maps?q=" . urlencode($q) . "&hl=es&z=17&output=embed";
    }

    /* 6) Link corto → no se puede resolver en servidor */
    if (preg_match('#(maps\.app\.goo\.gl|goo\.gl/maps)#i', $input)) {
        return '';
    }

    /* 7) Texto simple (dirección escrita) */
    return "https://www.google.com/maps?q=" . urlencode($input) . "&hl=es&z=17&output=embed";
}