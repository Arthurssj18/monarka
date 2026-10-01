<?php
require_once __DIR__ . '/config/bootstrap.php';

$pageTitle  = 'Propiedades';
$activePage = 'propiedades';

$type     = $_GET['type']     ?? 'Todos';
$listing  = $_GET['listing']  ?? 'Todos';
$location = trim($_GET['location'] ?? '');
$maxPrice = $_GET['price'] ?? '';

$typesAvailable = db()->query(
    "SELECT DISTINCT type FROM properties
     WHERE is_paused = 0 AND is_sold = 0 AND type IS NOT NULL AND type != ''
     ORDER BY type ASC"
)->fetchAll(PDO::FETCH_COLUMN);

$sql    = "SELECT * FROM properties WHERE is_paused = 0 AND is_sold = 0";
$params = [];

if ($type !== '' && $type !== 'Todos')        { $sql .= " AND type = ?";                      $params[] = $type; }
if ($listing !== '' && $listing !== 'Todos')  { $sql .= " AND listing_type = ?";              $params[] = $listing; }
if ($location !== '')                         { $sql .= " AND (location LIKE ? OR city LIKE ?)"; $params[] = "%$location%"; $params[] = "%$location%"; }
if ($maxPrice !== '' && is_numeric($maxPrice)){ $sql .= " AND price <= ?";                    $params[] = $maxPrice; }
$sql .= " ORDER BY created_at DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$propiedades = $stmt->fetchAll();

$detail = null;
$detailImgs = [];
$mapsEmbed  = '';
if (!empty($_GET['ver'])) {
    $st = db()->prepare("SELECT * FROM properties WHERE id = ? AND is_paused = 0 AND is_sold = 0");
    $st->execute([(int)$_GET['ver']]);
    $detail = $st->fetch();
    if ($detail) {
        $detailImgs = getPropertyImages((int)$detail['id']);
        if (empty($detailImgs)) $detailImgs = [$detail['image_main']];
        if (!empty($detail['google_maps_url'])) {
            $mapsEmbed = googleMapsEmbedUrl($detail['google_maps_url']);
        }
    }
}

$settings    = getSettings();
$waRawNumber = $settings['site_whatsapp'] ?? ($settings['site_phone'] ?? '');
$waClean     = preg_replace('/[^0-9]/', '', $waRawNumber);

$waDetailUrl = '';
if ($detail && $waClean) {
    $waDetailMsg = "¡Hola! 👋 Me interesa recibir información sobre la propiedad:\n\n" .
                   "🏠 *{$detail['title']}*\n" .
                   "🆔 ID: #{$detail['id']}\n" .
                   "📍 Ubicación: {$detail['location']}, {$detail['city']}\n" .
                   "💰 Precio: " . money($detail['price']) . "\n" .
                   "📋 Operación: {$detail['listing_type']}\n\n" .
                   "¿Podrían darme más detalles y agendar una visita?";
    $waDetailUrl = 'https://wa.me/' . $waClean . '?text=' . rawurlencode($waDetailMsg);
}

/* URLs de la galería + título para el lightbox */
$detailGalleryUrls = array_map(fn($u) => imageUrl($u), $detailImgs);
$detailTitleJson   = json_encode($detail['title'] ?? '', JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
$detailGalleryJson = json_encode($detailGalleryUrls, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

require __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-black text-slate-900 mb-2">Catálogo de Inmuebles</h1>
    <p class="text-slate-500 text-sm mb-8">Filtra las opciones disponibles en nuestro inventario.</p>

    <form method="GET" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-8 grid grid-cols-1 md:grid-cols-5 gap-4">
        <select name="type" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm">
            <option value="Todos" <?= $type==='Todos'?'selected':'' ?>>Todos los tipos</option>
            <?php foreach ($typesAvailable as $t): ?>
                <option value="<?= e($t) ?>" <?= $type===$t?'selected':'' ?>><?= e($t) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="listing" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm">
            <option value="Todos" <?= $listing==='Todos'?'selected':'' ?>>Venta y Renta</option>
            <option value="Venta" <?= $listing==='Venta'?'selected':'' ?>>Venta</option>
            <option value="Renta" <?= $listing==='Renta'?'selected':'' ?>>Renta</option>
        </select>

        <input type="text" name="location" value="<?= e($location) ?>" placeholder="Ubicación..."
               class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm">
        <input type="number" name="price" value="<?= e($maxPrice) ?>" placeholder="Precio máx..."
               class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm">

        <div class="flex gap-2">
            <button class="flex-1 bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-white font-bold py-2 rounded-xl text-xs">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>
            <a href="?" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2 px-3 rounded-xl text-xs">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>

    <p class="text-xs text-slate-400 mb-4">
        <i class="fa-solid fa-circle-info"></i> <?= count($propiedades) ?> inmueble(s)
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($propiedades)): ?>
            <div class="col-span-full text-center py-12 bg-white rounded-2xl border border-slate-200">
                <i class="fa-solid fa-building-circle-xmark text-4xl text-slate-300 mb-3 block"></i>
                <h4 class="font-bold text-slate-700">No se encontraron inmuebles</h4>
            </div>
        <?php else: foreach ($propiedades as $p): ?>
            <?php require __DIR__ . '/includes/property-card.php'; ?>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- =============== MODAL DETALLE =============== -->
<?php if ($detail): ?>
<div id="modal-property-detail" class="fixed inset-0 z-[90] modal-backdrop flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white max-w-5xl w-full rounded-3xl overflow-hidden shadow-2xl relative my-8">
        <a href="<?= url('propiedades.php') ?>"
           class="absolute top-4 right-4 z-20 bg-slate-900/80 hover:bg-slate-900 text-white w-9 h-9 rounded-full flex items-center justify-center">
            <i class="fa-solid fa-xmark"></i>
        </a>

        <div class="grid grid-cols-1 md:grid-cols-2">

            <!-- ============ GALERÍA ============ -->
            <div class="bg-slate-900 p-4 space-y-4">

                <div class="relative h-80 rounded-2xl overflow-hidden group">

                    <img id="detail-main-img"
                         src="<?= e(imageUrl($detailImgs[0])) ?>"
                         alt="<?= e($detail['title']) ?>"
                         class="w-full h-full object-cover transition-opacity duration-300 cursor-zoom-in"
                         onclick='openLightbox(this.src, <?= $detailTitleJson ?>, <?= $detailGalleryJson ?>, window.lightboxCurrentIndex || 0)'>

                    <?php if (count($detailImgs) > 1): ?>

                        <button type="button"
                                onclick="event.stopPropagation(); galleryPrev()"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-slate-950/70 hover:bg-amber-500 text-white hover:text-slate-950 backdrop-blur flex items-center justify-center transition-all shadow-lg opacity-80 hover:opacity-100 hover:scale-110 z-10">
                            <i class="fa-solid fa-chevron-left text-lg"></i>
                        </button>

                        <button type="button"
                                onclick="event.stopPropagation(); galleryNext()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-slate-950/70 hover:bg-amber-500 text-white hover:text-slate-950 backdrop-blur flex items-center justify-center transition-all shadow-lg opacity-80 hover:opacity-100 hover:scale-110 z-10">
                            <i class="fa-solid fa-chevron-right text-lg"></i>
                        </button>

                        <div class="absolute bottom-3 right-3 bg-slate-950/80 backdrop-blur text-white text-xs font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 z-10">
                            <i class="fa-solid fa-images text-amber-400"></i>
                            <span id="gallery-counter">1 / <?= count($detailImgs) ?></span>
                        </div>

                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 z-10">
                            <?php foreach ($detailImgs as $i => $img): ?>
                                <button type="button"
                                        data-idx="<?= $i ?>"
                                        onclick="event.stopPropagation(); galleryGoTo(<?= $i ?>)"
                                        class="gallery-dot w-2 h-2 rounded-full transition-all <?= $i === 0 ? 'bg-amber-500 w-6' : 'bg-white/50 hover:bg-white/80' ?>"></button>
                            <?php endforeach; ?>
                        </div>

                    <?php else: ?>
                        <div class="absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur text-slate-900 rounded-lg flex items-center justify-center shadow-lg pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Botón "Ver imagen en pantalla completa" -->
                <button type="button"
                        onclick='openLightbox(document.getElementById("detail-main-img").src, <?= $detailTitleJson ?>, <?= $detailGalleryJson ?>, window.lightboxCurrentIndex || 0)'
                        class="w-full bg-white/10 hover:bg-amber-500 hover:text-slate-950 backdrop-blur text-white text-xs font-bold py-2.5 rounded-xl transition-all flex items-center justify-center gap-2 border border-white/10 hover:border-amber-500">
                    <i class="fa-solid fa-expand"></i>
                    Ver imagen en pantalla completa
                </button>

                <?php if (count($detailImgs) > 1): ?>
                    <p class="text-center text-[10px] text-slate-500">
                        <i class="fa-solid fa-keyboard"></i>
                        Usa las flechas <kbd class="bg-slate-800 px-1.5 py-0.5 rounded text-slate-300">←</kbd>
                        <kbd class="bg-slate-800 px-1.5 py-0.5 rounded text-slate-300">→</kbd> para navegar
                    </p>
                <?php endif; ?>

                <?php if (!empty($mapsEmbed)): ?>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400 mb-2 tracking-wider">
                            <i class="fa-solid fa-map-location-dot text-amber-500"></i> Ubicación exacta
                        </p>
                        <div class="rounded-xl overflow-hidden border-2 border-slate-700">
                            <iframe src="<?= e($mapsEmbed) ?>" width="100%" height="200"
                                    style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ============ INFO ============ -->
            <div class="p-8 space-y-6 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold bg-amber-100 text-amber-800 px-3 py-1 rounded-full uppercase"><?= e($detail['listing_type']) ?></span>
                        <span class="text-2xl font-black text-slate-900"><?= money($detail['price']) ?></span>
                    </div>

                    <h2 class="text-2xl font-black text-slate-900 mb-2"><?= e($detail['title']) ?></h2>
                    <p class="text-xs text-slate-500 mb-4 flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-amber-500"></i> <?= e($detail['location']) ?>, <?= e($detail['city']) ?>
                    </p>
                    <p class="text-slate-600 text-xs leading-relaxed mb-6"><?= e($detail['description']) ?></p>

                    <?php
                    $hasFloors  = !empty($detail['floors']);
                    $hasParking = !empty($detail['has_parking']);
                    $cols = 3 + ($hasFloors ? 1 : 0) + ($hasParking ? 1 : 0);
                    ?>
                    <div class="grid grid-cols-<?= $cols ?> gap-2 py-3 bg-slate-50 rounded-xl px-4 text-xs">
                        <div><i class="fa-solid fa-bed text-amber-500 mr-1"></i><?= (int)$detail['bedrooms'] ?> Rec.</div>
                        <div><i class="fa-solid fa-bath text-amber-500 mr-1"></i><?= (int)$detail['bathrooms'] ?> Baños</div>
                        <div><i class="fa-solid fa-ruler-combined text-amber-500 mr-1"></i><?= (int)$detail['area_sqm'] ?> m²</div>
                        <?php if ($hasFloors): ?>
                            <div class="text-sky-700 font-bold"><i class="fa-solid fa-layer-group text-sky-500 mr-1"></i><?= (int)$detail['floors'] ?> Planta<?= $detail['floors'] > 1 ? 's' : '' ?></div>
                        <?php endif; ?>
                        <?php if ($hasParking): ?>
                            <div class="text-amber-700 font-bold"><i class="fa-solid fa-car text-amber-500 mr-1"></i><?= (int)$detail['parking_spaces'] ?> Autos</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="space-y-3 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 bg-amber-500/20 text-amber-700 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block">Asesor</span>
                            <span class="text-sm font-bold text-slate-800"><?= e($detail['agent_name']) ?></span>
                        </div>
                    </div>

                    <button onclick='openContactModal(<?= $detailTitleJson ?>)'
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 rounded-xl shadow-md flex items-center justify-center gap-2 text-sm">
                        <i class="fa-solid fa-envelope"></i> Solicitar Informes
                    </button>

                    <?php if (!empty($waDetailUrl)): ?>
                        <a href="<?= e($waDetailUrl) ?>" target="_blank" rel="noopener"
                           class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl shadow-md flex items-center justify-center gap-2 text-sm">
                            <i class="fa-brands fa-whatsapp text-lg"></i> Preguntar por WhatsApp
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const images = <?= $detailGalleryJson ?>;
    let currentIndex = 0;

    /* Inicializar el índice global que usa el lightbox */
    window.lightboxCurrentIndex = 0;

    const img     = document.getElementById('detail-main-img');
    const counter = document.getElementById('gallery-counter');
    const dots    = document.querySelectorAll('.gallery-dot');

    function updateGallery() {
        if (!img) return;
        img.style.opacity = '0';

        setTimeout(() => {
            img.src = images[currentIndex];
            img.style.opacity = '1';

            /* Actualizar índice global para el lightbox */
            window.lightboxCurrentIndex = currentIndex;

            if (counter) counter.innerText = (currentIndex + 1) + ' / ' + images.length;

            dots.forEach((dot, i) => {
                if (i === currentIndex) {
                    dot.classList.remove('bg-white/50', 'hover:bg-white/80', 'w-2');
                    dot.classList.add('bg-amber-500', 'w-6');
                } else {
                    dot.classList.remove('bg-amber-500', 'w-6');
                    dot.classList.add('bg-white/50', 'hover:bg-white/80', 'w-2');
                }
            });
        }, 150);
    }

    window.galleryNext = function () {
        if (images.length < 2) return;
        currentIndex = (currentIndex + 1) % images.length;
        updateGallery();
    };

    window.galleryPrev = function () {
        if (images.length < 2) return;
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        updateGallery();
    };

    window.galleryGoTo = function (i) {
        if (i < 0 || i >= images.length) return;
        currentIndex = i;
        updateGallery();
    };

    document.addEventListener('keydown', function (e) {
        const modal = document.getElementById('modal-property-detail');
        if (!modal || !modal.classList.contains('flex')) return;

        /* No interferir si el lightbox está abierto */
        const lb = document.getElementById('lightbox');
        if (lb && !lb.classList.contains('hidden')) return;

        const tag = (e.target.tagName || '').toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

        if (e.key === 'ArrowRight')      { e.preventDefault(); galleryNext(); }
        else if (e.key === 'ArrowLeft')  { e.preventDefault(); galleryPrev(); }
    });
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>