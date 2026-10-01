<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();

/* =========================================================
 *  PROCESAMIENTO POST
 * ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    requirePermission('settings.edit');

    /* -------- GUARDAR PARÁMETROS -------- */
    if (($_POST['action'] ?? '') === 'save') {
        $campos = ['company_name', 'site_phone', 'site_email', 'site_address'];
        $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($campos as $c) $stmt->execute([$c, trim($_POST[$c] ?? '')]);
        flash('Configuración guardada.');
        redirect('admin/settings.php');
    }

    /* -------- SUBIR LOGO -------- */
    if (($_POST['action'] ?? '') === 'upload_logo') {
        if (empty($_FILES['site_logo']['name'])) {
            flash('Selecciona un archivo.', 'error');
            redirect('admin/settings.php');
        }
        $newPath = uploadSingleImage($_FILES['site_logo'], 'branding');
        if ($newPath) {
            $s = getSettings();
            foreach (['site_logo', 'logo'] as $key) {
                if (!empty($s[$key])) deleteLocalImage($s[$key]);
            }
            $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                                   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute(['site_logo', $newPath]);
            $stmt->execute(['logo', $newPath]);
            flash('Logo actualizado.');
        } else {
            flash('No se pudo subir el logo.', 'error');
        }
        redirect('admin/settings.php');
    }

    /* -------- ELIMINAR LOGO -------- */
    if (($_POST['action'] ?? '') === 'delete_logo') {
        $s = getSettings();
        foreach (['site_logo', 'logo'] as $key) {
            if (!empty($s[$key])) deleteLocalImage($s[$key]);
            db()->prepare("UPDATE settings SET setting_value='' WHERE setting_key=?")->execute([$key]);
        }
        flash('Logo eliminado.');
        redirect('admin/settings.php');
    }

    /* -------- GUARDAR PALETAS -------- */
    if (($_POST['action'] ?? '') === 'save_colors') {
        $map = [
            'color_primary' => '#f59e0b',
            'color_neutral' => '#64748b',
            'color_success' => '#10b981',
            'color_info'    => '#0ea5e9',
            'color_danger'  => '#f43f5e',
            'color_accent'  => '#8b5cf6',
        ];

        $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($map as $key => $fallback) {
            $value = sanitizeHex($_POST[$key] ?? $fallback, $fallback);
            $stmt->execute([$key, $value]);
        }
        flash('Paletas de colores actualizadas. Recarga el sitio público (Ctrl+F5) para ver los cambios.');
        redirect('admin/settings.php');
    }

    /* -------- APLICAR TEMA PREDEFINIDO -------- */
    if (($_POST['action'] ?? '') === 'apply_theme') {
        $themes = [
            'default' => ['#f59e0b','#64748b','#10b981','#0ea5e9','#f43f5e','#8b5cf6'],
            'ocean'   => ['#0ea5e9','#475569','#14b8a6','#06b6d4','#ef4444','#6366f1'],
            'forest'  => ['#10b981','#52525b','#22c55e','#0ea5e9','#dc2626','#84cc16'],
            'royal'   => ['#6366f1','#475569','#10b981','#3b82f6','#dc2626','#a855f7'],
            'sunset'  => ['#f97316','#57534e','#10b981','#eab308','#dc2626','#ec4899'],
            'mono'    => ['#334155','#64748b','#475569','#64748b','#7f1d1d','#1e293b'],
            'luxury'  => ['#a16207','#1e293b','#15803d','#1e40af','#991b1b','#6d28d9'],
        ];

        $themeKey = $_POST['theme'] ?? 'default';
        if (!isset($themes[$themeKey])) $themeKey = 'default';

        $keys = ['color_primary','color_neutral','color_success','color_info','color_danger','color_accent'];
        $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($themes[$themeKey] as $i => $hex) {
            $stmt->execute([$keys[$i], $hex]);
        }
        flash('Tema aplicado. Recarga el sitio público (Ctrl+F5) para ver los cambios.');
        redirect('admin/settings.php');
    }
}

$pageTitle = 'Configuración';
$activeTab = 'settings';
require_once __DIR__ . '/includes/header.php';

$canEdit = can('settings.edit');

$s         = getSettings();
$logoPath  = $s['site_logo'] ?? ($s['logo'] ?? '');
$logoExists = $logoPath && (preg_match('#^https?://#i', $logoPath) || is_file(BASE_PATH . '/' . ltrim($logoPath, '/')));

/* ============ PALETAS ACTUALES ============ */
$palettes = [
    'primary' => ['label' => 'Principal',   'setting' => 'color_primary', 'hex' => getBaseColor('primary'), 'tw' => 'amber',   'desc' => 'Botones, acentos, íconos activos'],
    'neutral' => ['label' => 'Neutral',     'setting' => 'color_neutral', 'hex' => getBaseColor('neutral'), 'tw' => 'slate',   'desc' => 'Fondos, textos, header, footer'],
    'success' => ['label' => 'Éxito',       'setting' => 'color_success', 'hex' => getBaseColor('success'), 'tw' => 'emerald', 'desc' => 'WhatsApp, cochera, estados positivos'],
    'info'    => ['label' => 'Información', 'setting' => 'color_info',    'hex' => getBaseColor('info'),    'tw' => 'sky',     'desc' => 'Estados informativos, badges'],
    'danger'  => ['label' => 'Peligro',     'setting' => 'color_danger',  'hex' => getBaseColor('danger'),  'tw' => 'rose',    'desc' => 'Errores, eliminar, alertas'],
    'accent'  => ['label' => 'Acento',      'setting' => 'color_accent',  'hex' => getBaseColor('accent'),  'tw' => 'violet',  'desc' => 'Detalles secundarios, decoración'],
];

/* ============ PRESETS DE TEMAS ============ */
$themes = [
    'default' => ['label' => 'Ámbar Clásico', 'colors' => ['#f59e0b','#64748b','#10b981','#0ea5e9','#f43f5e','#8b5cf6']],
    'ocean'   => ['label' => 'Océano',        'colors' => ['#0ea5e9','#475569','#14b8a6','#06b6d4','#ef4444','#6366f1']],
    'forest'  => ['label' => 'Bosque',        'colors' => ['#10b981','#52525b','#22c55e','#0ea5e9','#dc2626','#84cc16']],
    'royal'   => ['label' => 'Real',          'colors' => ['#6366f1','#475569','#10b981','#3b82f6','#dc2626','#a855f7']],
    'sunset'  => ['label' => 'Atardecer',     'colors' => ['#f97316','#57534e','#10b981','#eab308','#dc2626','#ec4899']],
    'mono'    => ['label' => 'Monocromo',     'colors' => ['#334155','#64748b','#475569','#64748b','#7f1d1d','#1e293b']],
    'luxury'  => ['label' => 'Lujo',          'colors' => ['#a16207','#1e293b','#15803d','#1e40af','#991b1b','#6d28d9']],
];
?>

<?php if (!$canEdit): ?>
    <div class="mb-6 p-4 rounded-xl border bg-sky-50 border-sky-200 text-sky-800 text-sm flex items-center gap-2">
        <i class="fa-solid fa-eye"></i>
        <span><strong>Modo solo lectura:</strong> Puedes ver la configuración, pero no modificarla.</span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

    <!-- =============== LOGO =============== -->
    <div class="bg-white p-8 rounded-2xl border space-y-6">
        <h3 class="font-bold text-lg"><i class="fa-solid fa-image text-amber-500"></i> Logo del Sitio</h3>

        <div class="border-2 border-dashed rounded-2xl p-6 bg-slate-50 flex items-center justify-center min-h-[140px]">
            <?php if ($logoExists): ?>
                <img src="<?= e(imageUrl($logoPath)) ?>?v=<?= time() ?>" class="max-h-24 object-contain">
            <?php else: ?>
                <div class="text-center text-slate-400">
                    <i class="fa-solid fa-building-user text-4xl mb-2 text-amber-500"></i>
                    <p class="text-xs">Sin logo</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($canEdit): ?>
            <form method="POST" enctype="multipart/form-data" class="space-y-3">
                <?= csrfField() ?><input type="hidden" name="action" value="upload_logo">
                <input type="file" name="site_logo" accept="image/*" required class="w-full text-xs">
                <button class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2.5 rounded-xl text-xs">
                    <i class="fa-solid fa-upload"></i> Subir Logo
                </button>
            </form>

            <?php if ($logoPath): ?>
                <form method="POST" onsubmit="return confirm('¿Eliminar?')">
                    <?= csrfField() ?><input type="hidden" name="action" value="delete_logo">
                    <button class="w-full bg-rose-50 text-rose-600 font-bold py-2.5 rounded-xl text-xs">
                        <i class="fa-solid fa-trash"></i> Eliminar Logo
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- =============== PARÁMETROS =============== -->
    <div class="bg-white p-8 rounded-2xl border space-y-6">
        <h3 class="font-bold text-lg"><i class="fa-solid fa-sliders text-amber-500"></i> Parámetros</h3>
        <form method="POST" class="space-y-4">
            <?= csrfField() ?><input type="hidden" name="action" value="save">
            <input type="text" name="company_name" value="<?= e($s['company_name']) ?>" placeholder="Nombre comercial"
                   class="w-full bg-slate-50 border rounded-xl p-3 text-sm" <?= $canEdit ? '' : 'readonly' ?>>
            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="site_phone" value="<?= e($s['site_phone']) ?>" placeholder="Teléfono"
                       class="bg-slate-50 border rounded-xl p-3 text-sm" <?= $canEdit ? '' : 'readonly' ?>>
                <input type="email" name="site_email" value="<?= e($s['site_email']) ?>" placeholder="Email"
                       class="bg-slate-50 border rounded-xl p-3 text-sm" <?= $canEdit ? '' : 'readonly' ?>>
            </div>
            <input type="text" name="site_address" value="<?= e($s['site_address']) ?>" placeholder="Dirección"
                   class="w-full bg-slate-50 border rounded-xl p-3 text-sm" <?= $canEdit ? '' : 'readonly' ?>>
            <?php if ($canEdit): ?>
                <button class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl text-xs">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            <?php endif; ?>
        </form>
    </div>

    <!-- =============== TEMAS PREDEFINIDOS =============== -->
    <div class="lg:col-span-2 bg-white p-8 rounded-2xl border space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h3 class="font-bold text-lg">
                <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i> Temas Predefinidos
            </h3>
            <span class="text-xs text-slate-500">
                <i class="fa-solid fa-circle-info"></i> Aplica una combinación completa con un solo clic
            </span>
        </div>

        <form method="POST" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="apply_theme">
            <?php foreach ($themes as $key => $t): ?>
                <button type="submit" name="theme" value="<?= e($key) ?>"
                        <?= $canEdit ? '' : 'disabled' ?>
                        class="group p-3 rounded-xl border-2 border-slate-200 hover:border-amber-500 transition-all bg-white hover:shadow-md <?= $canEdit ? '' : 'opacity-60 cursor-not-allowed' ?>">
                    <div class="flex gap-1 mb-2 justify-center">
                        <?php foreach ($t['colors'] as $c): ?>
                            <span class="w-3 h-3 rounded-full" style="background-color: <?= e($c) ?>;"></span>
                        <?php endforeach; ?>
                    </div>
                    <span class="text-[10px] font-bold text-slate-700 group-hover:text-amber-600 block text-center">
                        <?= e($t['label']) ?>
                    </span>
                </button>
            <?php endforeach; ?>
        </form>
    </div>

    <!-- =============== EDITOR DE PALETAS =============== -->
    <div class="lg:col-span-2 bg-white p-8 rounded-2xl border space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h3 class="font-bold text-lg">
                <i class="fa-solid fa-palette text-amber-500"></i> Personalización Avanzada de Colores
            </h3>
            <span class="text-xs text-slate-500">
                <i class="fa-solid fa-circle-info"></i> Cada color genera 10 tonalidades automáticamente
            </span>
        </div>

        <form method="POST" class="space-y-6" id="palette-form">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save_colors">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($palettes as $key => $p): ?>
                    <div class="border border-slate-200 rounded-2xl p-5 space-y-4 bg-slate-50/50">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-black uppercase text-slate-700 tracking-wider">
                                    <?= e($p['label']) ?>
                                </span>
                                <span class="text-[9px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-bold">
                                    <?= e($p['tw']) ?>
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-500 leading-tight"><?= e($p['desc']) ?></p>
                        </div>

                        <div class="flex items-center gap-3">
                            <input type="color"
                                   name="<?= e($p['setting']) ?>"
                                   id="picker-<?= e($key) ?>"
                                   value="<?= e($p['hex']) ?>"
                                   oninput="updatePreview('<?= e($key) ?>', this.value)"
                                   <?= $canEdit ? '' : 'disabled' ?>
                                   class="w-14 h-14 rounded-xl cursor-pointer border-4 border-white shadow-md shrink-0 <?= $canEdit ? '' : 'opacity-60' ?>">

                            <input type="text"
                                   id="hex-<?= e($key) ?>"
                                   value="<?= e(strtoupper($p['hex'])) ?>"
                                   oninput="syncFromText('<?= e($key) ?>', this.value)"
                                   <?= $canEdit ? '' : 'readonly' ?>
                                   class="flex-1 bg-white border border-slate-300 rounded-lg p-2 text-xs font-mono uppercase">
                        </div>

                        <div id="preview-<?= e($key) ?>" class="grid grid-cols-11 gap-0.5 rounded-lg overflow-hidden">
                            <?php $pal = generatePalette($p['hex']); foreach ($pal as $tone => $color): ?>
                                <div class="h-6" style="background-color: <?= e($color) ?>;" title="<?= $tone ?>: <?= e($color) ?>"></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pt-4 border-t border-slate-200 flex flex-wrap gap-3 justify-between items-center">
                <p class="text-xs text-slate-500">
                    <i class="fa-solid fa-lightbulb text-amber-500"></i>
                    Los cambios se aplican al recargar el sitio público con <kbd class="bg-slate-100 px-1.5 py-0.5 rounded border text-[10px]">Ctrl + F5</kbd>
                </p>
                <?php if ($canEdit): ?>
                    <button type="submit"
                            class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-8 py-3 rounded-xl text-sm shadow-md">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Todas las Paletas
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

</div>

<script>
function hexToHsl(hex) {
    hex = hex.replace('#', '');
    const r = parseInt(hex.substr(0,2),16)/255;
    const g = parseInt(hex.substr(2,2),16)/255;
    const b = parseInt(hex.substr(4,2),16)/255;
    const max = Math.max(r,g,b), min = Math.min(r,g,b);
    let h = 0, s = 0, l = (max+min)/2;
    if (max !== min) {
        const d = max - min;
        s = l > 0.5 ? d/(2-max-min) : d/(max+min);
        switch(max) {
            case r: h = (g-b)/d + (g<b?6:0); break;
            case g: h = (b-r)/d + 2; break;
            case b: h = (r-g)/d + 4; break;
        }
        h /= 6;
    }
    return [h*360, s*100, l*100];
}

function hslToHex(h,s,l) {
    h /= 360; s /= 100; l /= 100;
    let r,g,b;
    if (s === 0) { r=g=b=l; }
    else {
        const hue2rgb = (p,q,t) => {
            if (t<0) t+=1; if (t>1) t-=1;
            if (t<1/6) return p+(q-p)*6*t;
            if (t<1/2) return q;
            if (t<2/3) return p+(q-p)*(2/3-t)*6;
            return p;
        };
        const q = l<0.5 ? l*(1+s) : l+s-l*s;
        const p = 2*l - q;
        r = hue2rgb(p,q,h+1/3);
        g = hue2rgb(p,q,h);
        b = hue2rgb(p,q,h-1/3);
    }
    const toHex = x => {
        const h2 = Math.round(x*255).toString(16);
        return h2.length === 1 ? '0'+h2 : h2;
    };
    return '#'+toHex(r)+toHex(g)+toHex(b);
}

function buildPalette(baseHex) {
    const [h,s,baseL] = hexToHsl(baseHex);
    const tones = {
        50: 96, 100: 91, 200: 82, 300: 72, 400: 62,
        500: baseL,
        600: Math.max(15, baseL-12),
        700: Math.max(10, baseL-22),
        800: Math.max(8,  baseL-30),
        900: Math.max(5,  baseL-38),
        950: Math.max(3,  baseL-45)
    };
    const out = {};
    for (const k in tones) {
        let sat = s;
        if (k <= 100) sat = Math.max(30, s-20);
        if (k >= 700) sat = Math.max(40, s-15);
        out[k] = hslToHex(h, sat, tones[k]);
    }
    return out;
}

function updatePreview(key, hex) {
    document.getElementById('hex-' + key).value = hex.toUpperCase();
    renderPalettePreview(key, hex);
}

function syncFromText(key, hex) {
    hex = hex.trim();
    if (!/^#?[0-9a-f]{6}$/i.test(hex)) return;
    if (hex[0] !== '#') hex = '#' + hex;
    document.getElementById('picker-' + key).value = hex;
    renderPalettePreview(key, hex);
}

function renderPalettePreview(key, hex) {
    const palette = buildPalette(hex);
    const box = document.getElementById('preview-' + key);
    box.innerHTML = '';
    Object.keys(palette).forEach(tone => {
        const div = document.createElement('div');
        div.className = 'h-6';
        div.style.backgroundColor = palette[tone];
        div.title = tone + ': ' + palette[tone];
        box.appendChild(div);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>