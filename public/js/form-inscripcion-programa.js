/* ============================================
   FORM-INSCRIPCION-PROGRAMA.JS
   Modal "Nueva Inscripción"
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    var btnAbrir = document.getElementById('btn-nueva-inscripcion');
    var modal = document.getElementById('modal-nueva-inscripcion');
    var btnCerrar = document.getElementById('fi-cerrar-modal');
    var btnCancelar = document.getElementById('fi-cancelar-modal');

    function abrirModal() {
        if (!modal) return;
        modal.style.display = 'flex';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModal() {
        if (!modal) return;
        modal.style.display = 'none';
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (btnAbrir && modal) {
        btnAbrir.addEventListener('click', function() {
            abrirModal();
        });
    }

    if (btnCerrar) btnCerrar.addEventListener('click', cerrarModal);
    if (btnCancelar) btnCancelar.addEventListener('click', cerrarModal);

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) cerrarModal();
        });
    }

    // --- Cerrar con Escape ---
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
            cerrarModal();
        }
    });

});
