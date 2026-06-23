/* ============================================
   CURSOS.JS - Programas Académicos
   (index + create + edit + show pages)
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ===========================
    // BÚSQUEDA EN TIEMPO REAL (AJAX) — index
    // ===========================
    var searchInput = document.getElementById('buscar-programa');
    var filterEstado = document.getElementById('filtro-estado');
    var gridContainer = document.getElementById('programas-grid-container');
    var searchTimeout;
    var abortController = null;

    function realizarBusqueda() {
        if (abortController) abortController.abort();
        abortController = new AbortController();

        var params = new URLSearchParams();
        if (searchInput && searchInput.value.length > 0) {
            params.set('buscar', searchInput.value);
        }
        if (filterEstado && filterEstado.value) {
            params.set('estado', filterEstado.value);
        }

        var searchUrl = gridContainer ? gridContainer.dataset.searchUrl : '/programas/buscar/ajax';
        fetch(searchUrl + '?' + params.toString(), {
            signal: abortController.signal,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.html && gridContainer) {
                var temp = document.createElement('div');
                temp.innerHTML = data.html;
                var newContainer = temp.querySelector('#programas-grid-container');
                if (newContainer) {
                    gridContainer.innerHTML = newContainer.innerHTML;
                }
            }
        })
        .catch(function(err) {
            if (err.name !== 'AbortError') {
                console.error('Error en la busqueda:', err);
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

    // ===========================
    // CONTADORES DE MODULOS — create/edit
    // ===========================
    var counters = document.querySelectorAll('.modulo-counter');
    counters.forEach(function(counter) {
        var btnMinus = counter.querySelector('.btn-minus');
        var btnPlus = counter.querySelector('.btn-plus');
        var input = counter.querySelector('input');
        var minVal = parseInt(input ? input.getAttribute('min') : null) || 0;

        if (btnMinus && input) {
            btnMinus.addEventListener('click', function() {
                var val = parseInt(input.value) || 0;
                if (val > minVal) {
                    input.value = val - 1;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }

        if (btnPlus && input) {
            btnPlus.addEventListener('click', function() {
                var val = parseInt(input.value) || 0;
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

    // ===========================
    // CALCULO DINAMICO COSTO POR MODULO — create/edit
    // ===========================
    var tipoSelect = document.getElementById('tipo');
    var costoTotalInput = document.getElementById('costo_total_estudio');
    var modDiplomado = document.getElementById('nro_modulos_diplomado');
    var modEspecialidad = document.getElementById('nro_modulos_especialidad');
    var modMaestria = document.getElementById('nro_modulos_maestria');
    var totalModulosOut = document.getElementById('total_modulos_display');
    var costoModuloOut = document.getElementById('costo_modulo_display');

    function valor(el) {
        return el ? (parseFloat(el.value) || 0) : 0;
    }

    function recalcularCostoModulo() {
        if (!costoModuloOut) return;

        var tipo = tipoSelect ? tipoSelect.value : 'Diplomado';
        var dip = valor(modDiplomado);
        var esp = valor(modEspecialidad);
        var mae = valor(modMaestria);

        var totalModulos = dip;
        if (tipo === 'Especialidad') {
            totalModulos = dip + esp;
        } else if (tipo === 'Maestria') {
            totalModulos = dip + esp + mae;
        }

        var costoTotal = valor(costoTotalInput);
        var costoModulo = totalModulos > 0 ? costoTotal / totalModulos : 0;

        if (totalModulosOut) totalModulosOut.textContent = totalModulos;
        costoModuloOut.value = costoModulo.toFixed(2);
    }

    [tipoSelect, costoTotalInput, modDiplomado, modEspecialidad, modMaestria].forEach(function(el) {
        if (!el) return;
        el.addEventListener('input', recalcularCostoModulo);
        el.addEventListener('change', recalcularCostoModulo);
    });

    recalcularCostoModulo();

    // ===========================
    // FORMATEAR INPUTS DE MONEDA — create/edit
    // ===========================
    var monedaInputs = document.querySelectorAll('.input-moneda input');
    monedaInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            var val = parseFloat(this.value);
            if (!isNaN(val)) {
                this.value = val.toFixed(2);
            }
        });
    });

    // ===========================
    // VALIDACION DE FORMULARIO — create/edit
    // ===========================
    var formPrograma = document.getElementById('form-nuevo-programa');
    if (formPrograma) {
        formPrograma.addEventListener('submit', function(e) {
            var valido = true;
            var camposRequeridos = formPrograma.querySelectorAll('[required]');

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

            if (!valido) {
                e.preventDefault();
                var primerError = formPrograma.querySelector('.error-input');
                if (primerError) primerError.focus();
            } else {
                var btnSubmit = formPrograma.querySelector('.btn-guardar-programa');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML =
                        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                            '<circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20">' +
                                '<animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>' +
                            '</circle>' +
                        '</svg>' +
                        '<span>Guardando...</span>';
                }
            }
        });
    }

    // ===========================
    // CONFIRMAR ELIMINACION — index
    // ===========================
    var btnsEliminar = document.querySelectorAll('.btn-eliminar-prog');
    btnsEliminar.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Esta seguro de eliminar este programa? Esta accion no se puede deshacer.')) {
                e.preventDefault();
            }
        });
    });

    // ===========================
    // CONFIRMAR INACTIVAR/ACTIVAR (delegacion) — index
    // ===========================
    document.addEventListener('click', function(e) {
        var btnInactivar = e.target.closest('.btn-inactivar-prog');
        var btnActivar = e.target.closest('.btn-icon-activate');

        if (btnInactivar) {
            if (!confirm('Desactivar este programa? Los estudiantes inscritos no se veran afectados, pero el programa dejara de estar visible como activo.')) {
                e.preventDefault();
            }
        } else if (btnActivar) {
            if (!confirm('Activar este programa?')) {
                e.preventDefault();
            }
        }
    });

    // ===========================
    // ANIMACION DE STATS CARDS — index
    // ===========================
    var statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(function(card, index) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(15px)';
        setTimeout(function() {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // ===========================
    // ANIMACION DE CARDS — index
    // ===========================
    var programCards = document.querySelectorAll('.programa-card');
    programCards.forEach(function(card, index) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(function() {
            card.style.transition = 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 80);
    });

    // ===========================
    // PROGRESS BARS (IntersectionObserver) — index
    // ===========================
    var progressFills = document.querySelectorAll('.cupo-progress-fill');
    if (progressFills.length > 0) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var fill = entry.target;
                    var width = fill.style.width;
                    fill.style.width = '0%';
                    setTimeout(function() {
                        fill.style.width = width;
                    }, 100);
                    observer.unobserve(fill);
                }
            });
        }, { threshold: 0.1 });

        progressFills.forEach(function(fill) { observer.observe(fill); });
    }

    // ===========================
    // MODAL CONFIRMACION GENERICO — show
    // ===========================
    var maiModal = document.getElementById('modal-accion-inscripcion');
    var maiTitulo = document.getElementById('mai-titulo');
    var maiTexto = document.getElementById('mai-texto');
    var maiSubtexto = document.getElementById('mai-subtexto');
    var maiConfirmar = document.getElementById('mai-confirmar');
    var maiCancelar = document.getElementById('mai-cancelar');
    var maiCerrar = document.getElementById('mai-cerrar');
    var maiForm = null;

    function maiAbrir(titulo, texto, subtexto, color, btnText) {
        if (!maiModal) return;
        maiTitulo.textContent = titulo;
        maiTexto.textContent = texto;
        maiSubtexto.textContent = subtexto || '';
        maiConfirmar.style.background = color || '#dc2626';
        maiConfirmar.textContent = btnText || 'Confirmar';
        maiModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function maiCerrarFn() {
        if (!maiModal) return;
        maiModal.classList.remove('active');
        document.body.style.overflow = '';
        maiForm = null;
    }

    if (maiConfirmar) {
        maiConfirmar.addEventListener('click', function() {
            if (maiForm) maiForm.submit();
            maiCerrarFn();
        });
    }
    if (maiCancelar) maiCancelar.addEventListener('click', maiCerrarFn);
    if (maiCerrar) maiCerrar.addEventListener('click', maiCerrarFn);
    if (maiModal) maiModal.addEventListener('click', function(e) { if (e.target === maiModal) maiCerrarFn(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && maiModal && maiModal.classList.contains('active')) maiCerrarFn();
    });

    // --- Boton Baja (de este curso) ---
    document.querySelectorAll('.btn-baja-curso').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = this.getAttribute('data-nombre') || 'este estudiante';
            maiForm = this.closest('form');
            maiAbrir(
                'Retirar del Programa',
                'Esta seguro que desea retirar a "' + nombre + '" de este programa?',
                'Se condonaran las cuotas futuras y la inscripcion quedara como Retirado. Las cuotas vencidas y pagadas no se modifican.',
                '#dc2626',
                'Si, retirar'
            );
        });
    });

    // --- Boton Reactivar (de este curso) ---
    document.querySelectorAll('.btn-reactivar-curso').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var nombre = this.getAttribute('data-nombre') || 'este estudiante';
            maiForm = this.closest('form');
            maiAbrir(
                'Reactivar Inscripcion',
                'Desea reactivar la inscripcion de "' + nombre + '" en este programa?',
                'Las cuotas condonadas volveran a Pendiente con nuevas fechas de vencimiento desde hoy.',
                '#059669',
                'Si, reactivar'
            );
        });
    });

    // ===========================
    // DROPDOWN TIPO INSCRIPCION — show
    // ===========================
    function posicionarDropdown(btn, dropdown) {
        var rect = btn.getBoundingClientRect();
        var dropdownWidth = 210;
        var left = rect.right - dropdownWidth;
        if (left < 10) left = 10;
        var top = rect.bottom + 4;
        if (top + 250 > window.innerHeight) {
            top = rect.top - 250;
            if (top < 10) top = 10;
        }
        dropdown.style.left = left + 'px';
        dropdown.style.top = top + 'px';
    }

    document.querySelectorAll('.btn-tipo-inscripcion').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var inscId = this.getAttribute('data-inscripcion-id');
            var dropdown = document.getElementById('dropdown-tipo-' + inscId);
            if (dropdown) {
                var visible = dropdown.style.display === 'block';
                document.querySelectorAll('.dropdown-menu-tipo').forEach(function(d) { d.style.display = 'none'; d.style.left = ''; d.style.top = ''; });
                if (!visible) {
                    posicionarDropdown(this, dropdown);
                    dropdown.style.display = 'block';
                }
            }
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.btn-tipo-inscripcion') && !e.target.closest('.dropdown-menu-tipo')) {
            document.querySelectorAll('.dropdown-menu-tipo').forEach(function(d) { d.style.display = 'none'; d.style.left = ''; d.style.top = ''; });
        }
    });

    var scrollTimer;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(function() {
            document.querySelectorAll('.dropdown-menu-tipo').forEach(function(d) {
                if (d.style.display === 'block') {
                    var id = d.id.replace('dropdown-tipo-', '');
                    var btn = document.querySelector('.btn-tipo-inscripcion[data-inscripcion-id="' + id + '"]');
                    if (btn) posicionarDropdown(btn, d);
                }
            });
        }, 50);
    });

    document.querySelectorAll('.btn-limitar, .btn-continuar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-menu-tipo').forEach(function(d) { d.style.display = 'none'; d.style.left = ''; d.style.top = ''; });
        });
    });

    // --- Botones Limitar ---
    document.querySelectorAll('.btn-limitar').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var nombre = this.getAttribute('data-nombre') || 'este estudiante';
            var accion = this.getAttribute('data-accion');
            var tipoLabel = accion === 'limitar-diplomado' ? 'Solo Diplomado' : 'Solo Especialidad';
            maiForm = this.closest('form');
            maiAbrir(
                'Limitar a ' + tipoLabel,
                'Esta seguro de limitar la inscripcion de "' + nombre + '" a "' + tipoLabel + '"?',
                'Se condonaran todas las cuotas de fases posteriores. Las cuotas ya pagadas se mantienen intactas.',
                '#d97706',
                'Si, ' + (accion === 'limitar-diplomado' ? 'limitar a Diplomado' : 'limitar a Especialidad')
            );
        });
    });

    // --- Botones Continuar ---
    document.querySelectorAll('.btn-continuar').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var nombre = this.getAttribute('data-nombre') || 'este estudiante';
            var accion = this.getAttribute('data-accion');
            var tipoLabel = accion === 'continuar-especialidad' ? 'Especialidad' : 'Maestria';
            maiForm = this.closest('form');
            maiAbrir(
                'Continuar a ' + tipoLabel,
                'Desea que "' + nombre + '" continúe a "' + tipoLabel + '"?',
                'Las cuotas condonadas de la nueva fase volveran a Pendiente con fechas de vencimiento recalculadas desde hoy.',
                '#1B4FD8',
                'Si, continuar'
            );
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

});
