<?php
require_once __DIR__ . '/../config/bootstrap.php';
if (isLoggedIn()) redirect('admin/index.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Administrador</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white max-w-md w-full rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 bg-amber-500/10 text-amber-600 rounded-2xl flex items-center justify-center mx-auto text-xl">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-900">Acceso Administrador</h2>
        </div>

        <?php if (!empty($_GET['error'])): ?>
            <div class="p-3 bg-rose-50 border border-rose-200 text-rose-600 text-xs rounded-xl">
                <i class="fa-solid fa-triangle-exclamation"></i> Credenciales incorrectas.
            </div>
        <?php endif; ?>

        <form action="<?= url('actions/login.php') ?>" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Email</label>
                <input type="email" name="email" required value="admin@luxespace.com"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Contraseña</label>
                <input type="password" name="password" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm">
                <span class="text-[10px] text-slate-400 mt-1 block"> <strong></strong></span>
            </div>
            <button class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-3.5 rounded-xl">
                <i class="fa-solid fa-key"></i> Iniciar Sesión
            </button>
        </form>
    </div>
</body>
</html>