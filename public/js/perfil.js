/* ============================================
   PERFIL.JS - Detalle del Alumno
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Modal de Nueva Inscripción ----------
    const btnNuevaInscripcion = document.getElementById('btn-nueva-inscripcion');
    const modalOverlay = document.getElementById('modal-nueva-inscripcion');
    const btnCerrarModal = document.getElementById('cerrar-modal');
    const btnCancelarModal = document.getElementById('cancelar-modal');

    function abrirModal() {
        if (modalOverlay) {
            modalOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Set fecha por defecto
            const fechaInput = document.getElementById('modal-fecha-inscripcion');
            if (fechaInput && !fechaInput.value) {
                fechaInput.value = new Date().toISOString().split('T')[0];
            }
        }
    }

    function cerrarModal() {
        if (modalOverlay) {
            modalOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (btnNuevaInscripcion) {
        btnNuevaInscripcion.addEventListener('click', abrirModal);
    }

    if (btnCerrarModal) {
        btnCerrarModal.addEventListener('click', cerrarModal);
    }

    if (btnCancelarModal) {
        btnCancelarModal.addEventListener('click', cerrarModal);
    }

    // Cerrar modal al hacer click fuera
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                cerrarModal();
            }
        });
    }

    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay && modalOverlay.classList.contains('active')) {
            cerrarModal();
        }
    });

    // ---------- Validación del modal ----------
    const modalForm = document.getElementById('form-nueva-inscripcion');
    if (modalForm) {
        modalForm.addEventListener('submit', function(e) {
            let valido = true;
            const camposRequeridos = modalForm.querySelectorAll('[required]');

            camposRequeridos.forEach(campo => {
                campo.classList.remove('error-input');
                const errorExistente = campo.parentElement.querySelector('.error');
                if (errorExistente) errorExistente.remove();

                if (!campo.value.trim()) {
                    valido = false;
                    campo.classList.add('error-input');

                    const errorMsg = document.createElement('span');
                    errorMsg.className = 'error';
                    errorMsg.textContent = 'Este campo es obligatorio';
                    campo.parentElement.appendChild(errorMsg);
                }
            });

            if (!valido) {
                e.preventDefault();
                const primerError = modalForm.querySelector('.error-input');
                if (primerError) primerError.focus();
            } else {
                const btnSubmit = modalForm.querySelector('button[type="submit"]');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = 'Guardando...';
                }
            }
        });
    }

    // ---------- Actualizar datos personales ----------
    const formDatos = document.getElementById('form-datos-personales');
    if (formDatos) {
        formDatos.addEventListener('submit', function(e) {
            const cedula = document.getElementById('cedula');
            if (cedula && cedula.value) {
                // Quitar puntos
                cedula.value = cedula.value.replace(/\./g, '');

                if (!/^\d+$/.test(cedula.value)) {
                    e.preventDefault();
                    alert('La cédula debe contener solo números, sin puntos');
                    cedula.focus();
                    return;
                }
            }

            // Mostrar loading
            const btn = formDatos.querySelector('.btn-actualizar');
            if (btn) {
                const originalHTML = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20">
                            <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
                        </circle>
                    </svg>
                    <span>Guardando...</span>
                `;
            }
        });
    }

    // ---------- Formatear cédula en tiempo real ----------
    const cedulaInput = document.getElementById('cedula');
    if (cedulaInput) {
        cedulaInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    // ---------- Formatear teléfono ----------
    const telefonoInput = document.getElementById('celular');
    if (telefonoInput) {
        telefonoInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\s]/g, '');
        });
    }

    // ---------- Acciones de documentos ----------
    const btnsVerDoc = document.querySelectorAll('.btn-ver-doc');
    btnsVerDoc.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const docUrl = this.dataset.url;
            if (docUrl) {
                window.open(docUrl, '_blank');
            } else {
                alert('Documento no disponible');
            }
        });
    });

    const btnsDescargarDoc = document.querySelectorAll('.btn-descargar-doc');
    btnsDescargarDoc.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const docUrl = this.dataset.url;
            if (docUrl) {
                const a = document.createElement('a');
                a.href = docUrl;
                a.download = '';
                a.click();
            } else {
                alert('Documento no disponible para descargar');
            }
        });
    });

    // ---------- Cargar documento ----------
    const btnCargarDoc = document.getElementById('btn-cargar-documento');
    if (btnCargarDoc) {
        btnCargarDoc.addEventListener('click', function(e) {
            e.preventDefault();
            // Aquí se implementaría la subida de archivos
            alert('Funcionalidad de carga de documentos - Implementar con dropzone o input file');
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

    // ---------- Animación de entrada ----------
    const cards = document.querySelectorAll('.perfil-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

});
