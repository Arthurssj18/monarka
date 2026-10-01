<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    requirePermission('header.edit');

    if (($_POST['action'] ?? '') === 'save_header') {
        $fields = [
            'header_location', 'header_location_url', 'header_hours', 'header_phone', 'header_phone_label',
            'header_social_facebook', 'header_social_whatsapp', 'header_social_youtube',
            'header_social_tiktok', 'header_social_instagram', 'header_social_twitter', 'header_social_linkedin',
        ];
        $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($fields as $k) $stmt->execute([$k, trim($_POST[$k] ?? '')]);
        $stmt->execute(['header_topbar_enabled', isset($_POST['header_topbar_enabled']) ? '1' : '0']);
        flash('Encabezado guardado.');
        redirect('admin/header.php');
    }
}

$pageTitle = 'Encabezado';
$activeTab = 'header';
require_once __DIR__ . '/includes/header.php';

$canEdit = can('header.edit');
$h   = getHeaderSettings();
$soc = headerSocialIconMap();
?>

<?php if (!$canEdit): ?>
    <div class="mb-6 p-4 rounded-xl border bg-sky-50 border-sky-200 text-sky-800 text-sm flex items-center gap-2">
        <i class="fa-solid fa-eye"></i>
        <span><strong>Modo solo lectura:</strong> Puedes ver la configuración del encabezado, pero no modificarla.</span>
    </div>
<?php endif; ?>

<form method="POST" class="space-y-6">
    <?= csrfField() ?><input type="hidden" name="action" value="save_header">

    <!-- Visibilidad -->
    <div class="bg-white p-6 rounded-2xl border">
        <label class="flex items-center gap-3 cursor-pointer <?= $canEdit ? '' : 'cursor-not-allowed opacity-70' ?>">
            <input type="checkbox" name="header_topbar_enabled" value="1"
                   <?= ($h['header_topbar_enabled'] ?? '1') === '1' ? 'checked' : '' ?>
                   <?= $canEdit ? '' : 'disabled' ?>
                   class="w-5 h-5 accent-amber-500">
            <span class="font-bold text-sm">
                <i class="fa-solid fa-toggle-on text-amber-500"></i> Mostrar barra superior (topbar)
            </span>
        </label>
    </div>

    <!-- Ubicación y Horario -->
    <div class="bg-white p-6 rounded-2xl border space-y-4">
        <h3 class="font-bold"><i class="fa-solid fa-location-dot text-amber-500"></i> Ubicación y Horario</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" name="header_location" value="<?= e($h['header_location']) ?>" placeholder="Dirección"
                   <?= $canEdit ? '' : 'readonly' ?>
                   class="bg-slate-50 border rounded-xl p-3 text-sm">
            <input type="url" name="header_location_url" value="<?= e($h['header_location_url']) ?>" placeholder="https://maps..."
                   <?= $canEdit ? '' : 'readonly' ?>
                   class="bg-slate-50 border rounded-xl p-3 text-sm">
        </div>
        <input type="text" name="header_hours" value="<?= e($h['header_hours']) ?>" placeholder="Horario"
               <?= $canEdit ? '' : 'readonly' ?>
               class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
    </div>

    <!-- Teléfono -->
    <div class="bg-white p-6 rounded-2xl border space-y-4">
        <h3 class="font-bold"><i class="fa-solid fa-phone text-amber-500"></i> Teléfono</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" name="header_phone" value="<?= e($h['header_phone']) ?>" placeholder="+5215512345678"
                   <?= $canEdit ? '' : 'readonly' ?>
                   class="bg-slate-50 border rounded-xl p-3 text-sm">
            <input type="text" name="header_phone_label" value="<?= e($h['header_phone_label']) ?>" placeholder="Llamar"
                   <?= $canEdit ? '' : 'readonly' ?>
                   class="bg-slate-50 border rounded-xl p-3 text-sm">
        </div>
    </div>

    <!-- Redes Sociales -->
    <div class="bg-white p-6 rounded-2xl border space-y-4">
        <h3 class="font-bold"><i class="fa-solid fa-share-nodes text-amber-500"></i> Redes Sociales</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php
            $labels = [
                'facebook'  => ['Facebook',    'fa-facebook-f'],
                'whatsapp'  => ['WhatsApp',    'fa-whatsapp'],
                'youtube'   => ['YouTube',     'fa-youtube'],
                'tiktok'    => ['TikTok',      'fa-tiktok'],
                'instagram' => ['Instagram',   'fa-instagram'],
                'twitter'   => ['Twitter / X', 'fa-x-twitter'],
                'linkedin'  => ['LinkedIn',    'fa-linkedin-in'],
            ];
            foreach ($labels as $k => $info):
                [$lbl, $icon] = $info;
                $field = 'header_social_' . $k;
            ?>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">
                        <i class="fa-brands <?= $icon ?> mr-1 <?= $k === 'whatsapp' ? 'text-emerald-500' : '' ?>"></i> <?= $lbl ?>
                    </label>
                    <input type="text" name="<?= $field ?>"
                           value="<?= e($h[$field] ?? '') ?>"
                           placeholder="https://..."
                           <?= $canEdit ? '' : 'readonly' ?>
                           class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($canEdit): ?>
        <div class="sticky bottom-4 bg-white p-4 rounded-2xl border shadow-lg flex justify-end">
            <button class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-8 py-3 rounded-xl text-sm">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Encabezado
            </button>
        </div>
    <?php endif; ?>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>