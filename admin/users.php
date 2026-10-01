<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();

$canEdit = can('users.create') || can('users.edit') || can('users.delete');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canEdit) {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_user') {
        requirePermission('users.create');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = $_POST['role'] ?? 'Asesor Senior';
        if ($name && $email && $password) {
            try {
                db()->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)")
                    ->execute([$name, $email, $password, $role]);
                flash('Usuario agregado.');
            } catch (Throwable $e) {
                flash('Error: el email ya existe.', 'error');
            }
        }
        redirect('admin/users.php');
    }

    if ($action === 'edit_user') {
        requirePermission('users.edit');
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'Asesor Senior';
        $password = trim($_POST['password'] ?? '');
        if ($password !== '') {
            db()->prepare("UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?")
                ->execute([$name, $email, $role, $password, $id]);
        } else {
            db()->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?")
                ->execute([$name, $email, $role, $id]);
        }
        flash('Usuario actualizado.');
        redirect('admin/users.php');
    }

    if ($action === 'delete_user') {
        requirePermission('users.delete');
        $id = (int)($_POST['id'] ?? 0);
        if ($id !== (int)$_SESSION['user_id']) {
            db()->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            flash('Usuario eliminado.');
        } else {
            flash('No puedes eliminar tu propio usuario.', 'error');
        }
        redirect('admin/users.php');
    }
}

$pageTitle = 'Usuarios';
$activeTab = 'users';
require_once __DIR__ . '/includes/header.php';

$users = db()->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<?php if (!$canEdit): ?>
    <div class="mb-6 p-4 rounded-xl border bg-sky-50 border-sky-200 text-sky-800 text-sm flex items-center gap-2">
        <i class="fa-solid fa-eye"></i>
        <span><strong>Modo solo lectura:</strong> Puedes ver el directorio, pero no modificarlo.</span>
    </div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-slate-500">Directorio de personal y asesores autorizados.</p>
    <?php if (can('users.create')): ?>
        <button onclick="openUserModal()" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs px-4 py-2.5 rounded-xl shadow flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Agregar Personal
        </button>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($users as $u): ?>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <div class="flex justify-between items-start">
                <div class="p-3 bg-amber-500/10 text-amber-600 rounded-xl"><i class="fa-solid fa-user-gear text-lg"></i></div>
                <?php if ($canEdit): ?>
                    <div class="flex gap-1">
                        <button onclick='openUserModal(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                class="p-1.5 hover:bg-slate-200 rounded text-slate-600" title="Editar">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <?php if (can('users.delete') && (int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                            <form method="POST" class="inline" onsubmit="return confirm('¿Eliminar usuario?')">
                                <?= csrfField() ?><input type="hidden" name="action" value="delete_user"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="p-1.5 hover:bg-rose-100 rounded text-rose-600" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <h4 class="font-bold text-slate-900 text-base"><?= e($u['name']) ?></h4>
            <span class="text-xs text-amber-600 font-semibold block"><?= e($u['role']) ?></span>
            <span class="text-xs text-slate-400 block"><?= e($u['email']) ?></span>
            <?php if (isAdmin()): ?>
                <div class="text-xs bg-slate-50 rounded-lg px-3 py-2 border border-slate-200">
                    <span class="text-slate-400 font-bold">Contraseña:</span>
                    <code class="text-slate-700 font-mono"><?= e($u['password']) ?></code>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($canEdit): ?>
<div id="modal-user-crud" class="fixed inset-0 z-50 modal-backdrop hidden items-center justify-center p-4">
    <div class="bg-white max-w-md w-full rounded-3xl p-8 relative shadow-2xl space-y-4">
        <button onclick="closeModal('modal-user-crud')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
        <h3 id="user-modal-title" class="text-xl font-bold text-slate-900">Agregar Usuario</h3>

        <form method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" id="user-action" value="add_user">
            <input type="hidden" name="id" id="user-id" value="">

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nombre Completo</label>
                <input type="text" name="name" id="user-name" required class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Email</label>
                <input type="email" name="email" id="user-email" required class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Contraseña</label>
                <input type="text" name="password" id="user-password" class="w-full bg-slate-50 border rounded-xl p-3 text-sm font-mono">
                <p class="text-[10px] text-slate-400 mt-1" id="user-password-hint">Se guarda en texto plano.</p>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Rol</label>
                <select name="role" id="user-role" class="w-full bg-slate-50 border rounded-xl p-3 text-sm">
                    <option value="Asesor Senior">Asesor</option>
                    <option value="Asesor Elite">Empleado</option>
                    <option value="Administrador">Administrador</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 rounded-xl">
                <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar
            </button>
        </form>
    </div>
</div>

<script>
function openUserModal(data = null) {
    const title = document.getElementById('user-modal-title');
    const action = document.getElementById('user-action');
    const passHint = document.getElementById('user-password-hint');
    if (data) {
        title.innerText = 'Editar Usuario';
        action.value = 'edit_user';
        document.getElementById('user-id').value = data.id;
        document.getElementById('user-name').value = data.name;
        document.getElementById('user-email').value = data.email;
        document.getElementById('user-role').value = data.role;
        document.getElementById('user-password').value = '';
        document.getElementById('user-password').required = false;
        passHint.innerText = 'Deja vacío para mantener la contraseña actual.';
    } else {
        title.innerText = 'Agregar Usuario';
        action.value = 'add_user';
        document.getElementById('user-id').value = '';
        document.getElementById('user-name').value = '';
        document.getElementById('user-email').value = '';
        document.getElementById('user-role').value = 'Asesor Senior';
        document.getElementById('user-password').value = '';
        document.getElementById('user-password').required = true;
        passHint.innerText = 'Se guarda en texto plano.';
    }
    openModal('modal-user-crud');
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>