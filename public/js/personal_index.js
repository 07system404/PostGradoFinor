/* ============================================
   PERSONAL_INDEX.JS - Gestión de Personal (Index)
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Búsqueda en tiempo real ----------
    const searchInput = document.getElementById('buscar-usuario');
    const searchForm = document.getElementById('form-buscar-usuario');
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

    // ---------- Toggle password visibility ----------
    const toggleBtns = document.querySelectorAll('.btn-toggle-password');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = `
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                `;
            } else {
                input.type = 'password';
                this.innerHTML = `
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                `;
            }
        });
    });

    // ---------- Modal editar usuario ----------
    const btnsEditar = document.querySelectorAll('.btn-editar-usuario');
    const modalOverlay = document.getElementById('modal-editar-usuario');
    const btnCerrarModal = document.getElementById('cerrar-modal-editar');
    const btnCancelarModal = document.getElementById('cancelar-modal-editar');

    function abrirModal() {
        if (modalOverlay) {
            modalOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function cerrarModal() {
        if (modalOverlay) {
            modalOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    btnsEditar.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            const userEmail = this.dataset.userEmail;
            const userRol = this.dataset.userRol;

            const form = document.getElementById('form-editar-usuario');
            if (form) {
                form.action = form.action.replace(/\/\d+$/, '/' + userId);
                document.getElementById('edit-name').value = userName;
                document.getElementById('edit-email').value = userEmail;
                document.getElementById('edit-rol').value = userRol;
                // Limpiar contraseña al abrir modal
                const editPass = document.getElementById('edit-password');
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

    // ---------- Confirmar eliminación ----------
    const btnsEliminar = document.querySelectorAll('.btn-eliminar-usuario');
    btnsEliminar.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('¿Está seguro de eliminar este usuario?')) {
                e.preventDefault();
            }
        });
    });

    // ---------- Auto-hide alerts ----------
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

    // ---------- Animación de entrada ----------
    const filas = document.querySelectorAll('.usuarios-table tbody tr');
    filas.forEach((fila, index) => {
        fila.style.opacity = '0';
        fila.style.transform = 'translateX(-10px)';
        setTimeout(() => {
            fila.style.transition = 'all 0.3s ease';
            fila.style.opacity = '1';
            fila.style.transform = 'translateX(0)';
        }, index * 60);
    });

});
