<?php
$pageTitle = 'Código PHP Nativo';
$activeTab = 'code';
require_once __DIR__ . '/includes/header.php';

/* Archivos permitidos para inspección */
$allowed = [
    'config/database.php',
    'config/bootstrap.php',
    'includes/functions.php',
    'includes/header.php',
    'includes/footer.php',
    'includes/modals.php',
    'includes/property-card.php',
    'api/leads.php',
    'api/messages.php',
    'admin/login.php',
    'admin/properties.php',
    'sql/schema.sql',
];

$file = $_GET['file'] ?? 'config/database.php';
if (!in_array($file, $allowed, true)) {
    $file = 'config/database.php';
}

$fullPath = BASE_PATH . '/' . $file;
$code = file_exists($fullPath) ? file_get_contents($fullPath) : '// Archivo no encontrado';
?>

<div class="bg-slate-950 text-slate-200 p-6 rounded-2xl border border-slate-800 space-y-4">
    <div class="flex justify-between items-center border-b border-slate-800 pb-4 flex-wrap gap-3">
        <div class="flex items-center gap-2 text-emerald-400 font-bold text-base">
            <i class="fa-brands fa-php text-xl"></i> Código Fuente del Proyecto
        </div>
        <span class="text-xs text-slate-400 bg-slate-900 px-3 py-1 rounded-full border border-slate-800">
            PDO + MySQL + Native Sessions
        </span>
    </div>

    <p class="text-xs text-slate-400 leading-relaxed">
        Visor de los archivos reales del proyecto. Selecciona un archivo para inspeccionar su contenido.
    </p>

    <div class="flex flex-wrap gap-2 text-xs">
        <?php foreach ($allowed as $f): ?>
            <a href="?file=<?= urlencode($f) ?>"
               class="px-3 py-1.5 rounded-lg font-bold transition-colors
                      <?= $file === $f ? 'bg-amber-500 text-slate-950' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' ?>">
                <?= e(basename($f)) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="text-xs text-slate-500 font-mono">
        <i class="fa-solid fa-folder-open mr-1"></i> <?= e($file) ?>
    </div>

    <pre class="bg-slate-900 p-4 rounded-xl text-xs text-emerald-400 overflow-auto font-mono code-scroll max-h-[600px] border border-slate-800"><code><?= e($code) ?></code></pre>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>