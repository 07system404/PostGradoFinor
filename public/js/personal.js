/* ============================================
   PERSONAL.JS - Gestion de Personal y Roles
   (index + create pages)
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ===========================
    // BUSQUEDA EN TIEMPO REAL — index
    // ===========================
    var searchInput = document.getElementById('buscar-usuario');
    var searchForm = document.getElementById('form-buscar-usuario');
    var searchTimeout;

    if (searchInput && searchForm) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            var val = this.value;
            searchTimeout = setTimeout(function() {
                if (val.length >= 2 || val.length === 0) {
                    searchForm.submit();
                }
            }, 500);
        });
    }

    // ===========================
    // TOGGLE PASSWORD VISIBILITY
    // ===========================
    var toggleBtns = document.querySelectorAll('.btn-toggle-password');
    toggleBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.parentElement.querySelector('input');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML =
                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                        '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>' +
                        '<circle cx="12" cy="12" r="3"/>' +
                    '</svg>';
            } else {
                input.type = 'password';
                this.innerHTML =
                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                        '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>' +
                        '<line x1="1" y1="1" x2="23" y2="23"/>' +
                    '</svg>';
            }
        });
    });

    // ===========================
    // MODAL EDITAR USUARIO — index
    // ===========================
    var btnsEditar = document.querySelectorAll('.btn-editar-usuario');
    var modalOverlay = document.getElementById('modal-editar-usuario');
    var btnCerrarModal = document.getElementById('cerrar-modal-editar');
    var btnCancelarModal = document.getElementById('cancelar-modal-editar');

    function abrirModal() {
        if (!modalOverlay) return;
        modalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModal() {
        if (!modalOverlay) return;
        modalOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    btnsEditar.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var userId = this.dataset.userId;
            var userName = this.dataset.userName;
            var userEmail = this.dataset.userEmail;
            var userRol = this.dataset.userRol;

            var form = document.getElementById('form-editar-usuario');
            if (form) {
                form.action = form.action.replace(/\/\d+$/, '/' + userId);
                document.getElementById('edit-name').value = userName;
                document.getElementById('edit-email').value = userEmail;
                document.getElementById('edit-rol').value = userRol;
                var editPass = document.getElementById('edit-password');
                if (editPass) editPass.value = '';
            }
            abrirModal();
        });
    });

    if (btnCerrarModal) btnCerrarModal.addEventListener('click', cerrarModal);
    if (btnCancelarModal) btnCancelarModal.addEventListener('click', cerrarModal);

    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) cerrarModal();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay && modalOverlay.classList.contains('active')) {
            cerrarModal();
        }
    });

    // ===========================
    // VALIDACION DE FORMULARIO — create
    // ===========================
    var formRegistrar = document.getElementById('form-registrar-usuario');
    if (formRegistrar) {
        formRegistrar.addEventListener('submit', function(e) {
            var valido = true;
            var camposRequeridos = formRegistrar.querySelectorAll('[required]');

            camposRequeridos.forEach(function(campo) {
                campo.classList.remove('error-input');
                var errorExistente = campo.parentElement.querySelector('.error');
                if (errorExistente) errorExistente.remove();

                if (!campo.value.trim()) {
                    valido = false;
                    campo.classList.add('error-input');
                    var errorMsg = document.createElement('span');
                    errorMsg.className = 'error';
                    errorMsg.textContent = 'Este campo es obligatorio';
                    errorMsg.style.cssText = 'color: var(--danger); font-size: 12px; margin-top: 4px;';
                    campo.parentElement.appendChild(errorMsg);
                }
            });

            var email = document.getElementById('email');
            if (email && email.value) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email.value)) {
                    valido = false;
                    email.classList.add('error-input');
                }
            }

            if (!valido) {
                e.preventDefault();
                var primerError = formRegistrar.querySelector('.error-input');
                if (primerError) primerError.focus();
            } else {
                var btn = formRegistrar.querySelector('.btn-registrar-usuario');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML =
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                            '<circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20">' +
                                '<animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>' +
                            '</circle>' +
                        '</svg>' +
                        '<span>Registrando...</span>';
                }
            }
        });
    }

    // ===========================
    // CONFIRMAR ELIMINACION — index
    // ===========================
    var btnsEliminar = document.querySelectorAll('.btn-eliminar-usuario');
    btnsEliminar.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Esta seguro de eliminar este usuario?')) {
                e.preventDefault();
            }
        });
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
    // ANIMACION DE ENTRADA — index
    // ===========================
    document.querySelectorAll('.usuarios-table tbody tr').forEach(function(fila, index) {
        fila.style.opacity = '0';
        fila.style.transform = 'translateX(-10px)';
        setTimeout(function() {
            fila.style.transition = 'all 0.3s ease';
            fila.style.opacity = '1';
            fila.style.transform = 'translateX(0)';
        }, index * 60);
    });

});
