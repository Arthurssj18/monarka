<?php
$pageTitle = 'Dashboard';
$activeTab = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$stats = [
    'properties' => (int) db()->query("SELECT COUNT(*) FROM properties")->fetchColumn(),
    'paused'     => (int) db()->query("SELECT COUNT(*) FROM properties WHERE is_paused=1 AND is_sold=0")->fetchColumn(),
    'sold'       => (int) db()->query("SELECT COUNT(*) FROM properties WHERE is_sold=1")->fetchColumn(),
    'visible'    => (int) db()->query("SELECT COUNT(*) FROM properties WHERE is_paused=0 AND is_sold=0")->fetchColumn(),
    'leads'      => (int) db()->query("SELECT COUNT(*) FROM leads")->fetchColumn(),
    'users'      => (int) db()->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'messages'   => (int) db()->query("SELECT COUNT(*) FROM messages")->fetchColumn(),
];
$recentLeads = db()->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <?php foreach ([
        ['Propiedades',  $stats['properties'], 'fa-house',              'amber'],
        ['Solicitudes',  $stats['leads'],      'fa-clipboard-list',     'emerald'],
        ['Personal',     $stats['users'],      'fa-users',              'sky'],
        ['Mensajes',     $stats['messages'],   'fa-envelope-open-text', 'violet'],
    ] as [$label, $val, $icon, $color]): ?>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start mb-3">
                <div class="p-3 bg-<?= $color ?>-500/10 text-<?= $color ?>-600 rounded-xl"><i class="fa-solid <?= $icon ?> text-lg"></i></div>
                <span class="text-3xl font-black text-slate-900"><?= $val ?></span>
            </div>
            <span class="text-xs font-bold uppercase text-slate-400"><?= $label ?></span>
        </div>
    <?php endforeach; ?>
</div>

<div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl p-6 mb-8 text-white shadow-lg">
    <h3 class="font-bold text-lg mb-5 flex items-center gap-2"><i class="fa-solid fa-chart-pie text-amber-400"></i> Estado del Catálogo</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ([
            ['Totales', $stats['properties'], 'fa-house', 'amber'],
            ['Visibles', $stats['visible'], 'fa-eye', 'emerald'],
            ['Pausadas', $stats['paused'], 'fa-pause', 'orange'],
            ['Vendidas', $stats['sold'], 'fa-circle-check', 'sky'],
        ] as [$l, $v, $i, $c]): ?>
            <div class="bg-white/5 backdrop-blur rounded-xl p-5 border border-white/10">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-<?= $c ?>-500/20 text-<?= $c ?>-400 rounded-xl"><i class="fa-solid <?= $i ?> text-xl"></i></div>
                    <div>
                        <div class="text-3xl font-black"><?= $v ?></div>
                        <div class="text-[10px] uppercase text-slate-400 font-bold mt-1"><?= $l ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="font-bold text-slate-900">Últimas Solicitudes</h3>
        <a href="<?= url('admin/leads.php') ?>" class="text-xs text-amber-600 font-bold">Ver todas →</a>
    </div>
    <table class="w-full text-left text-xs text-slate-600">
        <thead class="bg-slate-50 border-b uppercase font-bold text-slate-700">
            <tr><th class="p-4">Cliente</th><th class="p-4">Contacto</th><th class="p-4">Interés</th><th class="p-4">Estatus</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (empty($recentLeads)): ?>
                <tr><td colspan="4" class="p-6 text-center text-slate-400">Sin solicitudes.</td></tr>
            <?php else: foreach ($recentLeads as $l): ?>
                <tr>
                    <td class="p-4 font-bold text-slate-900"><?= e($l['name']) ?></td>
                    <td class="p-4"><?= e($l['email']) ?><span class="block text-[10px] text-slate-400"><?= e($l['phone']) ?></span></td>
                    <td class="p-4 text-amber-700"><?= e($l['property_title']) ?></td>
                    <td class="p-4"><span class="px-2 py-1 rounded text-[10px] font-bold <?= $l['status']==='Pendiente'?'bg-amber-100 text-amber-800':'bg-emerald-100 text-emerald-800' ?>"><?= e($l['status']) ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>