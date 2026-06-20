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

    // ---------- Modal de Cargar Documento ----------
    const btnCargarDoc = document.getElementById('btn-cargar-documento');
    const modalDoc = document.getElementById('modal-cargar-documento');
    const btnCerrarModalDoc = document.getElementById('cerrar-modal-doc');
    const docDropzone = document.getElementById('doc-dropzone');
    const docArchivoInput = document.getElementById('doc-archivo');
    const formCargarDoc = document.getElementById('form-cargar-documento');

    function abrirModalDoc() {
        if (modalDoc) {
            modalDoc.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function cerrarModalDoc() {
        if (modalDoc) {
            modalDoc.classList.remove('active');
            document.body.style.overflow = '';
            // Reset form
            if (formCargarDoc) {
                formCargarDoc.reset();
            }
            if (docDropzone) {
                docDropzone.classList.remove('active');
            }
        }
    }

    if (btnCargarDoc) {
        btnCargarDoc.addEventListener('click', abrirModalDoc);
    }

    // Abrir modal automáticamente si viene del parámetro URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('openDocModal') && urlParams.get('openDocModal') === 'true') {
        abrirModalDoc();
        // Limpiar el parámetro de la URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    if (btnCerrarModalDoc) {
        btnCerrarModalDoc.addEventListener('click', cerrarModalDoc);
    }

    const btnCancelarModalDoc = document.getElementById('cancelar-modal-doc');
    if (btnCancelarModalDoc) {
        btnCancelarModalDoc.addEventListener('click', cerrarModalDoc);
    }

    // Cerrar modal al hacer click fuera
    if (modalDoc) {
        modalDoc.addEventListener('click', function(e) {
            if (e.target === modalDoc) {
                cerrarModalDoc();
            }
        });
    }

    // Cerrar con Escape (para modal documento)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalDoc && modalDoc.classList.contains('active')) {
            cerrarModalDoc();
        }
    });

    // ---------- Drag & Drop para documento ----------
    if (docDropzone && docArchivoInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            docDropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            docDropzone.addEventListener(eventName, () => {
                docDropzone.classList.add('active');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            docDropzone.addEventListener(eventName, () => {
                docDropzone.classList.remove('active');
            });
        });

        docDropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            docArchivoInput.files = files;
            handleFileSelect({ target: { files: files } });
        });

        docDropzone.addEventListener('click', () => {
            docArchivoInput.click();
        });

        docArchivoInput.addEventListener('change', handleFileSelect);

        function handleFileSelect(e) {
            const files = e.target.files;
            if (files.length > 0) {
                const file = files[0];
                const validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                const maxSize = 5 * 1024 * 1024; // 5MB

                // Validar tipo
                if (!validTypes.includes(file.type)) {
                    alert('Solo se permiten archivos JPG, PNG o PDF');
                    docArchivoInput.value = '';
                    return;
                }

                // Validar tamaño
                if (file.size > maxSize) {
                    alert('El archivo no puede ser mayor a 5MB');
                    docArchivoInput.value = '';
                    return;
                }

                // Mostrar nombre del archivo
                const fileName = file.name;
                const fileInfo = docDropzone.querySelector('p:first-of-type');
                if (fileInfo) {
                    fileInfo.textContent = `Archivo: ${fileName}`;
                }
            }
        }
    }

    // ---------- Validación y envío del formulario de documento ----------
    if (formCargarDoc) {
        formCargarDoc.addEventListener('submit', function(e) {
            let valido = true;

            // Validar tipo
            const tipoSelect = document.getElementById('doc-tipo');
            if (!tipoSelect || !tipoSelect.value) {
                valido = false;
                if (tipoSelect) tipoSelect.classList.add('error-input');
            } else {
                if (tipoSelect) tipoSelect.classList.remove('error-input');
            }

            // Validar archivo
            if (!docArchivoInput || !docArchivoInput.files.length) {
                valido = false;
                if (docDropzone) {
                    const error = document.createElement('p');
                    error.className = 'error';
                    error.textContent = 'Debe seleccionar un archivo';
                    if (!docDropzone.querySelector('.error')) {
                        docDropzone.appendChild(error);
                    }
                }
            } else {
                const errorMsg = docDropzone?.querySelector('.error');
                if (errorMsg) errorMsg.remove();
            }

            if (!valido) {
                e.preventDefault();
                return;
            }

            // Mostrar loading state
            const btnSubmit = formCargarDoc.querySelector('button[type="submit"]');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = 'Cargando...';
            }
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
