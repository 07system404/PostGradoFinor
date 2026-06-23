/* ============================================
   CAJA.JS - Caja y Facturacion
   (index + pago + show pages)
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ===========================
    // TOGGLE PASSWORD VISIBILITY
    // ===========================
    document.querySelectorAll('.btn-toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.previousElementSibling;
            if (input) input.type = input.type === 'password' ? 'text' : 'password';
        });
    });

    // ===========================
    // BUSQUEDA CLIENT-SIDE — index
    // ===========================
    var searchInput = document.getElementById('buscar-alumno-finanzas');
    var listaAlumnos = document.querySelector('.lista-alumnos');

    if (searchInput && listaAlumnos) {
        searchInput.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();
            var items = listaAlumnos.querySelectorAll('.alumno-item');
            var visibles = 0;

            items.forEach(function(item) {
                var nombre = item.getAttribute('data-nombre') || '';
                var cedula = item.getAttribute('data-cedula') || '';
                var registro = item.getAttribute('data-registro') || '';
                var coincide = !query ||
                    nombre.indexOf(query) !== -1 ||
                    cedula.indexOf(query) !== -1 ||
                    registro.indexOf(query) !== -1;

                item.style.display = coincide ? '' : 'none';
                if (coincide) visibles++;
            });

            var emptyMsg = listaAlumnos.querySelector('.search-empty-msg');
            if (visibles === 0 && query) {
                if (!emptyMsg) {
                    emptyMsg = document.createElement('div');
                    emptyMsg.className = 'search-empty-msg';
                    emptyMsg.style.cssText = 'padding:20px;text-align:center;color:var(--gray-400);font-size:14px;';
                    emptyMsg.textContent = 'No se encontraron alumnos.';
                    listaAlumnos.appendChild(emptyMsg);
                }
                emptyMsg.style.display = '';
            } else if (emptyMsg) {
                emptyMsg.style.display = 'none';
            }
        });
    }

    // ===========================
    // CAMBIAR PROGRAMA SELECCIONADO — index
    // ===========================
    var selectPrograma = document.getElementById('select-programa');
    if (selectPrograma) {
        selectPrograma.addEventListener('change', function() {
            var url = new URL(window.location.href);
            url.searchParams.set('inscripcion_id', this.value);
            window.location.href = url.toString();
        });
    }

    // ===========================
    // AUTO-HIDE ALERTS
    // ===========================
    document.querySelectorAll('.alert-success, .alert-error').forEach(function(a) {
        setTimeout(function() {
            a.style.transition = 'opacity 0.5s';
            a.style.opacity = '0';
            setTimeout(function() { a.remove(); }, 500);
        }, 4000);
    });

    // ===========================
    // FORMULARIO DE PAGO — pago
    // ===========================
    var buscarInput = document.getElementById('buscar-alumno-pago');
    var dropdown = document.getElementById('autocomplete-dropdown');
    var sinAlumno = document.getElementById('pago-sin-alumno');
    var alumnoNombre = document.getElementById('pago-alumno-nombre');
    var alumnoDatos = document.getElementById('pago-alumno-datos');
    var btnCambiar = document.getElementById('btn-cambiar-alumno');
    var selectProgramaPago = document.getElementById('select-programa-pago');
    var selectCategoria = document.getElementById('select-categoria-pago');
    var inputModulo = document.getElementById('input-modulo-pago');
    var moduloConfirmacion = document.getElementById('modulo-confirmacion');
    var subSelectModulo = document.getElementById('sub-select-modulo');
    var subSelectDefensa = document.getElementById('sub-select-defensa');
    var selectDefensa = document.getElementById('select-defensa-pago');
    var montoInput = document.getElementById('monto');
    var hiddenDetalleId = document.getElementById('hidden-detalle-id');
    var hiddenInscripcionId = document.getElementById('hidden-inscripcion-id');
    var alertaPagado = document.getElementById('pago-alerta-pagado');
    var alertaPagadoTexto = document.getElementById('pago-alerta-pagado-texto');
    var btnConfirmar = document.getElementById('btn-confirmar-pago');

    var alumnoSeleccionadoData = null;
    var detallesData = null;
    var searchTimeout;
    var moduloTimeout;

    function resetearDesdeAlumno() {
        if (!selectProgramaPago) return;
        selectProgramaPago.disabled = true;
        selectProgramaPago.innerHTML = '<option value="">Seleccione un alumno primero...</option>';
        resetearDesdeCurso();
    }

    function resetearDesdeCurso() {
        if (!selectCategoria) return;
        selectCategoria.disabled = true;
        selectCategoria.value = '';
        resetearSubSelects();
    }

    function resetearSubSelects() {
        if (subSelectModulo) subSelectModulo.style.display = 'none';
        if (subSelectDefensa) subSelectDefensa.style.display = 'none';
        if (inputModulo) { inputModulo.disabled = true; inputModulo.value = ''; inputModulo.max = 99; }
        if (moduloConfirmacion) moduloConfirmacion.style.display = 'none';
        if (selectDefensa) { selectDefensa.disabled = true; selectDefensa.innerHTML = '<option value="">Seleccione un alumno y curso primero...</option>'; }
        if (montoInput) { montoInput.value = ''; montoInput.disabled = true; }
        if (hiddenDetalleId) hiddenDetalleId.value = '';
        if (alertaPagado) alertaPagado.style.display = 'none';
        if (btnConfirmar) btnConfirmar.disabled = true;
    }

    function seleccionarAlumno(alumno) {
        alumnoSeleccionadoData = alumno;
        if (dropdown) dropdown.classList.remove('active');

        if (sinAlumno) sinAlumno.style.display = 'none';
        if (alumnoNombre) { alumnoNombre.style.display = 'inline'; alumnoNombre.textContent = alumno.nombre_completo; }
        if (alumnoDatos) { alumnoDatos.style.display = 'inline'; alumnoDatos.textContent = ' \u2014 CI: ' + alumno.cedula + ' | ' + alumno.registro; }
        if (btnCambiar) btnCambiar.style.display = 'inline-block';

        resetearDesdeAlumno();

        if (alumno.inscripciones_count === 0) {
            selectProgramaPago.innerHTML = '<option value="">El alumno no tiene programas inscritos</option>';
            selectProgramaPago.disabled = true;
            return;
        }

        selectProgramaPago.disabled = false;
        selectProgramaPago.innerHTML = '<option value="">Seleccionar programa...</option>';
        alumno.inscripciones.forEach(function(insc) {
            var opt = document.createElement('option');
            opt.value = insc.id;
            opt.textContent = insc.curso_nombre + ' (' + insc.tipo_inscripcion + ')';
            selectProgramaPago.appendChild(opt);
        });
    }

    function cargarModuloPorNumero(numero) {
        if (!detallesData || !detallesData.modulos || !numero) {
            if (moduloConfirmacion) moduloConfirmacion.style.display = 'none';
            if (montoInput) { montoInput.value = ''; montoInput.disabled = true; }
            if (hiddenDetalleId) hiddenDetalleId.value = '';
            if (alertaPagado) alertaPagado.style.display = 'none';
            if (btnConfirmar) btnConfirmar.disabled = true;
            return;
        }

        var detalle = null;
        for (var i = 0; i < detallesData.modulos.length; i++) {
            var m = detallesData.modulos[i];
            var match = m.concepto.match(/M\u00f3dulo (\d+)/i);
            var numModulo = match ? parseInt(match[1]) : m.nro_cuota;
            if (numModulo === parseInt(numero)) { detalle = m; break; }
        }

        if (!detalle) {
            if (moduloConfirmacion) moduloConfirmacion.style.display = 'none';
            if (montoInput) { montoInput.value = ''; montoInput.disabled = true; }
            if (hiddenDetalleId) hiddenDetalleId.value = '';
            if (alertaPagado) alertaPagado.style.display = 'none';
            if (btnConfirmar) btnConfirmar.disabled = true;
            return;
        }

        if (hiddenDetalleId) hiddenDetalleId.value = detalle.id;
        if (montoInput) { montoInput.value = detalle.saldo_cuota.toFixed(2); montoInput.disabled = false; }
        if (moduloConfirmacion) {
            moduloConfirmacion.textContent = detalle.concepto + ' \u2014 Bs ' + detalle.saldo_cuota.toFixed(2);
            moduloConfirmacion.style.display = 'block';
        }

        if (detalle.esta_pagado) {
            if (moduloConfirmacion) moduloConfirmacion.className = 'pago-modulo-conf pagado';
            if (alertaPagado) { alertaPagado.style.display = 'flex'; alertaPagadoTexto.textContent = 'Este m\u00f3dulo ya fue pagado. Seleccione otro.'; }
            if (btnConfirmar) btnConfirmar.disabled = true;
        } else {
            if (moduloConfirmacion) moduloConfirmacion.className = 'pago-modulo-conf';
            if (alertaPagado) alertaPagado.style.display = 'none';
            if (btnConfirmar) btnConfirmar.disabled = false;
        }
    }

    function cargarDetalles(inscripcionId, callback) {
        resetearDesdeCurso();
        if (hiddenInscripcionId) hiddenInscripcionId.value = inscripcionId;

        fetch('/caja/pago/detalles/' + inscripcionId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                detallesData = data;
                if (selectCategoria) { selectCategoria.disabled = false; selectCategoria.value = ''; }
                if (callback) callback();
            })
            .catch(function() {
                if (selectCategoria) selectCategoria.disabled = true;
            });
    }

    if (selectProgramaPago) {
        selectProgramaPago.addEventListener('change', function() {
            var inscId = parseInt(this.value);
            if (!inscId) { resetearDesdeCurso(); if (hiddenInscripcionId) hiddenInscripcionId.value = ''; return; }
            cargarDetalles(inscId);
        });
    }

    if (selectCategoria) {
        selectCategoria.addEventListener('change', function() {
            var val = this.value;
            resetearSubSelects();
            if (!val || !detallesData) return;

            if (val === 'matricula') {
                var mat = detallesData.matricula;
                if (!mat) {
                    if (alertaPagado) { alertaPagado.style.display = 'flex'; alertaPagadoTexto.textContent = 'No se encontr\u00f3 la cuota de matr\u00edcula en el plan de pagos.'; }
                    return;
                }
                if (hiddenDetalleId) hiddenDetalleId.value = mat.id;
                if (montoInput) { montoInput.value = mat.saldo_cuota.toFixed(2); montoInput.disabled = false; }
                if (mat.esta_pagado) {
                    if (alertaPagado) { alertaPagado.style.display = 'flex'; alertaPagadoTexto.textContent = 'La matr\u00edcula ya fue pagada. Seleccione otro concepto.'; }
                    if (btnConfirmar) btnConfirmar.disabled = true;
                } else {
                    if (alertaPagado) alertaPagado.style.display = 'none';
                    if (btnConfirmar) btnConfirmar.disabled = false;
                }
            } else if (val === 'modulo') {
                var modulos = detallesData.modulos;
                if (!modulos || modulos.length === 0) return;
                var maxNum = 0;
                modulos.forEach(function(m) {
                    var match = m.concepto.match(/M\u00f3dulo (\d+)/i);
                    var n = match ? parseInt(match[1]) : m.nro_cuota;
                    if (n > maxNum) maxNum = n;
                });
                if (inputModulo) { inputModulo.max = maxNum; inputModulo.min = 1; inputModulo.placeholder = '1 \u2013 ' + maxNum; inputModulo.disabled = false; inputModulo.value = ''; inputModulo.focus(); }
                if (moduloConfirmacion) moduloConfirmacion.style.display = 'none';
                if (subSelectModulo) subSelectModulo.style.display = 'block';
            } else if (val === 'defensa') {
                var defensas = detallesData.defensas;
                if (!defensas || defensas.length === 0) return;
                if (selectDefensa) {
                    selectDefensa.disabled = false;
                    selectDefensa.innerHTML = '<option value="">Seleccionar defensa...</option>';
                    defensas.forEach(function(d) {
                        var opt = document.createElement('option');
                        opt.value = d.id;
                        var label = d.concepto + ' \u2014 Bs ' + d.saldo_cuota.toFixed(2);
                        if (d.esta_pagado) { opt.disabled = true; opt.textContent = label + ' (PAGADO)'; }
                        else { opt.textContent = label; }
                        selectDefensa.appendChild(opt);
                    });
                }
                if (subSelectDefensa) subSelectDefensa.style.display = 'block';
            }
        });
    }

    if (inputModulo) {
        inputModulo.addEventListener('input', function() {
            clearTimeout(moduloTimeout);
            var num = this.value.trim();
            if (!num || parseInt(num) < 1 || parseInt(num) > parseInt(this.max)) {
                if (moduloConfirmacion) moduloConfirmacion.style.display = 'none';
                if (montoInput) { montoInput.value = ''; montoInput.disabled = true; }
                if (hiddenDetalleId) hiddenDetalleId.value = '';
                if (alertaPagado) alertaPagado.style.display = 'none';
                if (btnConfirmar) btnConfirmar.disabled = true;
                return;
            }
            moduloTimeout = setTimeout(function() { cargarModuloPorNumero(parseInt(num)); }, 250);
        });

        inputModulo.addEventListener('blur', function() {
            var num = this.value.trim();
            if (num && parseInt(num) >= 1 && parseInt(num) <= parseInt(this.max)) {
                cargarModuloPorNumero(parseInt(num));
            }
        });
    }

    if (selectDefensa) {
        selectDefensa.addEventListener('change', function() {
            var detalleId = parseInt(this.value);
            if (!detalleId || !detallesData || !detallesData.defensas) {
                if (montoInput) { montoInput.value = ''; montoInput.disabled = true; }
                if (hiddenDetalleId) hiddenDetalleId.value = '';
                if (alertaPagado) alertaPagado.style.display = 'none';
                if (btnConfirmar) btnConfirmar.disabled = true;
                return;
            }
            var detalle = null;
            for (var i = 0; i < detallesData.defensas.length; i++) {
                if (detallesData.defensas[i].id === detalleId) { detalle = detallesData.defensas[i]; break; }
            }
            if (!detalle) return;

            if (hiddenDetalleId) hiddenDetalleId.value = detalle.id;
            if (montoInput) { montoInput.value = detalle.saldo_cuota.toFixed(2); montoInput.disabled = false; }

            if (detalle.esta_pagado) {
                if (alertaPagado) { alertaPagado.style.display = 'flex'; alertaPagadoTexto.textContent = 'Esta defensa ya fue pagada. Seleccione otra.'; }
                if (btnConfirmar) btnConfirmar.disabled = true;
            } else {
                if (alertaPagado) alertaPagado.style.display = 'none';
                if (btnConfirmar) btnConfirmar.disabled = false;
            }
        });
    }

    if (btnCambiar) {
        btnCambiar.addEventListener('click', function() {
            alumnoSeleccionadoData = null;
            if (sinAlumno) sinAlumno.style.display = 'inline';
            if (alumnoNombre) alumnoNombre.style.display = 'none';
            if (alumnoDatos) alumnoDatos.style.display = 'none';
            if (btnCambiar) btnCambiar.style.display = 'none';
            if (buscarInput) { buscarInput.value = ''; buscarInput.focus(); }
            resetearDesdeAlumno();
        });
    }

    if (buscarInput) {
        buscarInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            var q = this.value.trim();
            if (q.length < 2) { if (dropdown) dropdown.classList.remove('active'); return; }
            searchTimeout = setTimeout(function() {
                fetch('/caja/pago/buscar-alumnos?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(function(alumnos) {
                        if (!dropdown) return;
                        dropdown.innerHTML = '';
                        if (alumnos.length === 0) {
                            dropdown.innerHTML = '<div class="pago-autocomplete-item" style="cursor:default;color:#999;">No se encontraron alumnos</div>';
                            dropdown.classList.add('active');
                            return;
                        }
                        alumnos.forEach(function(a) {
                            var item = document.createElement('div');
                            item.className = 'pago-autocomplete-item';
                            item.innerHTML =
                                '<div class="item-nombre">' + a.nombre_completo + ' <span class="item-badge">' + a.inscripciones_count + ' prog.</span></div>' +
                                '<div class="item-detalle">CI: ' + a.cedula + ' &middot; Reg: ' + a.registro + '</div>';
                            item.addEventListener('click', function() { seleccionarAlumno(a); });
                            dropdown.appendChild(item);
                        });
                        dropdown.classList.add('active');
                    });
            }, 300);
        });

        buscarInput.addEventListener('blur', function() {
            setTimeout(function() { if (dropdown) dropdown.classList.remove('active'); }, 200);
        });

        buscarInput.addEventListener('focus', function() {
            if (dropdown && dropdown.children.length > 0) dropdown.classList.add('active');
        });
    }

    // --- Dropzone comprobante ---
    var dropzone = document.getElementById('dropzone-comprobante');
    var fileInput = document.getElementById('comprobante');
    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function() { fileInput.click(); });
        dropzone.addEventListener('dragover', function(e) { e.preventDefault(); this.classList.add('dragover'); });
        dropzone.addEventListener('dragleave', function() { this.classList.remove('dragover'); });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault(); this.classList.remove('dragover');
            if (e.dataTransfer.files.length) fileInput.files = e.dataTransfer.files;
        });
    }

    // --- Prefill mode ---
    var form = document.getElementById('form-registro-pago');
    if (form && form.getAttribute('data-prefill') === 'true') {
        var pfEstId = form.getAttribute('data-estudiante-id');
        var pfNombre = form.getAttribute('data-estudiante-nombre');
        var pfCedula = form.getAttribute('data-estudiante-cedula');
        var pfRegistro = form.getAttribute('data-estudiante-registro');
        var pfInscId = form.getAttribute('data-inscripcion-id');
        var pfCurso = form.getAttribute('data-curso-nombre');
        var pfTipo = form.getAttribute('data-tipo-inscripcion');
        var pfDetalleId = form.getAttribute('data-detalle-id');
        var pfConcepto = form.getAttribute('data-concepto');
        var pfMonto = form.getAttribute('data-monto');

        alumnoSeleccionadoData = { id: pfEstId, nombre_completo: pfNombre, cedula: pfCedula, registro: pfRegistro };
        if (sinAlumno) sinAlumno.style.display = 'none';
        if (alumnoNombre) { alumnoNombre.style.display = 'inline'; alumnoNombre.textContent = pfNombre; }
        if (alumnoDatos) { alumnoDatos.style.display = 'inline'; alumnoDatos.textContent = ' \u2014 CI: ' + pfCedula + ' | ' + pfRegistro; }
        if (btnCambiar) btnCambiar.style.display = 'inline-block';
        if (buscarInput) buscarInput.style.display = 'none';

        if (selectProgramaPago) {
            selectProgramaPago.disabled = false;
            selectProgramaPago.innerHTML = '<option value="">Seleccionar programa...</option>';
            var opt = document.createElement('option');
            opt.value = pfInscId;
            opt.textContent = pfCurso + ' (' + pfTipo + ')';
            selectProgramaPago.appendChild(opt);
            selectProgramaPago.value = pfInscId;
        }

        cargarDetalles(parseInt(pfInscId), function() {
            var conceptoLower = pfConcepto.toLowerCase();
            var catValue = '';
            var numModulo = 0;

            if (conceptoLower.indexOf('matr') !== -1) catValue = 'matricula';
            else if (conceptoLower.indexOf('defensa') !== -1) catValue = 'defensa';
            else {
                catValue = 'modulo';
                var matchMod = pfConcepto.match(/(\d+)/);
                if (matchMod) numModulo = parseInt(matchMod[1]);
            }

            if (selectCategoria) { selectCategoria.value = catValue; selectCategoria.dispatchEvent(new Event('change')); }

            if (catValue === 'modulo' && numModulo > 0 && inputModulo) {
                inputModulo.value = numModulo;
                inputModulo.dispatchEvent(new Event('input'));
            } else if (catValue === 'defensa' && selectDefensa) {
                for (var i = 0; i < selectDefensa.options.length; i++) {
                    if (selectDefensa.options[i].value === pfDetalleId) {
                        selectDefensa.value = pfDetalleId;
                        selectDefensa.dispatchEvent(new Event('change'));
                        break;
                    }
                }
            }

            if (!montoInput.value && pfMonto) { montoInput.value = pfMonto; montoInput.disabled = false; }
            if (!hiddenDetalleId.value && pfDetalleId) hiddenDetalleId.value = pfDetalleId;
            if (btnConfirmar) btnConfirmar.disabled = false;
        });
    }

});
