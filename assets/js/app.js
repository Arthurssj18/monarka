/* =========================================================
   LuxeSpace Inmobiliaria - JavaScript principal
   ========================================================= */

let selectedPropertyForDetail = null;

/* ---------- Modales ---------- */
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('hidden');
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

/* Cerrar modal al hacer clic en el fondo */
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.classList.add('hidden');
    }
});

/* Cerrar con tecla ESC */
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop').forEach(m => m.classList.add('hidden'));
    }
});

/* ---------- Toast ---------- */
function showToast(msg, type = 'success') {
    const container = document.getElementById('toast-container');
    const text      = document.getElementById('toast-message-text');
    if (!container || !text) return;

    text.innerText = msg;
    container.classList.remove('hidden');
    container.classList.add('flex');

    clearTimeout(window.__toastTimer);
    window.__toastTimer = setTimeout(() => {
        container.classList.add('hidden');
        container.classList.remove('flex');
    }, 3500);
}

/* ---------- Login ---------- */
function openLoginModal() {
    const err = document.getElementById('login-error-alert');
    if (err) err.classList.add('hidden');
    openModal('modal-login');
}

/* ---------- Contacto / Leads ---------- */
function openContactModal(title = null) {
    const sub = document.getElementById('lead-modal-subtitle');
    const hidden = document.getElementById('lead-property-title');

    if (sub) {
        sub.innerText = title
            ? `Solicitaste información sobre: "${title}"`
            : 'Completa tus datos para enviarlos al servidor.';
    }
    if (hidden) hidden.value = title || '';

    openModal('modal-contact-lead');
}

/* ---------- Detalle de propiedad ---------- */
function openPropertyDetail(id) {
    const list = window.LUXE_PROPERTIES || [];
    const p = list.find(x => Number(x.id) === Number(id));
    if (!p) return;

    selectedPropertyForDetail = p;

    document.getElementById('detail-title').innerText       = p.title;
    document.getElementById('detail-price').innerText       = '$' + Number(p.price).toLocaleString('es-MX') + ' MXN';
    document.getElementById('detail-location').innerHTML    = `<i class="fa-solid fa-location-dot text-amber-500"></i> ${p.location}`;
    document.getElementById('detail-description').innerText = p.description || '';
    document.getElementById('detail-bedrooms').innerText    = p.bedrooms;
    document.getElementById('detail-bathrooms').innerText   = p.bathrooms;
    document.getElementById('detail-area').innerText        = p.areaSqm;
    document.getElementById('detail-agent-name').innerText  = p.agentName || 'Asesor LuxeSpace';
    document.getElementById('detail-badge-listing').innerText = p.listingType;

    const mainImg = document.getElementById('detail-main-img');
    mainImg.src = (p.images && p.images[0]) ? p.images[0] : '';

    const thumbs = document.getElementById('detail-thumbnails');
    thumbs.innerHTML = (p.images || []).map(url => `
        <img src="${url}"
             onclick="document.getElementById('detail-main-img').src='${url}'"
             class="w-16 h-12 object-cover rounded-lg cursor-pointer border-2 border-slate-700 hover:border-amber-500 transition-all">
    `).join('');

    openModal('modal-property-detail');
}

function requestLeadFromDetail() {
    const title = selectedPropertyForDetail ? selectedPropertyForDetail.title : null;
    closeModal('modal-property-detail');
    openContactModal(title);
}

/* ---------- Envío de Lead (fetch → API PHP) ---------- */
async function handleLeadSubmit(e) {
    e.preventDefault();

    const payload = {
        name:    document.getElementById('lead-name').value,
        email:   document.getElementById('lead-email').value,
        phone:   document.getElementById('lead-phone').value,
        message: document.getElementById('lead-message').value,
        property_title: document.getElementById('lead-property-title').value
    };

    try {
        const res = await fetch(window.BASE_URL + '/api/leads.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.ok) {
            closeModal('modal-contact-lead');
            e.target.reset();
            openModal('modal-success');
        } else {
            showToast(data.error || 'Error al enviar la solicitud');
        }
    } catch (err) {
        showToast('Error de conexión con el servidor');
    }
}

/* ---------- Formulario de contacto general ---------- */
async function handleGeneralContactSubmit(e) {
    e.preventDefault();
    const form = e.target;

    const payload = {
        name:    form.querySelector('[name="name"]').value,
        email:   form.querySelector('[name="email"]').value,
        phone:   form.querySelector('[name="phone"]').value,
        message: form.querySelector('[name="message"]').value
    };

    try {
        const res = await fetch(window.BASE_URL + '/api/messages.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.ok) {
            form.reset();
            openModal('modal-success');
        } else {
            showToast(data.error || 'Error al enviar el mensaje');
        }
    } catch (err) {
        showToast('Error de conexión con el servidor');
    }
}

/* ---------- Confirmaciones admin ---------- */
function confirmDelete(msg = '¿Eliminar este registro?') {
    return confirm(msg);
}
