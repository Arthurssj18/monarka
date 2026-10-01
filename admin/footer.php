<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    requirePermission('footer.edit');

    if (($_POST['action'] ?? '') === 'save_footer') {
        $fields = [
            'footer_description','footer_hours_label','footer_hours',
            'footer_address_label','footer_address','footer_email_label','footer_email',
            'footer_social_facebook','footer_social_whatsapp','footer_social_twitter',
            'footer_social_instagram','footer_social_tiktok','footer_social_youtube',
            'footer_social_linkedin',
            'footer_links_menu_title','footer_links_menu',
            'footer_links_services_title','footer_links_services',
            'footer_legal_terms','footer_legal_terms_url',
            'footer_legal_privacy','footer_legal_privacy_url','footer_copyright',
        ];
        $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($fields as $k) $stmt->execute([$k, trim($_POST[$k] ?? '')]);
        flash('Pie de página guardado.');
        redirect('admin/footer.php');
    }
}

$pageTitle = 'Pie de Página';
$activeTab = 'footer';
require_once __DIR__ . '/includes/header.php';

$canEdit = can('footer.edit');
$f = getFooterSettings();
?>

<?php if (!$canEdit): ?>
    <div class="mb-6 p-4 rounded-xl border bg-sky-50 border-sky-200 text-sky-800 text-sm flex items-center gap-2">
        <i class="fa-solid fa-eye"></i>
        <span><strong>Modo solo lectura:</strong> Puedes ver la configuración del pie de página, pero no modificarla.</span>
    </div>
<?php endif; ?>

<form method="POST" class="space-y-6">
    <?= csrfField() ?><input type="hidden" name="action" value="save_footer">

    <!-- Descripción + Redes -->
    <div class="bg-white p-6 rounded-2xl border space-y-4">
        <h3 class="font-bold"><i class="fa-solid fa-align-left text-amber-500"></i> Descripción y Redes Sociales</h3>

        <textarea name="footer_description" rows="3"
                  <?= $canEdit ? '' : 'readonly' ?>
                  class="w-full bg-slate-50 border rounded-xl p-3 text-sm"><?= e($f['footer_description']) ?></textarea>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
            <?php
            $labels = [
                'facebook'  => ['Facebook',    'fa-facebook-f'],
                'whatsapp'  => ['WhatsApp',    'fa-whatsapp'],
                'instagram' => ['Instagram',   'fa-instagram'],
                'tiktok'    => ['TikTok',      'fa-tiktok'],
                'youtube'   => ['YouTube',     'fa-youtube'],
                'twitter'   => ['Twitter / X', 'fa-x-twitter'],
                'linkedin'  => ['LinkedIn',    'fa-linkedin-in'],
            ];
            foreach ($labels as $key => $info):
                [$lbl, $icon] = $info;
                $field = 'footer_social_' . $key;
            ?>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">
                        <i class="fa-brands <?= $icon ?> mr-1 <?= $key === 'whatsapp' ? 'text-emerald-500' : '' ?>"></i>
                        <?= $lbl ?>
                    </label>
                    <input type="text" name="<?= $field ?>"
                           value="<?= e($f[$field] ?? '') ?>"
                           placeholder="<?= $key === 'whatsapp' ? 'https://wa.me/5215512345678' : 'https://...' ?>"
                           <?= $canEdit ? '' : 'readonly' ?>
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            <?php endforeach; ?>
        </div>
        <p class="text-[10px] text-slate-400">
            <i class="fa-solid fa-circle-info mr-1"></i>
            Los íconos aparecen automáticamente al guardar un link. Deja en blanco los que no quieras mostrar.
        </p>
    </div>

    <!-- Enlaces -->
    <div class="bg-white p-6 rounded-2xl border space-y-4">
        <h3 class="font-bold"><i class="fa-solid fa-link text-amber-500"></i> Columnas de Enlaces</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase text-slate-500">Título columna 1</label>
                <input type="text" name="footer_links_menu_title" value="<?= e($f['footer_links_menu_title']) ?>"
                       <?= $canEdit ? '' : 'readonly' ?>
                       class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
                <textarea name="footer_links_menu" rows="7"
                          <?= $canEdit ? '' : 'readonly' ?>
                          class="w-full bg-slate-50 border rounded-xl p-3 text-sm font-mono"><?= e($f['footer_links_menu']) ?></textarea>
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase text-slate-500">Título columna 2</label>
                <input type="text" name="footer_links_services_title" value="<?= e($f['footer_links_services_title']) ?>"
                       <?= $canEdit ? '' : 'readonly' ?>
                       class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
                <textarea name="footer_links_services" rows="7"
                          <?= $canEdit ? '' : 'readonly' ?>
                          class="w-full bg-slate-50 border rounded-xl p-3 text-sm font-mono"><?= e($f['footer_links_services']) ?></textarea>
            </div>
        </div>
        <p class="text-[10px] text-slate-400">Formato: <code class="bg-slate-100 px-1.5 py-0.5 rounded">Etiqueta | url.php</code> (una por línea)</p>
    </div>

    <!-- Contacto -->
    <div class="bg-white p-6 rounded-2xl border space-y-4">
        <h3 class="font-bold"><i class="fa-solid fa-address-card text-amber-500"></i> Información de Contacto</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-2">
                <input type="text" name="footer_hours_label" value="<?= e($f['footer_hours_label']) ?>"
                       placeholder="Etiqueta horario" <?= $canEdit ? '' : 'readonly' ?>
                       class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
                <input type="text" name="footer_hours" value="<?= e($f['footer_hours']) ?>"
                       placeholder="Lunes a Sábado · 09:00 - 19:00" <?= $canEdit ? '' : 'readonly' ?>
                       class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
            </div>
            <div class="space-y-2">
                <input type="text" name="footer_address_label" value="<?= e($f['footer_address_label']) ?>"
                       placeholder="Etiqueta ubicación" <?= $canEdit ? '' : 'readonly' ?>
                       class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
                <input type="text" name="footer_address" value="<?= e($f['footer_address']) ?>"
                       placeholder="Dirección" <?= $canEdit ? '' : 'readonly' ?>
                       class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
            </div>
            <div class="space-y-2">
                <input type="text" name="footer_email_label" value="<?= e($f['footer_email_label']) ?>"
                       placeholder="Etiqueta correo" <?= $canEdit ? '' : 'readonly' ?>
                       class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
                <input type="email" name="footer_email" value="<?= e($f['footer_email']) ?>"
                       placeholder="correo@ejemplo.com" <?= $canEdit ? '' : 'readonly' ?>
                       class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
            </div>
        </div>
    </div>

    <!-- Subfooter -->
    <div class="bg-white p-6 rounded-2xl border space-y-4">
        <h3 class="font-bold"><i class="fa-solid fa-copyright text-amber-500"></i> Subfooter / Legal</h3>
        <input type="text" name="footer_copyright" value="<?= e($f['footer_copyright']) ?>"
               <?= $canEdit ? '' : 'readonly' ?>
               class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="grid grid-cols-2 gap-2">
                <input type="text" name="footer_legal_terms" value="<?= e($f['footer_legal_terms']) ?>"
                       placeholder="Términos" <?= $canEdit ? '' : 'readonly' ?>
                       class="bg-slate-50 border rounded-xl p-3 text-sm">
                <input type="text" name="footer_legal_terms_url" value="<?= e($f['footer_legal_terms_url']) ?>"
                       placeholder="#" <?= $canEdit ? '' : 'readonly' ?>
                       class="bg-slate-50 border rounded-xl p-3 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <input type="text" name="footer_legal_privacy" value="<?= e($f['footer_legal_privacy']) ?>"
                       placeholder="Privacidad" <?= $canEdit ? '' : 'readonly' ?>
                       class="bg-slate-50 border rounded-xl p-3 text-sm">
                <input type="text" name="footer_legal_privacy_url" value="<?= e($f['footer_legal_privacy_url']) ?>"
                       placeholder="#" <?= $canEdit ? '' : 'readonly' ?>
                       class="bg-slate-50 border rounded-xl p-3 text-sm">
            </div>
        </div>
    </div>

    <?php if ($canEdit): ?>
        <div class="sticky bottom-4 bg-white p-4 rounded-2xl border shadow-lg flex justify-end">
            <button class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-8 py-3 rounded-xl text-sm shadow-md">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Footer
            </button>
        </div>
    <?php endif; ?>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>