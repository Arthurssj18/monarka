    </div>
</div>
<script>
window.BASE_URL = "<?= BASE_URL ?>";
function openModal(id){ const m=document.getElementById(id); if(m){ m.classList.remove('hidden'); m.classList.add('flex'); } }
function closeModal(id){ const m=document.getElementById(id); if(m){ m.classList.add('hidden'); m.classList.remove('flex'); } }
function confirmDelete(msg){ return confirm(msg || '¿Confirmar acción?'); }
</script>
</body>
</html>