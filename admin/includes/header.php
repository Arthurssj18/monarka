<?php
require_once __DIR__ . '/auth.php';
$settings = getSettings();
$user = currentUser();
$pageTitle = $pageTitle ?? 'Panel';
$activeTab = $activeTab ?? 'dashboard';

/* =========================================================
 *  CONTADORES
 * ========================================================= */
$countProps      = (int) db()->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$countLeads      = (int) db()->query("SELECT COUNT(*) FROM leads WHERE status='Pendiente'")->fetchColumn();
$countUnreadMsgs = (int) db()->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();

/* =========================================================
 *  LOGO DEL PANEL
 * ========================================================= */
$adminLogoFile  = BASE_PATH . '/images/logo-admin.png';
$publicLogoFile = BASE_PATH . '/images/logo.png';
$hasLogo  = is_file($adminLogoFile) || is_file($publicLogoFile);
$logoPath = is_file($adminLogoFile) ? 'images/logo-admin.png' : 'images/logo.png';

/* =========================================================
 *  NAVEGACIÓN CON FILTROS POR PERMISO
 * ========================================================= */
$adminNav = [
    'dashboard'  => ['Dashboard',           'index.php',      'fa-gauge-high',       'dashboard.view'],
    'properties' => ['Propiedades (CRUD)',  'properties.php', 'fa-house',            'properties.view'],
    'leads'      => ['Solicitudes',         'leads.php',      'fa-clipboard-list',   'leads.view'],
    'users'      => ['Usuarios / Personal', 'users.php',      'fa-users',            'users.view'],
    'messages'   => ['Bandeja Mensajes',    'messages.php',   'fa-envelope-open-text','messages.view'],
    'header'     => ['Encabezado',          'header.php',     'fa-heading',          'header.view'],
    'footer'     => ['Pie de Página',       'footer.php',     'fa-shoe-prints',      'footer.view'],
    'settings'   => ['Configuración',       'settings.php',   'fa-sliders',          'settings.view'],
];

$titles = [
    'dashboard'  => 'Resumen General del Sistema',
    'properties' => 'Gestión de Propiedades (CRUD)',
    'leads'      => 'Solicitudes y Prospectos de Clientes',
    'users'      => 'Directorio de Personal y Asesores',
    'messages'   => 'Bandeja de Entrada de Consultas',
    'header'     => 'Configuración del Encabezado',
    'footer'     => 'Configuración del Pie de Página',
    'settings'   => 'Configuración General de la Empresa',
];

$flash = flash();
$role  = $user['role'] ?? 'Asesor';
$roleBadgeClass = getRoleBadgeClass();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Panel Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .modal-backdrop { background-color: rgba(15,23,42,.75); backdrop-filter: blur(4px); }
        .modal-backdrop.flex { display: flex !important; }
        .line-clamp-1 { display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex">

<!-- =========================================================
 *  SIDEBAR
 * ========================================================= -->
<aside class="w-64 bg-slate-900 text-slate-300 flex-col justify-between hidden md:flex border-r border-slate-800 min-h-screen sticky top-0">
    <div>
        <div class="p-6 border-b border-slate-800 flex items-center gap-3">
            <?php if ($hasLogo && is_file(BASE_PATH . '/' . $logoPath)): ?>
                <img src="<?= url($logoPath) ?>?v=<?= filemtime(BASE_PATH . '/' . $logoPath) ?>"
                     class="h-11 w-auto max-w-[120px] object-contain rounded-lg bg-white/5 p-1"
                     alt="<?= e($settings['company_name']) ?>">
            <?php else: ?>
                <div class="bg-amber-500 p-2 rounded-xl text-slate-950 font-bold shrink-0">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <span class="font-bold text-white text-sm block truncate">Panel Admin</span>
                <span class="text-[10px] text-emerald-400 font-semibold block">● Sesión Activa</span>
            </div>
        </div>

        <nav class="p-4 space-y-1">
            <?php foreach ($adminNav as $key => [$label, $file, $icon, $perm]):
                if (!can($perm)) continue;
                $a = $activeTab === $key;
            ?>
                <a href="<?= url('admin/' . $file) ?>"
                   class="w-full flex items-center justify-between px-3.5 py-3 rounded-xl text-xs font-semibold transition-all
                          <?= $a
                                ? 'bg-amber-500 text-slate-950'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid <?= $icon ?>"></i> <?= e($label) ?>
                    </span>

                    <?php if ($key === 'properties' && $countProps > 0): ?>
                        <span class="bg-slate-950 text-white px-2 py-0.5 rounded-full text-[10px]"><?= $countProps ?></span>
                    <?php elseif ($key === 'leads' && $countLeads > 0): ?>
                        <span class="bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full text-[10px]"><?= $countLeads ?></span>
                    <?php elseif ($key === 'messages' && $countUnreadMsgs > 0): ?>
                        <span class="bg-rose-500/20 text-rose-400 px-2 py-0.5 rounded-full text-[10px] font-bold animate-pulse">
                            <?= $countUnreadMsgs ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="p-4 border-t border-slate-800">
        <a href="<?= url('admin/logout.php') ?>"
           class="flex items-center gap-2 text-rose-400 hover:text-rose-300 text-xs font-semibold p-2.5 rounded-lg hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-right-from-bracket"></i> <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>

<!-- =========================================================
 *  CONTENIDO
 * ========================================================= -->
<div class="flex-1 flex flex-col min-w-0">

    <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center sticky top-0 z-30">
        <h2 class="text-xl font-bold text-slate-900"><?= e($titles[$activeTab] ?? 'Panel Administrativo') ?></h2>
        <div class="flex items-center gap-3">
            <a href="<?= url('index.php') ?>" class="text-xs text-slate-500 hover:text-amber-600 font-semibold">
                <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> Ver sitio
            </a>
            <div class="flex items-center gap-2 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                <span class="text-xs text-slate-500"><?= e($user['name']) ?></span>
                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full border <?= $roleBadgeClass ?>">
                    <?= e($role) ?>
                </span>
            </div>
        </div>
    </header>

    <div class="p-8 flex-grow">
        <?php if ($flash): ?>
            <div class="mb-6 p-4 rounded-xl border text-sm font-medium flex items-center gap-2
                        <?= $flash['type'] === 'error'
                            ? 'bg-rose-50 border-rose-200 text-rose-700'
                            : 'bg-emerald-50 border-emerald-200 text-emerald-700' ?>">
                <i class="fa-solid <?= $flash['type'] === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check' ?>"></i>
                <span><?= e($flash['msg']) ?></span>
            </div>
        <?php endif; ?>