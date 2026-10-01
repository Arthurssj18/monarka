<?php
declare(strict_types=1);

/* ============ HELPERS BÁSICOS ============ */
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

/* ============ SESIÓN / AUTH ============ */
function isLoggedIn(): bool { return !empty($_SESSION['user_id']); }

function requireLogin(): void
{
    if (!isLoggedIn()) redirect('admin/login.php');
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

/* ============ SETTINGS GLOBALES ============ */
function getSettings(): array
{
    static $s = null;
    if ($s !== null) return $s;

    $defaults = [
        'company_name' => 'LuxeSpace Inmobiliaria',
        'site_phone'   => '+52 (55) 8000-5000',
        'site_email'   => 'contacto@luxespace.com',
        'site_address' => 'Av. Paseo de la Reforma 483, CDMX',
        'site_logo'    => '',
        'logo'         => '',
    ];

    try {
        foreach (db()->query("SELECT setting_key, setting_value FROM settings") as $row) {
            $defaults[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Throwable $e) {}

    return $s = $defaults;
}

/* ============ LOGO CON PRIORIDAD ============ */
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

/* ============ IMÁGENES DE PROPIEDAD ============ */
function getPropertyImages(int $id): array
{
    $stmt = db()->prepare("SELECT url FROM property_images WHERE property_id = ? ORDER BY id ASC");
    $stmt->execute([$id]);
    return array_column($stmt->fetchAll(), 'url');
}

function imageUrl(?string $path, string $fallback = 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80'): string
{
    if (!$path) return $fallback;
    if (preg_match('#^https?://#i', $path)) return $path;
    return url($path);
}

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
            'hasParking'    => (int)($p['has_parking'] ?? 0),
            'parkingSpaces' => (int)($p['parking_spaces'] ?? 0),
            'agentName'     => $p['agent_name'],
            'images'        => $images,
        ];
    }
    return $out;
}

/* ============ SUBIDA DE IMÁGENES ============ */
function uploadImages(array $files, string $subdir = 'properties'): array
{
    $result = ['ok' => true, 'files' => [], 'errors' => []];

    $allowed = [
        'image/jpeg'    => 'jpg',
        'image/png'     => 'png',
        'image/webp'    => 'webp',
        'image/gif'     => 'gif',
        'image/svg+xml' => 'svg',
    ];
    $max = 5 * 1024 * 1024;

    $dir = BASE_PATH . '/uploads/' . $subdir;
    if (!is_dir($dir)) @mkdir($dir, 0775, true);

    $names  = (array)($files['name']     ?? []);
    $tmps   = (array)($files['tmp_name'] ?? []);
    $errs   = (array)($files['error']    ?? []);
    $sizes  = (array)($files['size']     ?? []);

    foreach ($names as $i => $orig) {
        if (($errs[$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        if ($errs[$i] !== UPLOAD_ERR_OK) { $result['errors'][] = "Error al subir $orig"; continue; }
        if ($sizes[$i] > $max)            { $result['errors'][] = "$orig supera 5MB"; continue; }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($tmps[$i]);
        if (!isset($allowed[$mime])) { $result['errors'][] = "$orig no es imagen válida"; continue; }

        $ext  = $allowed[$mime];
        $name = bin2hex(random_bytes(8)) . '-' . time() . '.' . $ext;
        $dest = $dir . '/' . $name;

        if (move_uploaded_file($tmps[$i], $dest)) {
            $result['files'][] = 'uploads/' . $subdir . '/' . $name;
        } else {
            $result['errors'][] = "No se pudo guardar $orig";
        }
    }

    if (!empty($result['errors'])) $result['ok'] = false;
    return $result;
}

function uploadSingleImage(array $file, string $subdir): ?string
{
    if (empty($file['name'])) return null;
    $norm = [
        'name'     => [$file['name']],
        'tmp_name' => [$file['tmp_name']],
        'error'    => [$file['error']],
        'size'     => [$file['size']],
    ];
    $r = uploadImages($norm, $subdir);
    foreach ($r['errors'] as $err) flash($err, 'error');
    return $r['files'][0] ?? null;
}

function deleteLocalImage(?string $path): void
{
    if (!$path || preg_match('#^https?://#i', $path)) return;
    $full = BASE_PATH . '/' . ltrim($path, '/');
    if (is_file($full)) @unlink($full);
}

/* ============ CSRF ============ */
function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . csrfToken() . '">';
}
function verifyCsrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        die('Token CSRF inválido.');
    }
}

/* ============ FLASH ============ */
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

/* ============ HEADER DINÁMICO ============ */
function getHeaderSettings(): array
{
    static $h = null;
    if ($h !== null) return $h;

    $d = [
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
        foreach (db()->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'header_%'") as $r) {
            $d[$r['setting_key']] = $r['setting_value'];
        }
    } catch (Throwable $e) {}
    return $h = $d;
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

/* ============ FOOTER DINÁMICO ============ */
function getFooterSettings(): array
{
    static $f = null;
    if ($f !== null) return $f;

    $d = [
        'footer_description'          => 'Descripción breve de la empresa.',
        'footer_hours_label'          => 'Horario',
        'footer_hours'                => '',
        'footer_address_label'        => 'Oficinas',
        'footer_address'              => '',
        'footer_email_label'          => 'Correo',
        'footer_email'                => '',
        'footer_social_facebook'      => '',
        'footer_social_twitter'       => '',
        'footer_social_instagram'     => '',
        'footer_social_tiktok'        => '',
        'footer_social_youtube'       => '',
        'footer_social_linkedin'      => '',
        'footer_links_menu_title'     => 'Navegación',
        'footer_links_menu'           => '',
        'footer_links_services_title' => 'Servicios',
        'footer_links_services'       => '',
        'footer_legal_terms'          => '',
        'footer_legal_terms_url'      => '#',
        'footer_legal_privacy'        => '',
        'footer_legal_privacy_url'    => '#',
        'footer_copyright'            => '© ' . date('Y') . ' Mi Empresa',
    ];
    try {
        foreach (db()->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'footer_%'") as $r) {
            $d[$r['setting_key']] = $r['setting_value'];
        }
    } catch (Throwable $e) {}
    return $f = $d;
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
        'twitter'   => 'fa-x-twitter',
        'instagram' => 'fa-instagram',
        'tiktok'    => 'fa-tiktok',
        'youtube'   => 'fa-youtube',
        'linkedin'  => 'fa-linkedin-in',
    ];
}