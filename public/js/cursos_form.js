/* ============================================
   CURSOS_FORM.JS - Formulario de Programa
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Contadores de módulos ----------
    const counters = document.querySelectorAll('.modulo-counter');
    counters.forEach(counter => {
        const btnMinus = counter.querySelector('.btn-minus');
        const btnPlus = counter.querySelector('.btn-plus');
        const input = counter.querySelector('input');

        if (btnMinus && input) {
            btnMinus.addEventListener('click', function() {
                let val = parseInt(input.value) || 0;
                if (val > 0) {
                    input.value = val - 1;
                }
            });
        }

        if (btnPlus && input) {
            btnPlus.addEventListener('click', function() {
                let val = parseInt(input.value) || 0;
                input.value = val + 1;
            });
        }

        if (input) {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    });

    // ---------- Formatear inputs de moneda ----------
    const monedaInputs = document.querySelectorAll('.input-moneda input');
    monedaInputs.forEach(input => {
        input.addEventListener('blur', function() {
            let val = parseFloat(this.value);
            if (!isNaN(val)) {
                this.value = val.toFixed(2);
            }
        });
    });

    // ---------- Validación de formulario ----------
    const formPrograma = document.getElementById('form-nuevo-programa');
    if (formPrograma) {
        formPrograma.addEventListener('submit', function(e) {
            let valido = true;
            const camposRequeridos = formPrograma.querySelectorAll('[required]');

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

            if (!valido) {
                e.preventDefault();
                const primerError = formPrograma.querySelector('.error-input');
                if (primerError) primerError.focus();
            } else {
                const btnSubmit = formPrograma.querySelector('.btn-guardar-programa');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = `
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20">
                                <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
                            </circle>
                        </svg>
                        <span>Guardando...</span>
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
