<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        requirePermission('properties.delete');
        $id = (int)($_POST['id'] ?? 0);
        foreach (getPropertyImages($id) as $img) deleteLocalImage($img);
        db()->prepare("DELETE FROM properties WHERE id = ?")->execute([$id]);
        flash('Propiedad eliminada.');
        redirect('admin/properties.php');
    }

    if ($action === 'toggle_pause') {
        requirePermission('properties.toggle');
        $id = (int)($_POST['id'] ?? 0);
        $cur = db()->prepare("SELECT is_paused FROM properties WHERE id = ?");
        $cur->execute([$id]); $r = $cur->fetch();
        if ($r) {
            $new = $r['is_paused'] ? 0 : 1;
            db()->prepare("UPDATE properties SET is_paused = ? WHERE id = ?")->execute([$new, $id]);
            flash($new ? '⏸ Propiedad pausada.' : '▶ Propiedad reactivada.');
        }
        redirect('admin/properties.php');
    }

    if ($action === 'toggle_sold') {
        requirePermission('properties.toggle');
        $id = (int)($_POST['id'] ?? 0);
        $cur = db()->prepare("SELECT is_sold FROM properties WHERE id = ?");
        $cur->execute([$id]); $r = $cur->fetch();
        if ($r) {
            $new = $r['is_sold'] ? 0 : 1;
            db()->prepare("UPDATE properties SET is_sold = ? WHERE id = ?")->execute([$new, $id]);
            flash($new ? '🏷 Marcada como VENDIDA.' : '↺ Marcada como DISPONIBLE.');
        }
        redirect('admin/properties.php');
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        requirePermission($id > 0 ? 'properties.edit' : 'properties.create');

        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);

        /* ============ TIPO (con soporte para "Otro") ============ */
        $type        = trim($_POST['type'] ?? 'Casa');
        if ($type === 'Otro' || $type === '__otro__') {
            $custom = trim($_POST['type_custom'] ?? '');
            $type   = $custom !== '' ? $custom : 'Otro';
        }

        $listing     = $_POST['listing_type'] ?? 'Venta';
        $location    = trim($_POST['location'] ?? '');
        $city        = trim($_POST['city'] ?? '');
        $bedrooms    = (int)($_POST['bedrooms'] ?? 0);
        $bathrooms   = (int)($_POST['bathrooms'] ?? 0);
        $area        = (float)($_POST['area_sqm'] ?? 0);
        $floors      = (int)($_POST['floors'] ?? 0);
        $mapsUrl     = trim($_POST['google_maps_url'] ?? '');
        $featured    = isset($_POST['featured']) ? 1 : 0;
        $agent       = trim($_POST['agent_name'] ?? '');
        $hasParking  = isset($_POST['has_parking']) ? 1 : 0;
        $parkingSp   = $hasParking ? max(1, (int)($_POST['parking_spaces'] ?? 1)) : 0;

        if ($title === '' || $price <= 0) {
            flash('Título y precio son obligatorios.', 'error');
            redirect('admin/properties.php');
        }

        $uploadedMain = null; $uploadedGallery = [];
        if (!empty($_FILES['image_main']['name'])) $uploadedMain = uploadSingleImage($_FILES['image_main'], 'properties');
        if (!empty($_FILES['gallery']['name'][0])) {
            $up = uploadImages($_FILES['gallery'], 'properties');
            $uploadedGallery = $up['files'];
        }

        if ($id > 0) {
            $cur = db()->prepare("SELECT image_main FROM properties WHERE id = ?");
            $cur->execute([$id]); $currMain = $cur->fetchColumn();
            if ($uploadedMain && $currMain) deleteLocalImage($currMain);
            $newMain = $uploadedMain ?: $currMain;

            db()->prepare("UPDATE properties SET title=?, description=?, price=?, type=?, listing_type=?,
                           location=?, city=?, bedrooms=?, bathrooms=?, area_sqm=?, floors=?,
                           google_maps_url=?, has_parking=?, parking_spaces=?, featured=?, agent_name=?, image_main=?
                           WHERE id=?")
                ->execute([$title,$description,$price,$type,$listing,$location,$city,$bedrooms,$bathrooms,$area,$floors,$mapsUrl,$hasParking,$parkingSp,$featured,$agent,$newMain,$id]);

            foreach ($uploadedGallery as $p) db()->prepare("INSERT INTO property_images (property_id, url) VALUES (?, ?)")->execute([$id, $p]);
            flash('Propiedad actualizada.');
        } else {
            db()->prepare("INSERT INTO properties
                (title, description, price, type, listing_type, location, city, bedrooms, bathrooms, area_sqm, floors,
                 google_maps_url, has_parking, parking_spaces, featured, agent_name, image_main)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
                ->execute([$title,$description,$price,$type,$listing,$location,$city,$bedrooms,$bathrooms,$area,$floors,$mapsUrl,$hasParking,$parkingSp,$featured,$agent,$uploadedMain ?: '']);
            $id = (int) db()->lastInsertId();
            if (!$uploadedMain && !empty($uploadedGallery)) {
                db()->prepare("UPDATE properties SET image_main = ? WHERE id = ?")->execute([$uploadedGallery[0], $id]);
            }
            foreach ($uploadedGallery as $p) db()->prepare("INSERT INTO property_images (property_id, url) VALUES (?, ?)")->execute([$id, $p]);
            flash('Propiedad creada.');
        }
        redirect('admin/properties.php');
    }
}

$pageTitle = 'Propiedades';
$activeTab = 'properties';
require_once __DIR__ . '/includes/header.php';

$props = db()->query("SELECT * FROM properties ORDER BY created_at DESC")->fetchAll();

$canCreate = can('properties.create');
$canEdit   = can('properties.edit');
$canToggle = can('properties.toggle');
$canDelete = can('properties.delete');
$isReadOnly = !$canCreate && !$canEdit && !$canToggle && !$canDelete;

/* ============ LISTA BASE DE TIPOS ============ */
$tiposBase = ['Casa', 'Departamento', 'Oficina', 'Terreno', 'Bodega', 'Local', 'Edificio'];
?>

<?php if ($isReadOnly): ?>
    <div class="mb-6 p-4 rounded-xl border bg-sky-50 border-sky-200 text-sky-800 text-sm flex items-center gap-2">
        <i class="fa-solid fa-eye"></i>
        <span><strong>Modo solo lectura:</strong> Puedes ver las propiedades, pero no modificarlas.</span>
    </div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-slate-500">
        Administra el catálogo.
        <?php if ($canToggle): ?><span class="text-amber-600 font-semibold ml-1"><i class="fa-solid fa-circle-info"></i> Usa ⏸/▶ y 🤝 para cambiar visibilidad.</span><?php endif; ?>
    </p>
    <?php if ($canCreate): ?>
        <button onclick="openPropertyModal()" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs px-4 py-2.5 rounded-xl">
            <i class="fa-solid fa-plus"></i> Nueva Propiedad
        </button>
    <?php endif; ?>
</div>

<div class="bg-white rounded-2xl border overflow-hidden shadow-sm">
    <table class="w-full text-left text-xs text-slate-600">
        <thead class="bg-slate-50 border-b uppercase font-bold text-slate-700">
            <tr>
                <th class="p-4">Inmueble</th><th class="p-4">Tipo</th><th class="p-4">Precio</th>
                <th class="p-4">Ubicación</th><th class="p-4">Plantas</th>
                <th class="p-4">Estacionamiento</th>
                <th class="p-4">Visibilidad</th>
                <?php if (!$isReadOnly): ?><th class="p-4 text-right">Acciones</th><?php endif; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (empty($props)): ?>
                <tr><td colspan="<?= $isReadOnly ? 7 : 8 ?>" class="p-6 text-center text-slate-400">Sin propiedades.</td></tr>
            <?php else: foreach ($props as $p):
                $paused = !empty($p['is_paused']); $sold = !empty($p['is_sold']);
            ?>
                <tr class="hover:bg-slate-50 <?= ($paused||$sold)?'opacity-60':'' ?>">
                    <td class="p-4">
                        <div class="flex items-center gap-3 font-semibold text-slate-900">
                            <img src="<?= e(imageUrl($p['image_main'])) ?>" class="w-12 h-10 object-cover rounded-lg <?= ($paused||$sold)?'grayscale':'' ?>">
                            <span class="line-clamp-1"><?= e($p['title']) ?></span>
                        </div>
                    </td>
                    <td class="p-4"><?= e($p['type']) ?><span class="block text-[10px] text-slate-400"><?= e($p['listing_type']) ?></span></td>
                    <td class="p-4 font-bold text-slate-900"><?= money($p['price']) ?></td>
                    <td class="p-4">
                        <?= e($p['city']) ?>
                        <?php if (!empty($p['google_maps_url'])): ?>
                            <span class="block text-[10px] text-emerald-600 mt-0.5">
                                <i class="fa-solid fa-map-location-dot"></i> Con mapa
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4">
                        <?php if (!empty($p['floors'])): ?>
                            <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 text-[10px] font-bold px-2 py-1 rounded-full border border-slate-200">
                                <i class="fa-solid fa-layer-group"></i> <?= (int)$p['floors'] ?>
                            </span>
                        <?php else: ?><span class="text-slate-300">—</span><?php endif; ?>
                    </td>
                    <td class="p-4">
                        <?php if (!empty($p['has_parking'])): ?>
                            <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-[10px] font-bold px-2 py-1 rounded-full border border-amber-200">
                                <i class="fa-solid fa-car"></i> <?= (int)$p['parking_spaces'] ?>
                            </span>
                        <?php else: ?><span class="text-slate-300">—</span><?php endif; ?>
                    </td>
                    <td class="p-4">
                        <?php if ($sold): ?>
                            <span class="bg-sky-100 text-sky-800 text-[10px] font-bold px-2 py-1 rounded-full"><i class="fa-solid fa-circle-check"></i> Vendida</span>
                        <?php elseif ($paused): ?>
                            <span class="bg-slate-200 text-slate-700 text-[10px] font-bold px-2 py-1 rounded-full"><i class="fa-solid fa-eye-slash"></i> Pausada</span>
                        <?php else: ?>
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-1 rounded-full"><i class="fa-solid fa-eye"></i> Visible</span>
                        <?php endif; ?>
                    </td>
                    <?php if (!$isReadOnly): ?>
                        <td class="p-4 text-right space-x-1 whitespace-nowrap">
                            <?php $p['images'] = getPropertyImages((int)$p['id']); $json = json_encode($p, JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE); ?>

                            <?php if ($canToggle && !$sold): ?>
                                <form method="POST" class="inline" onsubmit="return confirm('<?= $paused?'¿Reactivar?':'¿Pausar?' ?>')">
                                    <?= csrfField() ?><input type="hidden" name="action" value="toggle_pause"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button class="p-1.5 rounded <?= $paused?'text-emerald-600 hover:bg-emerald-100':'text-amber-600 hover:bg-amber-100' ?>"><i class="fa-solid <?= $paused?'fa-play':'fa-pause' ?>"></i></button>
                                </form>
                            <?php endif; ?>

                            <?php if ($canToggle): ?>
                                <form method="POST" class="inline" onsubmit="return confirm('<?= $sold?'¿Marcar como disponible?':'¿Marcar como vendida?' ?>')">
                                    <?= csrfField() ?><input type="hidden" name="action" value="toggle_sold"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button class="p-1.5 rounded <?= $sold?'text-sky-600 hover:bg-sky-100':'text-slate-600 hover:bg-slate-200' ?>"><i class="fa-solid <?= $sold?'fa-rotate-left':'fa-handshake' ?>"></i></button>
                                </form>
                            <?php endif; ?>

                            <?php if ($canEdit): ?>
                                <button onclick='openPropertyModal(<?= $json ?>)' class="p-1.5 hover:bg-slate-200 rounded text-slate-600"><i class="fa-solid fa-pen-to-square"></i></button>
                            <?php endif; ?>

                            <?php if ($canDelete): ?>
                                <form method="POST" class="inline" onsubmit="return confirmDelete('¿Eliminar?')">
                                    <?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button class="p-1.5 hover:bg-rose-100 rounded text-rose-600"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php if ($canCreate || $canEdit): ?>
<!-- =============== MODAL CRUD PROPIEDAD =============== -->
<div id="modal-property-crud" class="fixed inset-0 z-50 modal-backdrop hidden items-center justify-center p-4">
    <div class="bg-white w-full max-w-xl rounded-3xl shadow-2xl relative flex flex-col max-h-[92vh]">

        <!-- CABECERA FIJA -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
            <h3 id="property-crud-title" class="text-lg font-bold text-slate-900">Registrar Propiedad</h3>
            <button type="button" onclick="closeModal('modal-property-crud')"
                    class="text-slate-400 hover:text-slate-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- FORMULARIO CON SCROLL -->
        <form id="property-form" method="POST" enctype="multipart/form-data"
              class="flex-1 overflow-y-auto px-6 py-5 space-y-4">

            <?= csrfField() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="crud-id">

            <input type="text" name="title" id="crud-title" required placeholder="Título"
                   class="w-full bg-slate-50 border rounded-xl p-3 text-sm">

            <div class="grid grid-cols-2 gap-4">
                <input type="number" name="price" id="crud-price" required placeholder="Precio"
                       class="bg-slate-50 border rounded-xl p-3 text-sm">

                <!-- ============ SELECT DE TIPO CON "OTRO" ============ -->
                <div class="space-y-2">
                    <select name="type" id="crud-type" onchange="toggleCustomType(this.value)"
                            class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
                        <?php foreach ($tiposBase as $t): ?>
                            <option value="<?= e($t) ?>"><?= e($t) ?></option>
                        <?php endforeach; ?>
                        <option value="Otro">➕ Otro (especificar)</option>
                    </select>

                    <input type="text" name="type_custom" id="crud-type-custom"
                           placeholder="Escribe el tipo de propiedad..." maxlength="50"
                           class="hidden w-full bg-amber-50 border border-amber-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <select name="listing_type" id="crud-listing" class="bg-slate-50 border rounded-xl p-3 text-sm">
                    <option>Venta</option><option>Renta</option>
                </select>
                <input type="text" name="location" id="crud-location" required placeholder="Ubicación / Colonia"
                       class="bg-slate-50 border rounded-xl p-3 text-sm">
                <input type="text" name="city" id="crud-city" required placeholder="Ciudad"
                       class="bg-slate-50 border rounded-xl p-3 text-sm">
            </div>

            <div class="grid grid-cols-4 gap-4">
                <input type="number" name="bedrooms" id="crud-bedrooms" placeholder="Recámaras"
                       class="bg-slate-50 border rounded-xl p-3 text-sm">
                <input type="number" name="bathrooms" id="crud-bathrooms" placeholder="Baños"
                       class="bg-slate-50 border rounded-xl p-3 text-sm">
                <input type="number" name="area_sqm" id="crud-area" placeholder="m²"
                       class="bg-slate-50 border rounded-xl p-3 text-sm">
                <input type="number" name="floors" id="crud-floors" placeholder="Plantas" min="0" max="50"
                       class="bg-slate-50 border rounded-xl p-3 text-sm">
            </div>

            <!-- GOOGLE MAPS -->
            <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50/50 space-y-3">
                <label class="block text-xs font-bold uppercase text-slate-500">
                    <i class="fa-solid fa-map-location-dot text-amber-500 mr-1"></i> Ubicación en Google Maps
                </label>

                <textarea name="google_maps_url" id="crud-maps" rows="2"
                          placeholder="Pega el iframe de Google Maps, coordenadas o una dirección exacta..."
                          class="w-full bg-white border border-slate-300 rounded-xl p-3 text-sm font-mono text-xs"></textarea>

                <div id="maps-warning" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <strong>Este link no funciona.</strong> Los enlaces cortos no se pueden incrustar.
                    Usa <strong>Compartir → Insertar un mapa</strong> en Google Maps.
                </div>

                <div class="text-[10px] text-slate-600 leading-relaxed bg-amber-50 p-3 rounded-lg border border-amber-200">
                    <p class="font-bold text-amber-800 mb-1"><i class="fa-solid fa-circle-info"></i> ¿Cómo obtener el link correcto?</p>
                    <ol class="list-decimal list-inside space-y-1 text-amber-900">
                        <li>Abre <a href="https://www.google.com/maps" target="_blank" rel="noopener" class="underline font-bold">Google Maps</a> y busca la ubicación</li>
                        <li>Haz clic en <strong>Compartir</strong> → <strong>Insertar un mapa</strong></li>
                        <li>Copia el <strong>código HTML completo</strong> y pégalo aquí ✅</li>
                    </ol>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="https://www.google.com/maps" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 text-[10px] bg-slate-900 hover:bg-slate-800 text-white font-bold px-3 py-1.5 rounded-lg">
                        <i class="fa-solid fa-external-link-alt"></i> Abrir Google Maps
                    </a>
                    <button type="button" onclick="document.getElementById('crud-maps').value = 'Apizaco, Tlaxcala, México'; updateMapsPreview();"
                            class="inline-flex items-center gap-1.5 text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-3 py-1.5 rounded-lg">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Probar ejemplo
                    </button>
                </div>

                <div id="maps-preview" class="hidden rounded-xl overflow-hidden border border-slate-300">
                    <iframe id="maps-iframe" src="" width="100%" height="160" style="border:0;" loading="lazy"></iframe>
                </div>
            </div>

            <!-- Estacionamiento -->
            <div class="border rounded-xl p-4 bg-slate-50 space-y-3">
                <label class="flex items-center gap-2 text-sm font-semibold cursor-pointer">
                    <input type="checkbox" name="has_parking" id="crud-has-parking" value="1"
                           onchange="toggleParkingField(this.checked)"
                           class="w-4 h-4 accent-amber-500">
                    <i class="fa-solid fa-car text-amber-600"></i> Tiene cochera
                </label>
                <div id="crud-parking-wrap" style="display:none">
                    <input type="number" name="parking_spaces" id="crud-parking-spaces" value="1" min="1" max="20"
                           class="w-28 bg-white border rounded-xl p-3 text-sm">
                    <span class="text-xs text-slate-500">coche(s)</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="agent_name" id="crud-agent" placeholder="Asesor"
                       class="bg-slate-50 border rounded-xl p-3 text-sm">
                <label class="flex items-center gap-2 text-sm cursor-pointer pb-3">
                    <input type="checkbox" name="featured" id="crud-featured" class="w-4 h-4 accent-amber-500">
                    Destacar en el inicio
                </label>
            </div>

            <!-- Imágenes -->
            <div class="border-2 border-dashed border-slate-300 rounded-2xl p-5 bg-slate-50 space-y-5">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-2">
                        <i class="fa-solid fa-image text-amber-500 mr-1"></i> Fotografía Principal
                    </label>
                    <input type="file" name="image_main" id="crud-image" accept="image/*" class="hidden peer">
                    <label for="crud-image" class="flex items-center justify-center gap-2 w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-sm py-3 rounded-xl cursor-pointer peer-focus:ring-2 peer-focus:ring-amber-400">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span id="label-image-main">Selecciona foto Portada</span>
                    </label>
                    <p class="text-[10px] text-slate-400 mt-2 text-center">JPG, PNG, WEBP · máx 5 MB</p>
                    <div id="preview-main-wrap" class="mt-3 hidden">
                        <img id="preview-main" src="" class="w-32 h-24 object-cover rounded-lg border border-slate-300 shadow-sm">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200">
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-2">
                        <i class="fa-solid fa-images text-amber-500 mr-1"></i> Galería Adicional
                    </label>
                    <input type="file" name="gallery[]" id="crud-gallery" accept="image/*" multiple class="hidden peer">
                    <label for="crud-gallery" class="flex items-center justify-center gap-2 w-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm py-3 rounded-xl cursor-pointer peer-focus:ring-2 peer-focus:ring-slate-400">
                        <i class="fa-solid fa-images"></i>
                        <span id="label-gallery">Elige imágenes</span>
                    </label>
                    <p class="text-[10px] text-slate-400 mt-2 text-center">Puedes seleccionar varias a la vez</p>
                    <div id="preview-gallery" class="mt-3 flex flex-wrap gap-2"></div>
                </div>
            </div>

            <textarea name="description" id="crud-description" rows="3" placeholder="Descripción"
                      class="w-full bg-slate-50 border rounded-xl p-3 text-sm"></textarea>

        </form>

        <!-- BOTONES FIJOS -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 shrink-0 rounded-b-3xl grid grid-cols-2 gap-3">
            <button type="button" onclick="closeModal('modal-property-crud')"
                    class="bg-white hover:bg-slate-100 text-slate-700 font-bold py-3 rounded-xl flex items-center justify-center gap-2 border border-slate-300">
                <i class="fa-solid fa-xmark"></i> Cancelar
            </button>
            <button type="button" onclick="submitPropertyForm()"
                    class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-3 rounded-xl flex items-center justify-center gap-2 shadow-md">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Propiedad
            </button>
        </div>
    </div>
</div>

<script>
const TIPOS_BASE = <?= json_encode($tiposBase) ?>;

/* ============ MOSTRAR/OCULTAR CAMPO "OTRO" ============ */
function toggleCustomType(val) {
    const input = document.getElementById('crud-type-custom');
    if (val === 'Otro') {
        input.classList.remove('hidden');
        input.focus();
    } else {
        input.classList.add('hidden');
        input.value = '';
    }
}

/* ============ VALIDACIÓN ANTES DE ENVIAR ============ */
function submitPropertyForm() {
    const typeSelect = document.getElementById('crud-type');
    const typeCustom = document.getElementById('crud-type-custom');

    if (typeSelect.value === 'Otro' && typeCustom.value.trim() === '') {
        typeCustom.classList.remove('hidden');
        typeCustom.focus();
        typeCustom.classList.add('ring-2', 'ring-rose-400');
        alert('Por favor especifica el tipo de propiedad.');
        return;
    }
    typeCustom.classList.remove('ring-2', 'ring-rose-400');
    document.getElementById('property-form').requestSubmit();
}

function toggleParkingField(s) {
    document.getElementById('crud-parking-wrap').style.display = s ? 'block' : 'none';
    if (!s) document.getElementById('crud-parking-spaces').value = 1;
}

/* ============ PREVIEW DE GOOGLE MAPS ============ */
function updateMapsPreview() {
    const input = document.getElementById('crud-maps').value.trim();
    const wrap  = document.getElementById('maps-preview');
    const frame = document.getElementById('maps-iframe');
    const warn  = document.getElementById('maps-warning');

    warn.classList.add('hidden');
    if (!input) { wrap.classList.add('hidden'); return; }

    let embedUrl = '';
    const iframeMatch = input.match(/<iframe[^>]+src=["']([^"']+)["']/i);
    if (iframeMatch) {
        embedUrl = iframeMatch[1].replace(/&amp;/g, '&');
    } else if (input.includes('google.com/maps/embed')) {
        embedUrl = input;
    } else if (/@-?\d+\.\d+,-?\d+\.\d+/.test(input)) {
        const m = input.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
        embedUrl = `https://www.google.com/maps?q=${m[1]},${m[2]}&hl=es&z=17&output=embed`;
    } else if (/[?&](q|query)=/.test(input)) {
        const m = input.match(/[?&](?:q|query)=([^&]+)/);
        embedUrl = `https://www.google.com/maps?q=${encodeURIComponent(decodeURIComponent(m[1]))}&hl=es&z=17&output=embed`;
    } else if (/\/maps\/place\/([^/?]+)/.test(input)) {
        const m = input.match(/\/maps\/place\/([^/?]+)/);
        embedUrl = `https://www.google.com/maps?q=${encodeURIComponent(decodeURIComponent(m[1].replace(/\+/g,' ')))}&hl=es&z=17&output=embed`;
    } else if (/(maps\.app\.goo\.gl|goo\.gl\/maps)/i.test(input)) {
        warn.classList.remove('hidden');
        wrap.classList.add('hidden');
        return;
    } else {
        embedUrl = `https://www.google.com/maps?q=${encodeURIComponent(input)}&hl=es&z=17&output=embed`;
    }

    frame.src = embedUrl;
    wrap.classList.remove('hidden');
}

document.getElementById('crud-maps').addEventListener('input', updateMapsPreview);

function openPropertyModal(data = null) {
    /* Reset */
    document.getElementById('crud-image').value = '';
    document.getElementById('crud-gallery').value = '';
    document.getElementById('label-image-main').innerText = 'Selecciona foto Portada';
    document.getElementById('label-gallery').innerText = 'Elige imágenes';
    document.getElementById('preview-main-wrap').classList.add('hidden');
    document.getElementById('preview-gallery').innerHTML = '';
    document.getElementById('maps-preview').classList.add('hidden');
    document.getElementById('maps-warning').classList.add('hidden');
    document.getElementById('crud-type-custom').classList.add('hidden');
    document.getElementById('crud-type-custom').value = '';
    document.getElementById('crud-type-custom').classList.remove('ring-2', 'ring-rose-400');

    if (data) {
        document.getElementById('property-crud-title').innerText = 'Editar Propiedad';
        document.getElementById('crud-id').value          = data.id;
        document.getElementById('crud-title').value       = data.title;
        document.getElementById('crud-price').value       = data.price;
        document.getElementById('crud-listing').value     = data.listing_type;
        document.getElementById('crud-location').value    = data.location;
        document.getElementById('crud-city').value        = data.city;
        document.getElementById('crud-bedrooms').value    = data.bedrooms;
        document.getElementById('crud-bathrooms').value   = data.bathrooms;
        document.getElementById('crud-area').value        = data.area_sqm;
        document.getElementById('crud-floors').value      = data.floors || '';
        document.getElementById('crud-maps').value        = data.google_maps_url || '';
        document.getElementById('crud-agent').value       = data.agent_name || '';
        document.getElementById('crud-description').value = data.description || '';
        document.getElementById('crud-featured').checked  = data.featured == 1;

        /* ============ LÓGICA DEL TIPO ============ */
        if (TIPOS_BASE.includes(data.type)) {
            document.getElementById('crud-type').value = data.type;
            toggleCustomType(data.type);
        } else {
            document.getElementById('crud-type').value = 'Otro';
            document.getElementById('crud-type-custom').value = data.type;
            toggleCustomType('Otro');
        }

        const hp = data.has_parking == 1;
        document.getElementById('crud-has-parking').checked = hp;
        toggleParkingField(hp);
        document.getElementById('crud-parking-spaces').value = data.parking_spaces || 1;

        if (data.google_maps_url) updateMapsPreview();

        if (data.image_main) {
            const prev = document.getElementById('preview-main');
            prev.src = (window.BASE_URL || '') + '/' + data.image_main.replace(/^\/+/, '');
            document.getElementById('preview-main-wrap').classList.remove('hidden');
        }
        if (Array.isArray(data.images) && data.images.length) {
            const box = document.getElementById('preview-gallery');
            box.innerHTML = '<span class="text-[10px] text-slate-400 w-full">Galería actual:</span>' +
                data.images.map(u => {
                    const url = /^https?:\/\//i.test(u) ? u : (window.BASE_URL || '') + '/' + u.replace(/^\/+/, '');
                    return `<img src="${url}" class="w-16 h-12 object-cover rounded-lg border border-slate-300">`;
                }).join('');
        }
    } else {
        document.getElementById('property-crud-title').innerText = 'Registrar Propiedad';
        ['crud-id','crud-title','crud-description','crud-agent','crud-maps','crud-floors'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('crud-price').value = '5000000';
        document.getElementById('crud-type').value = 'Casa';
        document.getElementById('crud-location').value = 'Santa Fe, CDMX';
        document.getElementById('crud-city').value = 'CDMX';
        document.getElementById('crud-featured').checked = false;
        document.getElementById('crud-has-parking').checked = false;
        toggleParkingField(false);
    }
    openModal('modal-property-crud');
}

document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'crud-image') {
        const label = document.getElementById('label-image-main');
        const wrap  = document.getElementById('preview-main-wrap');
        const prev  = document.getElementById('preview-main');
        if (e.target.files[0]) {
            const name = e.target.files[0].name;
            label.innerText = '✓ ' + (name.length > 28 ? name.substring(0, 28) + '…' : name);
            prev.src = URL.createObjectURL(e.target.files[0]);
            wrap.classList.remove('hidden');
        } else {
            label.innerText = 'Selecciona foto Portada';
            wrap.classList.add('hidden');
        }
    }
    if (e.target && e.target.id === 'crud-gallery') {
        const label = document.getElementById('label-gallery');
        const box   = document.getElementById('preview-gallery');
        const files = e.target.files;
        if (files.length) {
            label.innerText = `✓ ${files.length} imagen(es) seleccionada(s)`;
            box.innerHTML = '';
            Array.from(files).forEach(file => {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.className = 'w-16 h-12 object-cover rounded-lg border border-slate-300';
                box.appendChild(img);
            });
        } else {
            label.innerText = 'Elige imágenes';
            box.innerHTML = '';
        }
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const m = document.getElementById('modal-property-crud');
        if (m && !m.classList.contains('hidden')) closeModal('modal-property-crud');
    }
});

document.getElementById('modal-property-crud').addEventListener('click', function(e) {
    if (e.target === this) closeModal('modal-property-crud');
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>