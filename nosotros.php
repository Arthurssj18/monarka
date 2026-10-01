<?php
require_once __DIR__ . '/config/bootstrap.php';

$pageTitle  = 'Nosotros';
$activePage = 'nosotros';

require __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div>
            <span class="text-amber-600 font-bold text-xs uppercase tracking-widest block mb-1">Sobre Monarka Inmobiliaria</span>
            <h1 class="text-4xl font-black text-slate-900 mb-6">Líderes en Comercialización Inmobiliaria</h1>
            <p class="text-slate-600 mb-4 leading-relaxed">
                Desde hace más de una década, MonarkA Inmobiliarias se ha posicionado como la firma de referencia
                en gestión patrimonial y bienes raíces premium. Nos enfocamos en ofrecer certeza
                jurídica y valor sostenido a cada transacción.
            </p>
            <div class="grid grid-cols-2 gap-4 mt-8">
                <div class="bg-slate-100 p-4 rounded-xl border border-slate-200">
                    <span class="text-3xl font-black text-slate-900 block">+200</span>
                    <span class="text-xs text-slate-500 font-semibold uppercase">Inmuebles Entregados</span>
                </div>
                <div class="bg-slate-100 p-4 rounded-xl border border-slate-200">
                    <span class="text-3xl font-black text-slate-900 block">99.4%</span>
                    <span class="text-xs text-slate-500 font-semibold uppercase">Satisfacción de Clientes</span>
                </div>
            </div>
        </div>
        <div>
            <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1000&q=80"
                 alt="Nosotros Inmobiliaria" class="rounded-3xl shadow-2xl border border-slate-200">
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>