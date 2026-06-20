/* ============================================
   ESTUDIANTES.JS - Gestión de Alumnos
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Búsqueda en tiempo real ----------
    const searchInput = document.getElementById('buscar-alumno');
    const searchForm = document.getElementById('form-buscar-alumnos');
    let searchTimeout;

    if (searchInput && searchForm) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2 || this.value.length === 0) {
                    searchForm.submit();
                }
            }, 500);
        });
    }

    // ---------- Filtro por curso ----------
    const filterSelect = document.getElementById('filtro-curso');

    if (filterSelect && searchForm) {
        filterSelect.addEventListener('change', function() {
            searchForm.submit();
        });
    }

    // ---------- Modal de Confirmación ----------
    const modalConfirm = document.getElementById('modal-confirmacion');
    const confirmHeader = document.getElementById('modal-confirm-header');
    const confirmIcon = document.getElementById('modal-confirm-icon');
    const confirmTitulo = document.getElementById('modal-confirm-titulo');
    const confirmText = document.getElementById('modal-confirm-text');
    const confirmSubtext = document.getElementById('modal-confirm-subtext');
    const btnConfirmar = document.getElementById('btn-confirmar-accion');
    const btnCancelarConfirm = document.getElementById('btn-cancelar-confirm');
    const btnCerrarConfirm = document.getElementById('cerrar-modal-confirm');
    let confirmForm = null;

    function abrirModalConfirm(tipo) {
        if (!modalConfirm) return;
        modalConfirm.classList.remove('modal-confirm-danger', 'modal-confirm-success');
        modalConfirm.classList.add('active');
        document.body.style.overflow = 'hidden';

        if (tipo === 'baja') {
            modalConfirm.classList.add('modal-confirm-danger');
            confirmIcon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>';
            confirmTitulo.textContent = 'Dar de Baja';
            btnConfirmar.textContent = 'Sí, dar de baja';
        } else {
            modalConfirm.classList.add('modal-confirm-success');
            confirmIcon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>';
            confirmTitulo.textContent = 'Reactivar Alumno';
            btnConfirmar.textContent = 'Sí, reactivar';
        }
    }

    function cerrarModalConfirm() {
        if (!modalConfirm) return;
        modalConfirm.classList.remove('active');
        document.body.style.overflow = '';
        confirmForm = null;
    }

    if (btnCerrarConfirm) btnCerrarConfirm.addEventListener('click', cerrarModalConfirm);
    if (btnCancelarConfirm) btnCancelarConfirm.addEventListener('click', cerrarModalConfirm);
    if (modalConfirm) {
        modalConfirm.addEventListener('click', function(e) {
            if (e.target === modalConfirm) cerrarModalConfirm();
        });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalConfirm && modalConfirm.classList.contains('active')) {
            cerrarModalConfirm();
        }
    });

    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', function() {
            if (confirmForm) confirmForm.submit();
            cerrarModalConfirm();
        });
    }

    // ---------- Dar de baja ----------
    const btnsBaja = document.querySelectorAll('.btn-baja');
    btnsBaja.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const nombre = this.dataset.nombre || 'este alumno';
            confirmForm = this.closest('form');
            confirmText.textContent = '¿Está seguro que desea dar de baja a "' + nombre + '"?';
            confirmSubtext.textContent = 'El alumno quedará como inactivo (opaco) en la lista. Sus inscripciones, documentos y deudas se conservarán como registro histórico.';
            abrirModalConfirm('baja');
        });
    });

    // ---------- Reactivar ----------
    const btnsReactivar = document.querySelectorAll('.btn-reactivar');
    btnsReactivar.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const nombre = this.dataset.nombre || 'este alumno';
            confirmForm = this.closest('form');
            confirmText.textContent = '¿Desea reactivar al alumno "' + nombre + '"?';
            confirmSubtext.textContent = 'El alumno volverá a aparecer como activo en la lista con todos sus datos e inscripciones.';
            abrirModalConfirm('reactivar');
        });
    });

    // ---------- Tooltips en acciones ----------
    const acciones = document.querySelectorAll('.btn-accion');
    acciones.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            const tooltip = this.getAttribute('title');
            if (tooltip) {
                this.dataset.tooltip = tooltip;
            }
        });
    });

    // ---------- Animación de filas ----------
    const filas = document.querySelectorAll('table.data-table tbody tr');
    filas.forEach((fila, index) => {
        fila.style.opacity = '0';
        fila.style.transform = 'translateY(10px)';
        setTimeout(() => {
            fila.style.transition = 'all 0.3s ease';
            fila.style.opacity = fila.classList.contains('fila-inactiva') ? '0.5' : '1';
            fila.style.transform = 'translateY(0)';
        }, index * 80);
    });

    // ---------- Exportar reporte con loading ----------
    const btnExportar = document.getElementById('btn-exportar');
    if (btnExportar) {
        btnExportar.addEventListener('click', function(e) {
            const originalText = this.innerHTML;
            this.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20">
                        <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
                    </circle>
                </svg>
                <span>Exportando...</span>
            `;
            this.style.pointerEvents = 'none';

            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.pointerEvents = 'auto';
            }, 2000);
        });
    }

    // ---------- Auto-hide alerts ----------
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

});
