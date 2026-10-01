<?php
require_once __DIR__ . '/config/bootstrap.php';

$pageTitle  = 'Inicio';
$activePage = 'inicio';

/* Tipos disponibles (dinámico) */
$typesAvailable = db()->query(
    "SELECT DISTINCT type FROM properties
     WHERE is_paused = 0 AND is_sold = 0 AND type IS NOT NULL AND type != ''
     ORDER BY type ASC"
)->fetchAll(PDO::FETCH_COLUMN);

$featured = db()->query(
    "SELECT * FROM properties
     WHERE featured = 1 AND is_paused = 0 AND is_sold = 0
     ORDER BY created_at DESC LIMIT 6"
)->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<div class="relative bg-slate-950 text-white py-24 px-4 overflow-hidden">
    <div class="absolute inset-0 opacity-25">
        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=2000&q=80"
             alt="Residencia de Lujo" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent"></div>

    <div class="relative max-w-5xl mx-auto text-center space-y-6">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 text-xs font-semibold border border-amber-500/30">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Tu Próximo Hogar te Espera
        </span>
        <h1 class="text-4xl sm:text-6xl font-black tracking-tight leading-tight">
            Encuentra la propiedad exclusiva que <span class="text-amber-500">mereces habitar</span>
        </h1>
        <p class="text-slate-300 text-lg max-w-2xl mx-auto font-light">
            Explora residencias, departamentos y terrenos comerciales y mas directamente desde nuestro sitio web Monarka Inmobiliaria.
        </p>

        <div class="bg-white/95 backdrop-blur-md text-slate-800 p-4 sm:p-6 rounded-2xl shadow-2xl max-w-4xl mx-auto border border-white/20 mt-8">
            <form action="<?= url('propiedades.php') ?>" method="GET"
                  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1 text-left">Tipo Inmueble</label>
                    <select name="type" class="w-full bg-slate-100 border border-slate-300 rounded-xl px-3 py-2.5 text-sm font-medium focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <option value="Todos">Todos los Tipos</option>
                        <?php foreach ($typesAvailable as $t): ?>
                            <option value="<?= e($t) ?>"><?= e($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1 text-left">Operación</label>
                    <select name="listing" class="w-full bg-slate-100 border border-slate-300 rounded-xl px-3 py-2.5 text-sm font-medium focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <option value="Todos">Venta y Renta</option>
                        <option value="Venta">Venta</option>
                        <option value="Renta">Renta</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1 text-left">Ubicación / Ciudad</label>
                    <div class="relative">
                        <i class="fa-solid fa-location-dot absolute left-3 top-3.5 text-slate-400 text-xs"></i>
                        <input type="text" name="location" placeholder="Ej. CDMX, Polanco..."
                               class="w-full bg-slate-100 border border-slate-300 rounded-xl pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar Ahora
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DESTACADAS -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="flex justify-between items-end mb-10">
        <div>
            <span class="text-amber-600 font-bold text-xs uppercase tracking-widest block mb-1">Catálogo Exclusivo</span>
            <h2 class="text-3xl font-black text-slate-900">Propiedades Destacadas</h2>
        </div>
        <a href="<?= url('propiedades.php') ?>" class="flex items-center gap-2 text-amber-600 font-bold text-sm hover:text-amber-700 transition-colors">
            <span>Ver Catálogo Completo</span> <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($featured)): ?>
            <div class="col-span-full text-center py-12 bg-white rounded-2xl border border-slate-200">
                <i class="fa-solid fa-building-circle-xmark text-4xl text-slate-300 mb-3 block"></i>
                <h4 class="font-bold text-slate-700">Aún no hay propiedades destacadas</h4>
            </div>
        <?php else: foreach ($featured as $p): ?>
            <?php require __DIR__ . '/includes/property-card.php'; ?>
        <?php endforeach; endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>