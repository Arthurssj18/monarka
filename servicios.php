<?php
require_once __DIR__ . '/config/bootstrap.php';
$pageTitle  = 'Servicios';
$activePage = 'servicios';
require __DIR__ . '/includes/header.php';

/* =========================================================
 *  CATÁLOGO DE SERVICIOS
 *  - Cada uno con su imagen (carpeta /images) y su ícono fallback
 * ========================================================= */
$servicios = [
    [
        'title'  => 'Administración',
        'desc'   => 'Gestión integral de propiedades en renta: cobranza, contratos, mantenimiento y atención a inquilinos con reportes mensuales.',
        'image'  => 'images/servicio-administracion.png',
        'icon'   => 'fa-building-user',
        'color'  => 'amber',
    ],
    [
        'title'  => 'Venta y Renta',
        'desc'   => 'Comercialización de inmuebles con estrategias de marketing digital, fotografía profesional y filtrado de clientes calificados.',
        'image'  => 'images/servicio-venta-renta.jpg',
        'icon'   => 'fa-handshake',
        'color'  => 'emerald',
    ],
    [
        'title'  => 'Avalúos',
        'desc'   => 'Valuación certificada de inmuebles para trámites bancarios, judiciales, fiscales o comerciales con peritos autorizados.',
        'image'  => 'images/servicio-avaluos.jpg',
        'icon'   => 'fa-chart-line',
        'color'  => 'sky',
    ],
    [
        'title'  => 'Mantenimiento',
        'desc'   => 'Servicio preventivo y correctivo para conservar tu propiedad en óptimas condiciones: plomería, electricidad, pintura y más.',
        'image'  => 'images/servicio-mantenimiento.jpg',
        'icon'   => 'fa-screwdriver-wrench',
        'color'  => 'violet',
    ],
    [
        'title'  => 'Construcción',
        'desc'   => 'Diseño, remodelación y obra nueva. Desde cimientos hasta acabados de lujo, con dirección técnica profesional.',
        'image'  => 'images/servicio-construccion.png',
        'icon'   => 'fa-helmet-safety',
        'color'  => 'orange',
    ],
];
?>

<!-- =============== HERO DE SERVICIOS =============== -->
<div class="relative bg-slate-950 text-white py-20 px-4 overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=2000&q=80"
             alt="Servicios" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent"></div>

    <div class="relative max-w-5xl mx-auto text-center space-y-4">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 text-xs font-semibold border border-amber-500/30">
            <i class="fa-solid fa-briefcase"></i> Servicios Profesionales
        </span>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight leading-tight">
            Soluciones <span class="text-amber-500">Inmobiliarias Integrales</span>
        </h1>
        <p class="text-slate-300 text-base max-w-2xl mx-auto font-light">
            Acompañamiento especializado en cada etapa de tu patrimonio: desde la compra y venta,
            hasta el mantenimiento y la construcción.
        </p>
    </div>
</div>

<!-- =============== GRID DE SERVICIOS =============== -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">

    <div class="text-center mb-12">
        <span class="text-amber-600 font-bold text-xs uppercase tracking-widest block mb-1">Lo que ofrecemos</span>
        <h2 class="text-3xl font-black text-slate-900">Nuestros Servicios</h2>
        <p class="text-slate-500 text-sm mt-3 max-w-2xl mx-auto">
            Cinco especialidades para cubrir todas las necesidades de tu inmueble.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($servicios as $s):
            $imgPath = BASE_PATH . '/' . $s['image'];
            $hasImg  = is_file($imgPath);
        ?>
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl transition-all border border-slate-200 flex flex-col group">

                <!-- Imagen o fallback con ícono -->
                <div class="relative h-52 overflow-hidden bg-slate-100">
                    <?php if ($hasImg): ?>
                        <img src="<?= url($s['image']) ?>?v=<?= filemtime($imgPath) ?>"
                             alt="<?= e($s['title']) ?>"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 to-transparent"></div>
                    <?php else: ?>
                        <!-- Fallback: ícono ámbar grande -->
                        <div class="w-full h-full bg-gradient-to-br from-amber-500/10 to-amber-600/20 flex items-center justify-center">
                            <i class="fa-solid <?= e($s['icon']) ?> text-6xl text-amber-500/60"></i>
                        </div>
                    <?php endif; ?>

                    <!-- Ícono flotante sobre la esquina -->
                    <div class="absolute bottom-4 left-4 bg-<?= $s['color'] ?>-500 text-white w-12 h-12 rounded-xl flex items-center justify-center shadow-lg border-4 border-white">
                        <i class="fa-solid <?= e($s['icon']) ?> text-lg"></i>
                    </div>
                </div>

                <!-- Contenido -->
                <div class="p-6 flex-grow flex flex-col justify-between space-y-4">
                    <div>
                        <h3 class="font-black text-slate-900 text-xl mb-3 group-hover:text-amber-600 transition-colors">
                            <?= e($s['title']) ?>
                        </h3>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            <?= e($s['desc']) ?>
                        </p>
                    </div>

                    <a href="<?= url('contacto.php') ?>"
                       class="inline-flex items-center gap-2 text-<?= $s['color'] ?>-600 hover:text-<?= $s['color'] ?>-700 font-bold text-sm pt-3 border-t border-slate-100 group/link">
                        Solicitar información
                        <i class="fa-solid fa-arrow-right text-xs group-hover/link:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- =============== CTA FINAL =============== -->
<div class="bg-slate-950 text-white py-16 px-4">
    <div class="max-w-4xl mx-auto text-center space-y-6">
        <h3 class="text-3xl sm:text-4xl font-black">
            ¿Listo para <span class="text-amber-500">empezar tu proyecto</span>?
        </h3>
        <p class="text-slate-400 max-w-2xl mx-auto">
            Agenda una consulta gratuita con uno de nuestros asesores especializados.
        </p>
        <div class="flex flex-wrap justify-center gap-4 pt-4">
            <a href="<?= url('contacto.php') ?>"
               class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg flex items-center gap-2">
                <i class="fa-solid fa-phone"></i> Contactar Asesor
            </a>
            <a href="<?= url('propiedades.php') ?>"
               class="bg-white/10 hover:bg-white/20 backdrop-blur text-white font-bold px-8 py-3.5 rounded-xl transition-all border border-white/20 flex items-center gap-2">
                <i class="fa-solid fa-building"></i> Ver Propiedades
            </a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>