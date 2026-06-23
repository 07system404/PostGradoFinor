/* ============================================
   ESTUDIANTES.JS - Gestión de Alumnos
   (index + show pages)
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ===========================
    // BÚSQUEDA EN TIEMPO REAL
    // ===========================
    var searchInput = document.getElementById('buscar-alumno');
    var searchForm = document.getElementById('form-buscar-alumnos');
    var searchTimeout;
    if (searchInput && searchForm) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            var val = this.value;
            searchTimeout = setTimeout(function() {
                if (val.length >= 2 || val.length === 0) searchForm.submit();
            }, 500);
        });
    }

    // ===========================
    // FILTRO POR CURSO
    // ===========================
    var filterSelect = document.getElementById('filtro-curso');
    if (filterSelect && searchForm) {
        filterSelect.addEventListener('change', function() { searchForm.submit(); });
    }

    // ===========================
    // MODAL CONFIRMACIÓN (BAJA / REACTIVAR)
    // ===========================
    var modalConfirm = document.getElementById('modal-confirmacion');
    var confirmIcon = document.getElementById('modal-confirm-icon');
    var confirmTitulo = document.getElementById('modal-confirm-titulo');
    var confirmText = document.getElementById('modal-confirm-text');
    var confirmSubtext = document.getElementById('modal-confirm-subtext');
    var btnConfirmar = document.getElementById('btn-confirmar-accion');
    var btnCancelarConfirm = document.getElementById('btn-cancelar-confirm');
    var btnCerrarConfirm = document.getElementById('cerrar-modal-confirm');
    var confirmForm = null;

    function abrirModalConfirm(tipo, subtexto) {
        if (!modalConfirm) return;
        modalConfirm.classList.remove('modal-confirm-danger', 'modal-confirm-success');
        modalConfirm.classList.add('active');
        document.body.style.overflow = 'hidden';
        if (tipo === 'baja') {
            modalConfirm.classList.add('modal-confirm-danger');
            confirmIcon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>';
            confirmTitulo.textContent = 'Dar de Baja';
            btnConfirmar.textContent = 'Si, dar de baja';
            confirmSubtext.textContent = subtexto || 'Se condonaran las cuotas futuras en todos sus cursos.';
        } else if (tipo === 'reactivar') {
            modalConfirm.classList.add('modal-confirm-success');
            confirmIcon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>';
            confirmTitulo.textContent = 'Reactivar Inscripción';
            btnConfirmar.textContent = 'Si, reactivar';
            confirmSubtext.textContent = subtexto || 'Las cuotas condonadas volveran a Pendiente.';
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
    if (modalConfirm) modalConfirm.addEventListener('click', function(e) { if (e.target === modalConfirm) cerrarModalConfirm(); });
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', function() {
            if (confirmForm) confirmForm.submit();
            cerrarModalConfirm();
        });
    }

    // --- Botones de baja (data-baja-url) ---
    document.querySelectorAll('[data-baja-url]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = this.getAttribute('data-nombre') || 'este alumno';
            var url = this.getAttribute('data-baja-url');
            var f = document.createElement('form');
            f.method = 'POST'; f.action = url; f.style.display = 'none';
            var t = document.createElement('input');
            t.type = 'hidden'; t.name = '_token'; t.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            f.appendChild(t);
            document.body.appendChild(f);
            confirmForm = f;
            confirmText.textContent = 'Esta seguro que desea dar de baja a "' + nombre + '" de TODOS sus cursos?';
            abrirModalConfirm('baja', 'Se condonaran las cuotas futuras y el alumno quedara como Retirado.');
        });
    });

    // --- Botones de reactivar (data-reactivar-url) ---
    document.querySelectorAll('[data-reactivar-url]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = this.getAttribute('data-nombre') || 'este alumno';
            var url = this.getAttribute('data-reactivar-url');
            var f = document.createElement('form');
            f.method = 'POST'; f.action = url; f.style.display = 'none';
            var t = document.createElement('input');
            t.type = 'hidden'; t.name = '_token'; t.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            f.appendChild(t);
            document.body.appendChild(f);
            confirmForm = f;
            confirmText.textContent = 'Desea reactivar la inscripcion de "' + nombre + '"?';
            abrirModalConfirm('reactivar', 'Las cuotas condonadas volveran a Pendiente con fechas recalculadas desde hoy.');
        });
    });

    // --- Botones de baja legacy ---
    document.querySelectorAll('.form-baja-estudiante .btn-baja').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = btn.getAttribute('data-nombre') || 'este alumno';
            confirmForm = btn.closest('form');
            confirmText.textContent = 'Esta seguro que desea dar de baja a "' + nombre + '"?';
            abrirModalConfirm('baja', 'El alumno quedara como inactivo en la lista.');
        });
    });

    // --- Botones de reactivar legacy ---
    document.querySelectorAll('.form-baja-inline .btn-reactivar').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = btn.getAttribute('data-nombre') || 'este alumno';
            confirmForm = btn.closest('form');
            confirmText.textContent = 'Desea reactivar al alumno "' + nombre + '"?';
            abrirModalConfirm('reactivar');
        });
    });

    // ===========================
    // ANIMACIÓN DE FILAS (index)
    // ===========================
    document.querySelectorAll('table.data-table tbody tr').forEach(function(fila, i) {
        fila.style.opacity = '0';
        fila.style.transform = 'translateY(10px)';
        setTimeout(function() {
            fila.style.transition = 'all 0.3s ease';
            fila.style.opacity = fila.classList.contains('fila-inactiva') ? '0.5' : '1';
            fila.style.transform = 'translateY(0)';
        }, i * 80);
    });

    // ===========================
    // ANIMACIÓN ENTRADA (show - perfil cards)
    // ===========================
    document.querySelectorAll('.perfil-card').forEach(function(card, i) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(function() {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, i * 100);
    });

    // ===========================
    // AUTO-HIDE ALERTS
    // ===========================
    document.querySelectorAll('.alert').forEach(function(a) {
        setTimeout(function() {
            a.style.transition = 'opacity 0.5s';
            a.style.opacity = '0';
            setTimeout(function() { a.remove(); }, 500);
        }, 4000);
    });

    // ===========================
    // HANDLER ÚNICO DE ESCAPE
    // ===========================
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        if (modalConfirm && modalConfirm.classList.contains('active')) {
            cerrarModalConfirm();
        }
    });

    // ===========================
    // TOGGLE INSCRIPCIÓN OPCIONAL — create
    // ===========================
    var toggle = document.getElementById('toggle-inscripcion');
    var fieldsContainer = document.getElementById('inscripcion-fields');
    var btnText = document.getElementById('btn-text');
    var cardInscripcion = document.getElementById('card-inscripcion');

    function toggleInscripcion(checked) {
        if (!fieldsContainer || !btnText) return;
        if (checked) {
            fieldsContainer.classList.remove('is-disabled');
            if (cardInscripcion) cardInscripcion.classList.add('toggle-on');
            document.querySelectorAll('#inscripcion-fields select, #inscripcion-fields input').forEach(function(el) {
                el.removeAttribute('disabled');
                if (el.hasAttribute('data-required')) {
                    el.setAttribute('required', '');
                }
            });
            btnText.textContent = 'Guardar e Inscribir';
        } else {
            fieldsContainer.classList.add('is-disabled');
            if (cardInscripcion) cardInscripcion.classList.remove('toggle-on');
            document.querySelectorAll('#inscripcion-fields select, #inscripcion-fields input').forEach(function(el) {
                el.setAttribute('disabled', 'disabled');
                if (el.hasAttribute('required')) {
                    el.setAttribute('data-required', 'true');
                    el.removeAttribute('required');
                }
            });
            btnText.textContent = 'Guardar Estudiante';
        }
    }

    if (toggle && fieldsContainer) {
        toggleInscripcion(toggle.checked);
        toggle.addEventListener('change', function() {
            toggleInscripcion(this.checked);
        });
    }

    // --- Botón submit fuera del form ---
    var formInscripcion = document.getElementById('form-inscripcion');
    var submitBtn = document.getElementById('btn-submit');
    if (submitBtn && formInscripcion) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            formInscripcion.submit();
        });
    }

    // --- Animación de tarjetas (create) ---
    document.querySelectorAll('.form-card').forEach(function(card, index) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(15px)';
        setTimeout(function() {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 120);
    });

});
