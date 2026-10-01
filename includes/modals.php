<!-- ===== 1. Modal Detalle de Propiedad ===== -->
<div id="modal-property-detail" class="fixed inset-0 z-50 modal-backdrop hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white max-w-4xl w-full rounded-3xl overflow-hidden shadow-2xl relative my-8">
        <button onclick="closeModal('modal-property-detail')" class="absolute top-4 right-4 z-10 bg-slate-900/80 text-white w-9 h-9 rounded-full flex items-center justify-center hover:bg-slate-900">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="grid grid-cols-1 md:grid-cols-2">
            <div class="bg-slate-900 p-4 flex flex-col justify-between">
                <div class="relative h-72 rounded-2xl overflow-hidden mb-4">
                    <img id="detail-main-img" src="" alt="Propiedad" class="w-full h-full object-cover">
                </div>
                <div id="detail-thumbnails" class="flex gap-2 flex-wrap"></div>
            </div>

            <div class="p-8 space-y-6 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span id="detail-badge-listing" class="text-xs font-bold bg-amber-100 text-amber-800 px-3 py-1 rounded-full uppercase">Venta</span>
                        <span id="detail-price" class="text-2xl font-black text-slate-900">$0 MXN</span>
                    </div>
                    <h2 id="detail-title" class="text-2xl font-black text-slate-900 mb-2">Título</h2>
                    <p id="detail-location" class="text-xs text-slate-500 flex items-center gap-1.5 mb-4">
                        <i class="fa-solid fa-location-dot text-amber-500"></i> Ubicación
                    </p>
                    <p id="detail-description" class="text-slate-600 text-xs leading-relaxed mb-6">Descripción.</p>

                    <div class="grid grid-cols-3 gap-2 py-3 bg-slate-50 rounded-xl px-4 text-xs font-medium text-slate-700 mb-6">
                        <div><i class="fa-solid fa-bed text-amber-500 mr-1"></i> <span id="detail-bedrooms">0</span> Rec.</div>
                        <div><i class="fa-solid fa-bath text-amber-500 mr-1"></i> <span id="detail-bathrooms">0</span> Baños</div>
                        <div><i class="fa-solid fa-ruler-combined text-amber-500 mr-1"></i> <span id="detail-area">0</span> m²</div>
                    </div>
                </div>

                <div class="space-y-3 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-500/20 text-amber-700 rounded-full flex items-center justify-center font-bold">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block font-semibold">Asesor Comercial Asignado</span>
                            <span id="detail-agent-name" class="text-sm font-bold text-slate-800">—</span>
                        </div>
                    </div>
                    <button onclick="requestLeadFromDetail()" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-3 rounded-xl transition-all shadow-md flex items-center justify-center gap-2 text-sm">
                        <i class="fa-solid fa-envelope"></i> Solicitar Informes / Agendar Cita
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== 2. Modal Captura de Lead ===== -->
<div id="modal-contact-lead" class="fixed inset-0 z-50 modal-backdrop hidden flex items-center justify-center p-4">
    <div class="bg-white max-w-lg w-full rounded-3xl p-8 relative shadow-2xl">
        <button onclick="closeModal('modal-contact-lead')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h3 class="text-2xl font-black text-slate-900 mb-1">Contactar Asesor</h3>
        <p id="lead-modal-subtitle" class="text-xs text-slate-500 mb-6">Completa tus datos para enviarlos al sistema.</p>

        <form onsubmit="handleLeadSubmit(event)" class="space-y-4">
            <input type="hidden" id="lead-property-title" value="">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nombre Completo</label>
                <input type="text" id="lead-name" required placeholder="Tu nombre..." class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Email</label>
                    <input type="email" id="lead-email" required placeholder="correo@ejemplo.com" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Teléfono</label>
                    <input type="tel" id="lead-phone" required placeholder="5512345678" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Mensaje</label>
                <textarea id="lead-message" rows="3" placeholder="Me interesa agendar una cita..." class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
            </div>
            <button type="submit" class="w-full bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane"></i> Enviar Datos
            </button>
        </form>
    </div>
</div>

<!-- ===== 3. Modal Confirmación ===== -->
<div id="modal-success" class="fixed inset-0 z-50 modal-backdrop hidden flex items-center justify-center p-4">
    <div class="bg-white max-w-md w-full rounded-3xl p-8 text-center space-y-4 shadow-2xl">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h3 class="text-2xl font-black text-slate-900">¡Confirmación de Envío!</h3>
        <p class="text-xs text-slate-500 leading-relaxed">
            Tus datos han sido registrados correctamente en la base de datos de nuestro servidor.
            Un asesor se comunicará a la brevedad.
        </p>
        <button onclick="closeModal('modal-success')" class="bg-slate-900 text-white font-bold px-6 py-2.5 rounded-xl text-xs hover:bg-slate-800">
            Entendido
        </button>
    </div>
</div>

<!-- ===== 4. Modal Login Admin ===== -->
<div id="modal-login" class="fixed inset-0 z-50 modal-backdrop hidden flex items-center justify-center p-4">
    <div class="bg-white max-w-md w-full rounded-3xl p-8 relative shadow-2xl space-y-6">
        <button onclick="closeModal('modal-login')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div class="text-center space-y-2">
            <div class="w-12 h-12 bg-amber-500/10 text-amber-600 rounded-2xl flex items-center justify-center mx-auto text-xl">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-900">Acceso Administrador</h2>
            <p class="text-slate-500 text-xs">Autenticación en servidor PHP vía <code>$_SESSION</code></p>
        </div>

        <?php if (!empty($_SESSION['login_error'])): ?>
            <div class="p-3 bg-rose-50 border border-rose-200 text-rose-600 text-xs rounded-xl flex items-center gap-2 font-medium">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= e($_SESSION['login_error']) ?></span>
            </div>
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>

        <div id="login-error-alert" class="hidden p-3 bg-rose-50 border border-rose-200 text-rose-600 text-xs rounded-xl flex items-center gap-2 font-medium">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span id="login-error-text">Credenciales incorrectas.</span>
        </div>

        <form action="<?= url('admin/login.php') ?>" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Usuario / Email</label>
                <input type="email" name="email" required value="admin@luxespace.com"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Contraseña</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                <span class="text-[10px] text-slate-400 mt-1 block">Clave demo: <strong class="text-slate-700">admin123</strong></span>
            </div>
            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-3.5 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-key"></i> Iniciar Sesión PHP
            </button>
        </form>
    </div>
</div>