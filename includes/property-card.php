<?php
$hasParking  = !empty($p['has_parking']);
$hasFloors   = !empty($p['floors']);

$cols = 3;
if ($hasFloors)  $cols++;
if ($hasParking) $cols++;

$settings    = $settings ?? getSettings();
$waRawNumber = $settings['site_whatsapp'] ?? ($settings['site_phone'] ?? '');
$waClean     = preg_replace('/[^0-9]/', '', $waRawNumber);

$waMessage = "¡Hola! 👋 Me interesa recibir información sobre la propiedad: *{$p['title']}* " .
             "(ID #{$p['id']}) ubicada en {$p['location']}, {$p['city']}. " .
             "Precio publicado: " . money($p['price']) . ". " .
             "¿Podrían darme más detalles?";

$waUrl = $waClean ? 'https://wa.me/' . $waClean . '?text=' . rawurlencode($waMessage) : '';
?>
<div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-slate-200 flex flex-col group">

    <!-- ============ IMAGEN (SIN LIGHTBOX) ============ -->
    <div class="relative h-60 overflow-hidden">
        <img src="<?= e(imageUrl($p['image_main'])) ?>"
             alt="<?= e($p['title']) ?>"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

        <div class="absolute top-3 left-3 bg-slate-900/80 backdrop-blur-sm text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase">
            <?= e($p['listing_type']) ?>
        </div>
        <div class="absolute top-3 right-3 bg-amber-500 text-slate-950 font-black text-xs px-2.5 py-1 rounded-lg">
            <?= money($p['price']) ?>
        </div>

        <?php if ($hasFloors): ?>
            <div class="absolute bottom-3 right-3 bg-sky-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-md flex items-center gap-1">
                <i class="fa-solid fa-layer-group"></i> <?= (int)$p['floors'] ?> planta<?= $p['floors'] > 1 ? 's' : '' ?>
            </div>
        <?php endif; ?>

        <?php if ($hasParking): ?>
            <div class="absolute bottom-3 left-3 bg-emerald-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-md flex items-center gap-1">
                <i class="fa-solid fa-car"></i> Cochera <?= (int)$p['parking_spaces'] ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ============ CONTENIDO ============ -->
    <div class="p-6 flex-grow flex flex-col justify-between space-y-4">
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1"><?= e($p['type']) ?> • <?= e($p['city']) ?></span>
            <h3 class="font-bold text-slate-900 text-base line-clamp-1 group-hover:text-amber-600 transition-colors"><?= e($p['title']) ?></h3>
            <p class="text-slate-500 text-xs flex items-center gap-1 mt-2">
                <i class="fa-solid fa-location-dot text-amber-500"></i> <?= e($p['location']) ?>
            </p>
        </div>

        <div>
            <div class="grid grid-cols-<?= $cols ?> gap-2 py-2 border-y border-slate-100 text-slate-600 text-xs font-medium mb-4">
                <div><i class="fa-solid fa-bed text-slate-400 mr-1"></i><?= (int)$p['bedrooms'] ?> Rec.</div>
                <div><i class="fa-solid fa-bath text-slate-400 mr-1"></i><?= (int)$p['bathrooms'] ?> Baños</div>
                <div><i class="fa-solid fa-ruler-combined text-slate-400 mr-1"></i><?= (int)$p['area_sqm'] ?> m²</div>
                <?php if ($hasFloors): ?>
                    <div class="text-sky-700 font-bold"><i class="fa-solid fa-layer-group text-sky-500 mr-1"></i><?= (int)$p['floors'] ?> P.</div>
                <?php endif; ?>
                <?php if ($hasParking): ?>
                    <div class="text-amber-700 font-bold"><i class="fa-solid fa-car text-amber-500 mr-1"></i><?= (int)$p['parking_spaces'] ?> Autos</div>
                <?php endif; ?>
            </div>

            <div class="space-y-2">
                <a href="<?= url('propiedades.php?ver=' . (int)$p['id']) ?>"
                   class="block w-full text-center bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-xl transition-all text-xs">
                    <i class="fa-solid fa-eye"></i> Ver Detalles
                </a>

                <?php if ($waUrl): ?>
                    <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener"
                       class="block w-full text-center bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2.5 rounded-xl transition-all text-xs flex items-center justify-center gap-2">
                        <i class="fa-brands fa-whatsapp text-base"></i>
                        Consultar por WhatsApp
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>