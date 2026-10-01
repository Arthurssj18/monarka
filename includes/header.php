<?php
$settings   = getSettings();
$header     = getHeaderSettings();
$socialMap  = headerSocialIconMap();
$pageTitle  = $pageTitle  ?? 'Inicio';
$activePage = $activePage ?? 'inicio';
$user       = currentUser();

/* ============ PALETAS DE COLORES DINÁMICAS ============ */
$palettes = getAllPalettes();

$navItems = [
    'inicio'      => ['Inicio',      'index.php'],
    'propiedades' => ['Propiedades', 'propiedades.php'],
    'servicios'   => ['Servicios',   'servicios.php'],
    'nosotros'    => ['Nosotros',    'nosotros.php'],
    'contacto'    => ['Contacto',    'contacto.php'],
];

$logoSrc = getLogoSrc($settings);

$faviconFile = BASE_PATH . '/images/favicon.png';
$hasFavicon  = is_file($faviconFile);

$showTopbar = ($header['header_topbar_enabled'] ?? '1') === '1';
$hasLocation = !empty($header['header_location']);
$hasHours    = !empty($header['header_hours']);
$hasPhone    = !empty($header['header_phone']);
$hasSocials  = false;
foreach ($socialMap as $k => $i) {
    if (!empty($header['header_social_' . $k])) { $hasSocials = true; break; }
}
$topbarHasContent = $hasLocation || $hasHours || $hasPhone || $hasSocials;
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= e($pageTitle) ?> | <?= e($settings['company_name']) ?></title>
    <title><?= e($pageTitle) ?> | <?= e($settings['company_name']) ?></title>

    <?php if ($hasFavicon): ?>
        <link rel="icon" href="<?= url('images/favicon.png') ?>?v=<?= filemtime($faviconFile) ?>">
    <?php else: ?>
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='75' font-size='75'>🏢</text></svg>">
    <?php endif; ?>

    <!-- ============ TAILWIND CDN ============ -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ============ CONFIGURACIÓN DINÁMICA DE COLORES ============ -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        amber:   <?= json_encode($palettes['amber'],   JSON_UNESCAPED_SLASHES) ?>,
                        slate:   <?= json_encode($palettes['slate'],   JSON_UNESCAPED_SLASHES) ?>,
                        emerald: <?= json_encode($palettes['emerald'], JSON_UNESCAPED_SLASHES) ?>,
                        sky:     <?= json_encode($palettes['sky'],     JSON_UNESCAPED_SLASHES) ?>,
                        rose:    <?= json_encode($palettes['rose'],    JSON_UNESCAPED_SLASHES) ?>,
                        violet:  <?= json_encode($palettes['violet'],  JSON_UNESCAPED_SLASHES) ?>
                    }
                }
            }
        };
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .modal-backdrop { background-color: rgba(15,23,42,.75); backdrop-filter: blur(4px); }
        .modal-backdrop.flex { display: flex !important; }
        .line-clamp-1 { display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden; }
        .line-clamp-2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen selection:bg-amber-500 selection:text-white">

    <?php if ($showTopbar && $topbarHasContent): ?>
    <!-- ============ TOP BAR ============ -->
    <div id="top-bar" class="bg-slate-900 text-slate-300 text-xs py-2 px-4 flex flex-wrap justify-between items-center border-b border-slate-800 sticky top-0 z-50 gap-3">
        <div class="flex items-center gap-5 flex-wrap">
            <?php if ($hasLocation): ?>
                <a href="<?= e($header['header_location_url'] ?: '#') ?>"
                   <?= $header['header_location_url'] ? 'target="_blank" rel="noopener"' : '' ?>
                   class="flex items-center gap-1.5 hover:text-amber-400">
                    <i class="fa-solid fa-location-dot text-amber-500"></i>
                    <span><?= e($header['header_location']) ?></span>
                </a>
            <?php endif; ?>

            <?php if ($hasHours): ?>
                <div class="hidden md:flex items-center gap-1.5 text-slate-400">
                    <i class="fa-solid fa-clock text-amber-500"></i>
                    <span><?= e($header['header_hours']) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex items-center gap-3">
            <?php if ($hasPhone): ?>
                <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $header['header_phone'])) ?>"
                   class="flex items-center gap-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-3 py-1 rounded-md">
                    <i class="fa-solid fa-phone text-[10px]"></i>
                    <span><?= e($header['header_phone_label'] ?: 'Llamar') ?></span>
                </a>
            <?php endif; ?>

            <?php if ($hasSocials): ?>
                <div class="flex items-center gap-1">
                    <?php foreach ($socialMap as $k => $i):
                        $u = trim($header['header_social_' . $k] ?? '');
                        if (!$u) continue;
                    ?>
                        <a href="<?= e($u) ?>" target="_blank" rel="noopener"
                           class="w-7 h-7 rounded-full bg-slate-800 hover:bg-amber-500 text-slate-300 hover:text-slate-950 flex items-center justify-center transition-colors"
                           title="<?= e(ucfirst($k)) ?>">
                            <i class="fa-brands <?= $i ?> text-[11px]"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============ HEADER PRINCIPAL ============ -->
    <header id="public-header"
            class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky <?= ($showTopbar && $topbarHasContent) ? 'top-9' : 'top-0' ?> z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex justify-between items-center">

            <!-- Logo -->
            <a href="<?= url('index.php') ?>" class="flex items-center gap-3 group">
                <?php if ($logoSrc): ?>
                    <img src="<?= e($logoSrc) ?>"
                         alt="<?= e($settings['company_name']) ?>"
                         class="h-14 w-auto max-w-[180px] object-contain group-hover:scale-105 transition-transform">
                <?php else: ?>
                    <div class="bg-gradient-to-tr from-amber-600 to-amber-400 text-slate-950 p-2.5 rounded-xl shadow-lg shadow-amber-500/20 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-building-user text-xl"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <span class="text-xl font-black text-slate-900 tracking-tight block">
                        <?= e($settings['company_name']) ?>
                    </span>
                    <span class="text-[10px] uppercase tracking-widest text-slate-400 block font-bold">Inmobiliaria</span>
                </div>
            </a>

            <!-- Navegación -->
            <nav class="hidden md:flex items-center gap-8">
                <?php foreach ($navItems as $key => [$label, $file]): $a = $activePage === $key; ?>
                    <a href="<?= url($file) ?>"
                       class="text-sm font-semibold py-1 border-b-2 transition-colors <?= $a
                            ? 'text-amber-600 border-amber-600'
                            : 'text-slate-600 hover:text-slate-900 border-transparent' ?>">
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <!-- CTA -->
            <div class="flex items-center gap-3">
                <a href="<?= url('contacto.php') ?>"
                   class="hidden lg:flex items-center gap-2 bg-slate-900 text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-amber-600 hover:text-slate-950 transition-all shadow-sm">
                    <i class="fa-solid fa-phone text-amber-400"></i>
                    <span>Contactar Asesor</span>
                </a>
            </div>
        </div>
    </header>

    <main class="flex-grow">