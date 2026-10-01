<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_read') {
        requirePermission('messages.mark_read');
        $id = (int)($_POST['id'] ?? 0);
        $cur = db()->prepare("SELECT is_read FROM messages WHERE id = ?");
        $cur->execute([$id]); $row = $cur->fetch();
        if ($row) {
            $new = $row['is_read'] ? 0 : 1;
            db()->prepare("UPDATE messages SET is_read = ? WHERE id = ?")->execute([$new, $id]);
            flash($new ? 'Mensaje marcado como leído.' : 'Mensaje marcado como no leído.');
        }
        redirect('admin/messages.php');
    }

    if ($action === 'mark_all_read') {
        requirePermission('messages.mark_read');
        db()->prepare("UPDATE messages SET is_read = 1 WHERE is_read = 0")->execute();
        flash('Todos los mensajes marcados como leídos.');
        redirect('admin/messages.php');
    }

    if ($action === 'delete') {
        requirePermission('messages.delete');
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare("DELETE FROM messages WHERE id = ?")->execute([$id]);
        flash('Mensaje eliminado.');
        redirect('admin/messages.php');
    }

    if ($action === 'delete_read') {
        requirePermission('messages.delete');
        $stmt = db()->prepare("DELETE FROM messages WHERE is_read = 1");
        $stmt->execute();
        flash("Se eliminaron {$stmt->rowCount()} mensaje(s) leído(s).");
        redirect('admin/messages.php');
    }
}

$pageTitle = 'Mensajes';
$activeTab = 'messages';
require_once __DIR__ . '/includes/header.php';

$canRead   = can('messages.mark_read');
$canDelete = can('messages.delete');

$totalMsgs  = (int) db()->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$unreadMsgs = (int) db()->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
$readMsgs   = $totalMsgs - $unreadMsgs;

$filter = $_GET['filter'] ?? 'all';
$where = match ($filter) {
    'unread' => 'WHERE is_read = 0',
    'read'   => 'WHERE is_read = 1',
    default  => '',
};
$msgs = db()->query("SELECT * FROM messages $where ORDER BY is_read ASC, created_at DESC")->fetchAll();
?>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-slate-100 text-slate-600 rounded-xl"><i class="fa-solid fa-envelope text-lg"></i></div>
        <div><div class="text-2xl font-black text-slate-900"><?= $totalMsgs ?></div><div class="text-[10px] uppercase text-slate-500 font-bold tracking-wider">Totales</div></div>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-rose-200 bg-rose-50/30 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-rose-100 text-rose-600 rounded-xl"><i class="fa-solid fa-envelope-open-text text-lg"></i></div>
        <div><div class="text-2xl font-black text-rose-700"><?= $unreadMsgs ?></div><div class="text-[10px] uppercase text-rose-500 font-bold tracking-wider">Sin leer</div></div>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-emerald-200 bg-emerald-50/30 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl"><i class="fa-solid fa-check-double text-lg"></i></div>
        <div><div class="text-2xl font-black text-emerald-700"><?= $readMsgs ?></div><div class="text-[10px] uppercase text-emerald-500 font-bold tracking-wider">Leídos</div></div>
    </div>
</div>

<div class="bg-white p-4 rounded-2xl border border-slate-200 mb-6 flex flex-wrap justify-between items-center gap-3">
    <div class="flex gap-2">
        <?php
        $filters = [
            'all'    => ['Todos', $totalMsgs],
            'unread' => ['Sin leer', $unreadMsgs],
            'read'   => ['Leídos', $readMsgs],
        ];
        foreach ($filters as $key => [$label, $count]):
            $isActive = $filter === $key;
        ?>
            <a href="?filter=<?= $key ?>" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= $isActive ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                <?= $label ?> <span class="ml-1.5 bg-black/10 px-1.5 py-0.5 rounded text-[10px]"><?= $count ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="flex gap-2 flex-wrap">
        <?php if ($canRead && $unreadMsgs > 0): ?>
            <form method="POST" class="inline">
                <?= csrfField() ?><input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs px-4 py-2 rounded-xl flex items-center gap-2">
                    <i class="fa-solid fa-check-double"></i> Marcar todos como leídos
                </button>
            </form>
        <?php endif; ?>

        <?php if ($canDelete && $readMsgs > 0): ?>
            <form method="POST" class="inline" onsubmit="return confirm('¿Eliminar TODOS los mensajes leídos?')">
                <?= csrfField() ?><input type="hidden" name="action" value="delete_read">
                <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs px-4 py-2 rounded-xl border border-rose-200 flex items-center gap-2">
                    <i class="fa-solid fa-trash"></i> Eliminar leídos (<?= $readMsgs ?>)
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="space-y-4">
    <?php if (empty($msgs)): ?>
        <div class="bg-white p-10 rounded-2xl border border-slate-200 text-center">
            <i class="fa-solid fa-inbox text-5xl text-slate-300 mb-3 block"></i>
            <h4 class="font-bold text-slate-700"><?= $filter === 'unread' ? 'No hay mensajes sin leer' : ($filter === 'read' ? 'No hay mensajes leídos' : 'Sin mensajes') ?></h4>
        </div>
    <?php else: foreach ($msgs as $m): $isUnread = !$m['is_read']; ?>
        <div class="bg-white p-6 rounded-2xl border shadow-sm space-y-3 transition-all <?= $isUnread ? 'border-l-4 border-l-rose-500 border-slate-200' : 'border-slate-200 opacity-80' ?>">
            <div class="flex justify-between items-start gap-4 flex-wrap">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <strong class="text-sm text-slate-900"><?= e($m['name']) ?></strong>
                        <?php if ($isUnread): ?>
                            <span class="bg-rose-100 text-rose-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-rose-200"><i class="fa-solid fa-circle text-[6px]"></i> NUEVO</span>
                        <?php else: ?>
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-200"><i class="fa-solid fa-check"></i> Leído</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-3">
                        <a href="mailto:<?= e($m['email']) ?>" class="hover:text-amber-600 flex items-center gap-1">
                            <i class="fa-solid fa-envelope text-[10px]"></i> <?= e($m['email']) ?>
                        </a>
                        <?php if (!empty($m['phone'])): ?>
                            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $m['phone'])) ?>" class="hover:text-amber-600 flex items-center gap-1">
                                <i class="fa-solid fa-phone text-[10px]"></i> <?= e($m['phone']) ?>
                            </a>
                        <?php endif; ?>
                        <span class="text-slate-400 flex items-center gap-1">
                            <i class="fa-solid fa-calendar text-[10px]"></i>
                            <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
                        </span>
                    </div>
                </div>

                <div class="flex gap-1.5">
                    <?php if ($canRead): ?>
                        <form method="POST" class="inline">
                            <?= csrfField() ?><input type="hidden" name="action" value="toggle_read"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button type="submit" title="<?= $isUnread ? 'Marcar como leído' : 'Marcar como no leído' ?>"
                                    class="p-2 rounded-lg transition-colors <?= $isUnread ? 'hover:bg-emerald-100 text-emerald-600' : 'hover:bg-amber-100 text-amber-600' ?>">
                                <i class="fa-solid <?= $isUnread ? 'fa-check' : 'fa-envelope' ?>"></i>
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($canDelete): ?>
                        <form method="POST" class="inline" onsubmit="return confirm('¿Eliminar este mensaje?')">
                            <?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button type="submit" title="Eliminar" class="p-2 rounded-lg hover:bg-rose-100 text-rose-600">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                <p class="text-sm text-slate-700 whitespace-pre-line"><?= e($m['message']) ?></p>
            </div>

            <div class="flex gap-2">
                <a href="mailto:<?= e($m['email']) ?>?subject=Re: Consulta inmobiliaria" class="text-xs text-slate-500 hover:text-amber-600 flex items-center gap-1 font-semibold">
                    <i class="fa-solid fa-reply"></i> Responder por email
                </a>
                <?php if (!empty($m['phone'])): ?>
                    <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $m['phone'])) ?>?text=Hola%20<?= urlencode($m['name']) ?>,%20gracias%20por%20contactarnos."
                       target="_blank" rel="noopener"
                       class="text-xs text-slate-500 hover:text-emerald-600 flex items-center gap-1 font-semibold">
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>