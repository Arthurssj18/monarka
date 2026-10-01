<?php
require_once __DIR__ . '/config/bootstrap.php';
$pageTitle = 'Contacto'; $activePage = 'contacto';
require __DIR__ . '/includes/header.php';

$flash = flash();
$s = getSettings();
?>
<div class="max-w-7xl mx-auto px-4 py-16 grid grid-cols-1 lg:grid-cols-2 gap-12">
    <div>
        <h1 class="text-4xl font-black text-slate-900 mb-6">Contáctanos</h1>
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-amber-500/10 text-amber-600 rounded-xl"><i class="fa-solid fa-phone text-xl"></i></div>
                <div><span class="text-xs text-slate-400 block font-semibold">Teléfono</span><span class="font-bold"><?= e($s['site_phone']) ?></span></div>
            </div>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-amber-500/10 text-amber-600 rounded-xl"><i class="fa-solid fa-envelope text-xl"></i></div>
                <div><span class="text-xs text-slate-400 block font-semibold">Email</span><span class="font-bold"><?= e($s['site_email']) ?></span></div>
            </div>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-amber-500/10 text-amber-600 rounded-xl"><i class="fa-solid fa-map-location-dot text-xl"></i></div>
                <div><span class="text-xs text-slate-400 block font-semibold">Oficinas</span><span class="font-bold"><?= e($s['site_address']) ?></span></div>
            </div>
        </div>
    </div>
    <div class="bg-white p-8 rounded-3xl shadow-xl border border-slate-200">
        <h3 class="text-xl font-bold mb-6">Formulario de Contacto</h3>
        <?php if ($flash): ?>
            <div class="mb-4 p-3 rounded-xl text-sm <?= $flash['type']==='error'?'bg-rose-50 text-rose-700 border-rose-200':'bg-emerald-50 text-emerald-700 border-emerald-200' ?> border">
                <?= e($flash['msg']) ?>
            </div>
        <?php endif; ?>
        <form action="<?= url('actions/guardar_mensaje.php') ?>" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="text" name="name" required placeholder="Nombre completo" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm">
            <div class="grid grid-cols-2 gap-4">
                <input type="email" name="email" required placeholder="Email" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm">
                <input type="tel" name="phone" required placeholder="Teléfono" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm">
            </div>
            <textarea name="message" rows="4" required placeholder="Mensaje..." class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm"></textarea>
            <button class="w-full bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-white font-bold py-3.5 rounded-xl transition-all">
                <i class="fa-solid fa-paper-plane"></i> Enviar
            </button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>