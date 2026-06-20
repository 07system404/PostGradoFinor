/* ============================================
   PERSONAL_FORM.JS - Formulario de Personal
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Validación de formulario registrar ----------
    const formRegistrar = document.getElementById('form-registrar-usuario');
    if (formRegistrar) {
        formRegistrar.addEventListener('submit', function(e) {
            let valido = true;
            const camposRequeridos = formRegistrar.querySelectorAll('[required]');

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

            const email = document.getElementById('email');
            if (email && email.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email.value)) {
                    valido = false;
                    email.classList.add('error-input');
                }
            }

            if (!valido) {
                e.preventDefault();
                const primerError = formRegistrar.querySelector('.error-input');
                if (primerError) primerError.focus();
            } else {
                const btn = formRegistrar.querySelector('.btn-registrar-usuario');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20">
                                <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
                            </circle>
                        </svg>
                        <span>Registrando...</span>
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
