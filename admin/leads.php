<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'change_status') {
        requirePermission('leads.update_status');
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'Pendiente';
        $allowed = ['Pendiente', 'En Proceso', 'Contactado', 'Cerrado', 'Cancelado'];
        if (!in_array($status, $allowed, true)) $status = 'Pendiente';
        db()->prepare("UPDATE leads SET status = ? WHERE id = ?")->execute([$status, $id]);
        flash("Estatus actualizado a «{$status}».");
        redirect('admin/leads.php');
    }

    if ($action === 'delete') {
        requirePermission('leads.delete');
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare("DELETE FROM leads WHERE id = ?")->execute([$id]);
        flash('Solicitud eliminada.');
        redirect('admin/leads.php');
    }

    if ($action === 'delete_closed') {
        requirePermission('leads.delete');
        $stmt = db()->prepare("DELETE FROM leads WHERE status IN ('Cerrado', 'Cancelado')");
        $stmt->execute();
        flash("Se eliminaron {$stmt->rowCount()} solicitud(es) cerrada(s).");
        redirect('admin/leads.php');
    }
}

$pageTitle = 'Solicitudes';
$activeTab = 'leads';
require_once __DIR__ . '/includes/header.php';

$canUpdate = can('leads.update_status');
$canDelete = can('leads.delete');

$filter = $_GET['filter'] ?? 'all';
$where = match ($filter) {
    'pendiente'  => "WHERE status = 'Pendiente'",
    'proceso'    => "WHERE status = 'En Proceso'",
    'contactado' => "WHERE status = 'Contactado'",
    'cerrado'    => "WHERE status IN ('Cerrado','Cancelado')",
    default      => '',
};
$leads = db()->query("SELECT * FROM leads $where ORDER BY created_at DESC")->fetchAll();

$stats = [
    'total'      => (int) db()->query("SELECT COUNT(*) FROM leads")->fetchColumn(),
    'pendiente'  => (int) db()->query("SELECT COUNT(*) FROM leads WHERE status='Pendiente'")->fetchColumn(),
    'proceso'    => (int) db()->query("SELECT COUNT(*) FROM leads WHERE status='En Proceso'")->fetchColumn(),
    'contactado' => (int) db()->query("SELECT COUNT(*) FROM leads WHERE status='Contactado'")->fetchColumn(),
    'cerrado'    => (int) db()->query("SELECT COUNT(*) FROM leads WHERE status IN ('Cerrado','Cancelado')")->fetchColumn(),
];
?>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    <?php
    $cards = [
        ['Total',       $stats['total'],      'fa-list-check',      'slate'],
        ['Pendiente',   $stats['pendiente'],  'fa-hourglass-half',  'amber'],
        ['En Proceso',  $stats['proceso'],    'fa-spinner',         'sky'],
        ['Contactado',  $stats['contactado'], 'fa-phone-volume',    'emerald'],
        ['Cerrado',     $stats['cerrado'],    'fa-circle-check',    'violet'],
    ];
    foreach ($cards as [$label, $val, $icon, $color]): ?>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="p-2.5 bg-<?= $color ?>-500/10 text-<?= $color ?>-600 rounded-xl">
                <i class="fa-solid <?= $icon ?> text-base"></i>
            </div>
            <div>
                <div class="text-xl font-black text-slate-900 leading-none"><?= $val ?></div>
                <div class="text-[10px] uppercase text-slate-500 font-bold mt-1"><?= $label ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="bg-white p-4 rounded-2xl border border-slate-200 mb-6 flex flex-wrap justify-between items-center gap-3">
    <div class="flex gap-2 flex-wrap">
        <?php
        $filters = [
            'all'        => ['Todas',       $stats['total']],
            'pendiente'  => ['Pendiente',   $stats['pendiente']],
            'proceso'    => ['En Proceso',  $stats['proceso']],
            'contactado' => ['Contactado',  $stats['contactado']],
            'cerrado'    => ['Cerradas',    $stats['cerrado']],
        ];
        foreach ($filters as $key => [$label, $count]):
            $isActive = $filter === $key;
        ?>
            <a href="?filter=<?= $key ?>"
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5
                      <?= $isActive ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                <?= $label ?>
                <span class="bg-black/10 px-1.5 py-0.5 rounded text-[10px]"><?= $count ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($canDelete && $stats['cerrado'] > 0): ?>
        <form method="POST" class="inline"
              onsubmit="return confirm('¿Eliminar TODAS las solicitudes cerradas y canceladas?')">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="delete_closed">
            <button type="submit"
                    class="bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs px-4 py-2 rounded-xl border border-rose-200 flex items-center gap-2">
                <i class="fa-solid fa-trash"></i> Eliminar cerradas (<?= $stats['cerrado'] ?>)
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="space-y-4">
    <?php if (empty($leads)): ?>
        <div class="bg-white p-10 rounded-2xl border border-slate-200 text-center">
            <i class="fa-solid fa-inbox text-5xl text-slate-300 mb-3 block"></i>
            <h4 class="font-bold text-slate-700">Sin solicitudes</h4>
        </div>
    <?php else: foreach ($leads as $l):
        $statusStyle = match ($l['status']) {
            'Pendiente'  => ['bg-amber-100 text-amber-800 border-amber-200',    'fa-hourglass-half'],
            'En Proceso' => ['bg-sky-100 text-sky-800 border-sky-200',           'fa-spinner'],
            'Contactado' => ['bg-emerald-100 text-emerald-800 border-emerald-200','fa-phone-volume'],
            'Cerrado'    => ['bg-violet-100 text-violet-800 border-violet-200',  'fa-circle-check'],
            'Cancelado'  => ['bg-rose-100 text-rose-800 border-rose-200',        'fa-circle-xmark'],
            default      => ['bg-slate-100 text-slate-700 border-slate-200',     'fa-circle'],
        };
        [$badgeClass, $badgeIcon] = $statusStyle;
        $clientWa = preg_replace('/[^0-9]/', '', $l['phone'] ?? '');
    ?>
        <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-start gap-4 flex-wrap">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <strong class="text-sm text-slate-900"><?= e($l['name']) ?></strong>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border <?= $badgeClass ?>">
                            <i class="fa-solid <?= $badgeIcon ?>"></i> <?= e($l['status']) ?>
                        </span>
                        <span class="text-[10px] text-slate-400"><i class="fa-solid fa-hashtag"></i> <?= (int)$l['id'] ?></span>
                    </div>
                    <div class="text-xs text-slate-500 flex flex-wrap gap-3">
                        <a href="mailto:<?= e($l['email']) ?>" class="hover:text-amber-600 flex items-center gap-1">
                            <i class="fa-solid fa-envelope text-[10px]"></i> <?= e($l['email']) ?>
                        </a>
                        <?php if (!empty($l['phone'])): ?>
                            <a href="tel:<?= e($l['phone']) ?>" class="hover:text-amber-600 flex items-center gap-1">
                                <i class="fa-solid fa-phone text-[10px]"></i> <?= e($l['phone']) ?>
                            </a>
                        <?php endif; ?>
                        <span class="text-slate-400 flex items-center gap-1">
                            <i class="fa-solid fa-calendar text-[10px]"></i>
                            <?= date('d/m/Y H:i', strtotime($l['created_at'])) ?>
                        </span>
                    </div>
                </div>

                <div class="flex gap-1.5 shrink-0">
                    <?php if ($clientWa): ?>
                        <a href="https://wa.me/<?= e($clientWa) ?>?text=<?= rawurlencode('Hola ' . $l['name'] . ', soy asesor de ' . ($settings['company_name'] ?? 'la inmobiliaria') . '. Recibí tu solicitud sobre: ' . $l['property_title'] . '.') ?>"
                           target="_blank" rel="noopener" title="Responder por WhatsApp"
                           class="p-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                    <?php endif; ?>

                    <a href="mailto:<?= e($l['email']) ?>?subject=Re: <?= e($l['property_title']) ?>"
                       title="Responder por email"
                       class="p-2 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-600">
                        <i class="fa-solid fa-reply"></i>
                    </a>

                    <?php if ($canDelete): ?>
                        <form method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta solicitud?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
                            <button type="submit" title="Eliminar"
                                    class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="px-5 py-3 bg-slate-50 border-b border-slate-100 flex items-center gap-2 flex-wrap">
                <i class="fa-solid fa-house text-amber-500"></i>
                <span class="text-xs font-bold text-slate-700">Interés:</span>
                <span class="text-xs text-amber-700"><?= e($l['property_title']) ?></span>
                <?php if (!empty($l['agent'])): ?>
                    <span class="text-[10px] text-slate-400 ml-auto">Asesor: <strong class="text-slate-600"><?= e($l['agent']) ?></strong></span>
                <?php endif; ?>
            </div>

            <div class="p-5">
                <div class="flex items-start gap-2 mb-2">
                    <i class="fa-solid fa-comment-dots text-slate-400 mt-0.5"></i>
                    <span class="text-xs font-bold uppercase text-slate-500 tracking-wider">Mensaje del cliente</span>
                </div>
                <?php if (!empty($l['message'])): ?>
                    <p class="text-sm text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-100 whitespace-pre-line"><?= e($l['message']) ?></p>
                <?php else: ?>
                    <p class="text-xs text-slate-400 italic bg-slate-50 p-4 rounded-xl border border-dashed border-slate-200">El cliente no dejó mensaje adicional.</p>
                <?php endif; ?>
            </div>

            <?php if ($canUpdate): ?>
                <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">
                        <i class="fa-solid fa-tags"></i> Cambiar estatus:
                    </span>
                    <form method="POST" class="flex items-center gap-2 flex-wrap">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="change_status">
                        <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
                        <select name="status" class="text-xs bg-white border border-slate-300 rounded-lg px-3 py-1.5 font-semibold">
                            <option value="Pendiente"  <?= $l['status']==='Pendiente'?'selected':''  ?>>⏳ Pendiente</option>
                            <option value="En Proceso" <?= $l['status']==='En Proceso'?'selected':'' ?>>🔄 En Proceso</option>
                            <option value="Contactado" <?= $l['status']==='Contactado'?'selected':'' ?>>📞 Contactado</option>
                            <option value="Cerrado"    <?= $l['status']==='Cerrado'?'selected':''    ?>>✅ Cerrado</option>
                            <option value="Cancelado"  <?= $l['status']==='Cancelado'?'selected':''  ?>>❌ Cancelado</option>
                        </select>
                        <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold px-4 py-1.5 rounded-lg">
                            <i class="fa-solid fa-check"></i> Actualizar
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>