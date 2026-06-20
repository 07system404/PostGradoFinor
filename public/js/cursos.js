/* ============================================
   PROGRAMAS.JS - Programas Académicos
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Búsqueda en tiempo real (AJAX) ----------
    const searchInput = document.getElementById('buscar-programa');
    const filterEstado = document.getElementById('filtro-estado');
    const gridContainer = document.getElementById('programas-grid-container');
    let searchTimeout;
    let abortController = null;

    function realizarBusqueda() {
        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        const params = new URLSearchParams();
        if (searchInput && searchInput.value.length > 0) {
            params.set('buscar', searchInput.value);
        }
        if (filterEstado && filterEstado.value) {
            params.set('estado', filterEstado.value);
        }

        const searchUrl = gridContainer ? gridContainer.dataset.searchUrl : '/programas/buscar/ajax';
        fetch(searchUrl + '?' + params.toString(), {
            signal: abortController.signal,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.html && gridContainer) {
                const temp = document.createElement('div');
                temp.innerHTML = data.html;
                const newContainer = temp.querySelector('#programas-grid-container');
                if (newContainer) {
                    gridContainer.innerHTML = newContainer.innerHTML;
                }
            }
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Error en la búsqueda:', err);
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(realizarBusqueda, 350);
        });
    }

    if (filterEstado) {
        filterEstado.addEventListener('change', function() {
            realizarBusqueda();
        });
    }

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

    // ---------- Confirmar eliminación ----------
    const btnsEliminar = document.querySelectorAll('.btn-eliminar-prog');
    btnsEliminar.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('¿Está seguro de eliminar este programa? Esta acción no se puede deshacer.')) {
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

    // ---------- Animación de stats cards ----------
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(15px)';
        setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // ---------- Animación de cards ----------
    const programCards = document.querySelectorAll('.programa-card');
    programCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 80);
    });

    // ---------- Confirmar inactivación (delegación, funciona tras AJAX) ----------
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-inactivar-prog');
        if (btn && !confirm('¿Está seguro de desactivar este programa? Los estudiantes inscritos no se verán afectados, pero el programa dejará de estar visible como activo.')) {
            e.preventDefault();
        }
    });

    // ---------- Animación de progress bars al hacer scroll ----------
    const progressFills = document.querySelectorAll('.cupo-progress-fill');
    if (progressFills.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const fill = entry.target;
                    const width = fill.style.width;
                    fill.style.width = '0%';
                    setTimeout(() => {
                        fill.style.width = width;
                    }, 100);
                    observer.unobserve(fill);
                }
            });
        }, { threshold: 0.1 });

        progressFills.forEach(fill => observer.observe(fill));
    }

});