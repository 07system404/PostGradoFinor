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

        // Respeta el mínimo declarado en cada input (Diplomado = 1, otros = 0)
        const minVal = parseInt(input?.getAttribute('min')) || 0;

        if (btnMinus && input) {
            btnMinus.addEventListener('click', function() {
                let val = parseInt(input.value) || 0;
                if (val > minVal) {
                    input.value = val - 1;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }

        if (btnPlus && input) {
            btnPlus.addEventListener('click', function() {
                let val = parseInt(input.value) || 0;
                input.value = val + 1;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }

        if (input) {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    });

    // ---------- Cálculo dinámico del Costo por Módulo ----------
    // Total de módulos acumulativo según el tipo de programa:
    //   Diplomado    → Diplomado
    //   Especialidad → Diplomado + Especialidad
    //   Maestría     → Diplomado + Especialidad + Maestría
    // Costo por Módulo = Costo Total de Estudios ÷ Total de módulos.
    const tipoSelect = document.getElementById('tipo');
    const costoTotalInput = document.getElementById('costo_total_estudio');
    const modDiplomado = document.getElementById('nro_modulos_diplomado');
    const modEspecialidad = document.getElementById('nro_modulos_especialidad');
    const modMaestria = document.getElementById('nro_modulos_maestria');
    const totalModulosOut = document.getElementById('total_modulos_display');
    const costoModuloOut = document.getElementById('costo_modulo_display');

    function valor(el) {
        return el ? (parseFloat(el.value) || 0) : 0;
    }

    function recalcularCostoModulo() {
        if (!costoModuloOut) return;

        const tipo = tipoSelect ? tipoSelect.value : 'Diplomado';
        const dip = valor(modDiplomado);
        const esp = valor(modEspecialidad);
        const mae = valor(modMaestria);

        let totalModulos = dip;
        if (tipo === 'Especialidad') {
            totalModulos = dip + esp;
        } else if (tipo === 'Maestría') {
            totalModulos = dip + esp + mae;
        }

        const costoTotal = valor(costoTotalInput);
        const costoModulo = totalModulos > 0 ? costoTotal / totalModulos : 0;

        if (totalModulosOut) totalModulosOut.textContent = totalModulos;
        costoModuloOut.value = costoModulo.toFixed(2);
    }

    [tipoSelect, costoTotalInput, modDiplomado, modEspecialidad, modMaestria].forEach(el => {
        if (!el) return;
        el.addEventListener('input', recalcularCostoModulo);
        el.addEventListener('change', recalcularCostoModulo);
    });

    // Cálculo inicial al cargar la página (refleja los valores guardados en edición)
    recalcularCostoModulo();

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
