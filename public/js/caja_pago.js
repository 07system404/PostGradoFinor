/* ============================================
   CAJA_PAGO.JS - Registro de Pago
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Dropzone para comprobante ----------
    const dropzone = document.getElementById('dropzone-comprobante');
    const fileInput = document.getElementById('comprobante');

    if (dropzone && fileInput) {
        // Click para abrir selector
        dropzone.addEventListener('click', function() {
            fileInput.click();
        });

        // Drag & drop
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                mostrarArchivoSeleccionado(files[0]);
            }
        });

        // Cuando se selecciona archivo
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                mostrarArchivoSeleccionado(this.files[0]);
            }
        });
    }

    function mostrarArchivoSeleccionado(file) {
        const dropzone = document.getElementById('dropzone-comprobante');
        if (dropzone) {
            dropzone.innerHTML = `
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2E7D32" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <div class="pago-dropzone-text" style="color: var(--success); font-weight: 600;">
                    ${file.name}
                </div>
                <div class="pago-dropzone-sub">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
            `;
            dropzone.style.borderColor = 'var(--success)';
            dropzone.style.background = 'var(--success-light)';
        }
    }

    // ---------- Validación de formulario de pago ----------
    const formPago = document.getElementById('form-registro-pago');
    if (formPago) {
        formPago.addEventListener('submit', function(e) {
            let valido = true;
            const camposRequeridos = formPago.querySelectorAll('[required]');

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
                    errorMsg.style.cssText = 'color: var(--danger); font-size: 12px; margin-top: 4px;';
                    campo.parentElement.appendChild(errorMsg);
                }
            });

            // Validar monto
            const montoInput = document.getElementById('monto');
            if (montoInput && montoInput.value) {
                const monto = parseFloat(montoInput.value);
                if (monto <= 0) {
                    valido = false;
                    montoInput.classList.add('error-input');
                }
            }

            if (!valido) {
                e.preventDefault();
                const primerError = formPago.querySelector('.error-input');
                if (primerError) primerError.focus();
            } else {
                const btnSubmit = formPago.querySelector('.btn-confirmar-pago');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = `
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20">
                                <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
                            </circle>
                        </svg>
                        <span>Procesando...</span>
                    `;
                }
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

});
