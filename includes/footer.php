<?php
$footer       = getFooterSettings();
$socialIcon   = socialIconMap();
$menuLinks    = parseFooterLinks($footer['footer_links_menu']);
$serviceLinks = parseFooterLinks($footer['footer_links_services']);
$settings     = $settings ?? getSettings();
$logoSrc      = getLogoSrc($settings);
?>
</main>

<!-- ===================== FOOTER ===================== -->
<footer class="bg-slate-950 text-slate-400 pt-16 pb-6 mt-auto border-t border-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-12 border-b border-slate-800">

            <!-- Col 1: Logo + descripción + redes -->
            <div class="space-y-5">
                <a href="<?= url('index.php') ?>" class="inline-flex items-center gap-3 group">
                    <?php if ($logoSrc): ?>
                        <img src="<?= e($logoSrc) ?>" alt="<?= e($settings['company_name']) ?>"
                             class="h-12 w-auto max-w-[160px] object-contain brightness-0 invert group-hover:scale-105 transition-transform">
                    <?php else: ?>
                        <div class="bg-gradient-to-tr from-amber-600 to-amber-400 text-slate-950 p-2.5 rounded-xl">
                            <i class="fa-solid fa-building-user text-xl"></i>
                        </div>
                        <span class="text-lg font-black text-white"><?= e($settings['company_name']) ?></span>
                    <?php endif; ?>
                </a>

                <p class="text-sm text-slate-400 leading-relaxed">
                    <?= nl2br(e($footer['footer_description'])) ?>
                </p>

                <?php
                $anySocial = false;
                foreach ($socialIcon as $k => $i) {
                    if (!empty($footer['footer_social_' . $k])) { $anySocial = true; break; }
                }
                ?>
                <?php if ($anySocial): ?>
                    <div class="flex flex-wrap gap-2 pt-2">
                        <?php foreach ($socialIcon as $key => $icon):
                            $url = trim($footer['footer_social_' . $key] ?? '');
                            if (!$url) continue;

                            $isWhatsapp = ($key === 'whatsapp');
                            $baseClass  = $isWhatsapp
                                ? 'bg-emerald-500/20 hover:bg-emerald-500 text-emerald-400 hover:text-white border-emerald-500/40 hover:border-emerald-500'
                                : 'bg-slate-900 hover:bg-amber-500 text-slate-300 hover:text-slate-950 border-slate-800 hover:border-amber-500';
                        ?>
                            <a href="<?= e($url) ?>" target="_blank" rel="noopener" title="<?= e(ucfirst($key)) ?>"
                               class="w-9 h-9 rounded-full <?= $baseClass ?> flex items-center justify-center transition-all border">
                                <i class="fa-brands <?= $icon ?> text-sm"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Col 2: Enlaces menú -->
            <div>
                <h4 class="text-white font-bold text-sm uppercase tracking-widest mb-5">
                    <?= e($footer['footer_links_menu_title']) ?>
                </h4>
                <?php if (empty($menuLinks)): ?>
                    <p class="text-xs text-slate-500 italic">Sin enlaces configurados.</p>
                <?php else: ?>
                    <ul class="space-y-2.5 text-sm">
                        <?php foreach ($menuLinks as $l): ?>
                            <li>
                                <a href="<?= e($l['url']) ?>" class="text-slate-400 hover:text-amber-400 flex items-center gap-2 transition-colors">
                                    <i class="fa-solid fa-angle-right text-[10px] text-amber-500"></i>
                                    <?= e($l['label']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Col 3: Enlaces servicios -->
            <div>
                <h4 class="text-white font-bold text-sm uppercase tracking-widest mb-5">
                    <?= e($footer['footer_links_services_title']) ?>
                </h4>
                <?php if (empty($serviceLinks)): ?>
                    <p class="text-xs text-slate-500 italic">Sin enlaces configurados.</p>
                <?php else: ?>
                    <ul class="space-y-2.5 text-sm">
                        <?php foreach ($serviceLinks as $l): ?>
                            <li>
                                <a href="<?= e($l['url']) ?>" class="text-slate-400 hover:text-amber-400 flex items-center gap-2 transition-colors">
                                    <i class="fa-solid fa-angle-right text-[10px] text-amber-500"></i>
                                    <?= e($l['label']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Col 4: Contacto -->
            <div class="space-y-5">
                <h4 class="text-white font-bold text-sm uppercase tracking-widest mb-5">Información</h4>

                <?php if (!empty($footer['footer_hours'])): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-clock text-sm"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase text-slate-500 tracking-wider"><?= e($footer['footer_hours_label']) ?></span>
                            <span class="text-sm text-slate-300"><?= e($footer['footer_hours']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($footer['footer_address'])): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-location-dot text-sm"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase text-slate-500 tracking-wider"><?= e($footer['footer_address_label']) ?></span>
                            <span class="text-sm text-slate-300"><?= e($footer['footer_address']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($footer['footer_email'])): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-envelope text-sm"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase text-slate-500 tracking-wider"><?= e($footer['footer_email_label']) ?></span>
                            <a href="mailto:<?= e($footer['footer_email']) ?>" class="text-sm text-slate-300 hover:text-amber-400 transition-colors">
                                <?= e($footer['footer_email']) ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Subfooter -->
        <div class="pt-6 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500">
            <p><?= e($footer['footer_copyright']) ?></p>
            <ul class="flex gap-6">
                <?php if (!empty($footer['footer_legal_terms'])): ?>
                    <li><a href="<?= e($footer['footer_legal_terms_url'] ?: '#') ?>" class="hover:text-amber-400 transition-colors"><?= e($footer['footer_legal_terms']) ?></a></li>
                <?php endif; ?>
                <?php if (!empty($footer['footer_legal_privacy'])): ?>
                    <li><a href="<?= e($footer['footer_legal_privacy_url'] ?: '#') ?>" class="hover:text-amber-400 transition-colors"><?= e($footer['footer_legal_privacy']) ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</footer>

<!-- =========================================================
 *  LIGHTBOX: Visor de imágenes con navegación
 * ========================================================= -->
<div id="lightbox"
     class="fixed inset-0 z-[200] hidden items-center justify-center bg-slate-950/95 backdrop-blur-sm p-4 opacity-0 transition-opacity duration-200"
     onclick="closeLightbox(event)">

    <!-- Cerrar -->
    <button type="button" onclick="closeLightbox()"
            class="absolute top-4 right-4 w-12 h-12 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors z-20">
        <i class="fa-solid fa-xmark text-xl"></i>
    </button>

    <!-- Pantalla completa -->
    <button type="button"
            onclick="event.stopPropagation(); toggleFullscreenLightbox()"
            class="absolute top-4 right-20 w-12 h-12 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors z-20"
            title="Pantalla completa">
        <i id="lightbox-fullscreen-icon" class="fa-solid fa-expand text-lg"></i>
    </button>

    <!-- Contador superior -->
    <div id="lightbox-counter-top"
         class="hidden absolute top-4 left-4 bg-slate-900/80 backdrop-blur text-white text-sm font-bold px-4 py-2 rounded-full z-20 flex items-center gap-2">
        <i class="fa-solid fa-images text-amber-400"></i>
        <span id="lightbox-counter">1 / 1</span>
    </div>

    <!-- Contenido central -->
    <div class="relative w-full max-w-6xl h-full flex flex-col items-center justify-center gap-4"
         onclick="event.stopPropagation()">

        <!-- Flecha izquierda -->
        <button id="lightbox-prev"
                type="button"
                onclick="event.stopPropagation(); lightboxPrev()"
                class="hidden absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 w-14 h-14 rounded-full bg-white/10 hover:bg-amber-500 text-white hover:text-slate-950 backdrop-blur items-center justify-center transition-all shadow-lg hover:scale-110 z-20">
            <i class="fa-solid fa-chevron-left text-2xl"></i>
        </button>

        <!-- Imagen -->
        <img id="lightbox-img"
             src=""
             alt=""
             class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl transition-opacity duration-200 select-none">

        <!-- Flecha derecha -->
        <button id="lightbox-next"
                type="button"
                onclick="event.stopPropagation(); lightboxNext()"
                class="hidden absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 w-14 h-14 rounded-full bg-white/10 hover:bg-amber-500 text-white hover:text-slate-950 backdrop-blur items-center justify-center transition-all shadow-lg hover:scale-110 z-20">
            <i class="fa-solid fa-chevron-right text-2xl"></i>
        </button>

        <!-- Caption + puntitos -->
        <div class="flex flex-col items-center gap-3 w-full">
            <div id="lightbox-caption"
                 class="text-center text-white text-sm font-semibold px-4 py-2 bg-slate-900/70 rounded-full backdrop-blur-sm max-w-[90%]"></div>
            <div id="lightbox-dots" class="hidden gap-1.5 items-center"></div>
        </div>
    </div>
</div>

<!-- ============ MODAL: Contactar Asesor ============ -->
<div id="modal-contact-lead" class="fixed inset-0 z-[100] modal-backdrop hidden items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white max-w-lg w-full rounded-3xl p-8 relative shadow-2xl my-8">
        <button type="button" onclick="closeModal('modal-contact-lead')"
                class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h3 class="text-2xl font-black text-slate-900 mb-1">Contactar Asesor</h3>
        <p id="lead-modal-subtitle" class="text-xs text-slate-500 mb-6">
            Completa tus datos y un asesor te contactará a la brevedad.
        </p>

        <form action="<?= url('actions/guardar_lead.php') ?>" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="property_title" id="lead-property-title" value="">

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nombre Completo</label>
                <input type="text" name="name" required placeholder="Tu nombre..."
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Email</label>
                    <input type="email" name="email" required placeholder="correo@ejemplo.com"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Teléfono</label>
                    <input type="tel" name="phone" required placeholder="5512345678"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Mensaje (opcional)</label>
                <textarea name="message" rows="3" placeholder="Me interesa agendar una cita..."
                          class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
            </div>

            <button type="submit"
                    class="w-full bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane"></i> Enviar Solicitud
            </button>
        </form>
    </div>
</div>

<!-- ============ MODAL: Confirmación ============ -->
<div id="modal-success" class="fixed inset-0 z-[100] modal-backdrop hidden items-center justify-center p-4">
    <div class="bg-white max-w-md w-full rounded-3xl p-8 text-center space-y-4 shadow-2xl">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h3 class="text-2xl font-black text-slate-900">¡Confirmación de Envío!</h3>
        <p class="text-xs text-slate-500 leading-relaxed">
            Tus datos han sido registrados correctamente. Un asesor se comunicará a la brevedad.
        </p>
        <button type="button" onclick="closeModal('modal-success')"
                class="bg-slate-900 text-white font-bold px-6 py-2.5 rounded-xl text-xs hover:bg-slate-800">
            Entendido
        </button>
    </div>
</div>

<!-- ============ TOAST ============ -->
<div id="toast-container" class="fixed top-12 right-5 z-[110] hidden bg-slate-900 text-white px-5 py-3 rounded-xl shadow-2xl border border-amber-500/30 items-center gap-3">
    <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
    <span id="toast-message-text" class="text-sm font-medium">Operación realizada con éxito.</span>
</div>

<script src="<?= asset('js/main.js') ?>"></script>

<script>
/* =========================================================
 *  LIGHTBOX con navegación
 * ========================================================= */
(function () {
    const lb = document.getElementById('lightbox');
    if (!lb) return;

    let currentImages = [];
    let currentIndex  = 0;
    let currentTitle  = '';

    function renderLightbox() {
        const img        = document.getElementById('lightbox-img');
        const caption    = document.getElementById('lightbox-caption');
        const counter    = document.getElementById('lightbox-counter');
        const counterTop = document.getElementById('lightbox-counter-top');
        const prevBtn    = document.getElementById('lightbox-prev');
        const nextBtn    = document.getElementById('lightbox-next');
        const dotsBox    = document.getElementById('lightbox-dots');

        const total       = currentImages.length;
        const hasMultiple = total > 1;

        /* Fade out → cambiar src → fade in */
        img.style.opacity = '0';
        setTimeout(() => {
            img.src = currentImages[currentIndex] || '';
            img.style.opacity = '1';
        }, 100);

        /* Caption */
        const suffix = hasMultiple ? ` (${currentIndex + 1}/${total})` : '';
        caption.innerText = currentTitle + suffix;

        /* Contador superior */
        if (hasMultiple) {
            counterTop.classList.remove('hidden');
            counterTop.classList.add('flex');
            counter.innerText = (currentIndex + 1) + ' / ' + total;
        } else {
            counterTop.classList.add('hidden');
            counterTop.classList.remove('flex');
        }

        /* Flechas */
        if (hasMultiple) {
            prevBtn.classList.remove('hidden');
            nextBtn.classList.remove('hidden');
            prevBtn.classList.add('flex');
            nextBtn.classList.add('flex');
        } else {
            prevBtn.classList.add('hidden');
            nextBtn.classList.add('hidden');
            prevBtn.classList.remove('flex');
            nextBtn.classList.remove('flex');
        }

        /* Puntitos (2-10 imágenes) */
        if (hasMultiple && total <= 10) {
            dotsBox.classList.remove('hidden');
            dotsBox.classList.add('flex');
            dotsBox.innerHTML = '';
            for (let i = 0; i < total; i++) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'rounded-full transition-all ' +
                    (i === currentIndex ? 'bg-amber-500 w-6 h-2' : 'bg-white/40 hover:bg-white/70 w-2 h-2');
                dot.onclick = (e) => {
                    e.stopPropagation();
                    currentIndex = i;
                    renderLightbox();
                };
                dotsBox.appendChild(dot);
            }
        } else {
            dotsBox.classList.add('hidden');
            dotsBox.classList.remove('flex');
        }
    }

    window.openLightbox = function (src, title, images, index) {
        currentTitle = title || '';

        if (Array.isArray(images) && images.length > 0) {
            currentImages = images;
            currentIndex  = (typeof index === 'number' && index >= 0 && index < images.length) ? index : 0;
        } else {
            currentImages = [src];
            currentIndex  = 0;
        }

        lb.classList.remove('hidden');
        lb.classList.add('flex');
        requestAnimationFrame(() => lb.classList.remove('opacity-0'));

        renderLightbox();
        document.body.style.overflow = 'hidden';
    };

    window.closeLightbox = function (e) {
        if (e && e.target !== lb) return;

        lb.classList.add('opacity-0');
        setTimeout(() => {
            lb.classList.add('hidden');
            lb.classList.remove('flex');
            document.getElementById('lightbox-img').src = '';
            document.getElementById('lightbox-caption').innerText = '';
            document.getElementById('lightbox-counter-top').classList.add('hidden');
            document.getElementById('lightbox-dots').classList.add('hidden');
            document.body.style.overflow = '';
            currentImages = [];
            currentIndex  = 0;
        }, 200);
    };

    window.lightboxNext = function () {
        if (currentImages.length < 2) return;
        currentIndex = (currentIndex + 1) % currentImages.length;
        renderLightbox();
    };

    window.lightboxPrev = function () {
        if (currentImages.length < 2) return;
        currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
        renderLightbox();
    };

    window.toggleFullscreenLightbox = function () {
        const container = document.getElementById('lightbox-img').parentElement;
        const icon = document.getElementById('lightbox-fullscreen-icon');

        if (!document.fullscreenElement) {
            (container.requestFullscreen || container.webkitRequestFullscreen || container.msRequestFullscreen).call(container);
            icon.classList.remove('fa-expand');
            icon.classList.add('fa-compress');
        } else {
            (document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen).call(document);
            icon.classList.remove('fa-compress');
            icon.classList.add('fa-expand');
        }
    };

    document.addEventListener('keydown', function (e) {
        if (lb.classList.contains('hidden')) return;
        const tag = (e.target.tagName || '').toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

        if (e.key === 'ArrowRight')      { e.preventDefault(); window.lightboxNext(); }
        else if (e.key === 'ArrowLeft')  { e.preventDefault(); window.lightboxPrev(); }
        else if (e.key === 'Escape')     { window.closeLightbox(); }
    });

    console.log('[Lightbox] Listo ✅');
})();
</script>

<script>
/* ============ MODALES GENERALES ============ */
window.openContactModal = function (propertyTitle) {
    const sub = document.getElementById('lead-modal-subtitle');
    const hidden = document.getElementById('lead-property-title');
    if (propertyTitle) {
        sub.innerHTML = 'Solicitaste información sobre: <strong>' + propertyTitle + '</strong>';
        hidden.value = propertyTitle;
    } else {
        sub.innerText = 'Completa tus datos y un asesor te contactará a la brevedad.';
        hidden.value = '';
    }
    const m = document.getElementById('modal-contact-lead');
    if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
};

window.openModal = function (id) {
    const m = document.getElementById(id);
    if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
};

window.closeModal = function (id) {
    const m = document.getElementById(id);
    if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
};
</script>
</body>
</html>
