document.addEventListener('DOMContentLoaded', function() {

    var buscarInput = document.getElementById('buscar-alumno-pago');
    var dropdown = document.getElementById('autocomplete-dropdown');
    var sinAlumno = document.getElementById('pago-sin-alumno');
    var alumnoNombre = document.getElementById('pago-alumno-nombre');
    var alumnoDatos = document.getElementById('pago-alumno-datos');
    var btnCambiar = document.getElementById('btn-cambiar-alumno');
    var selectPrograma = document.getElementById('select-programa-pago');
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
        selectPrograma.disabled = true;
        selectPrograma.innerHTML = '<option value="">Seleccione un alumno primero...</option>';
        selectCategoria.disabled = true;
        selectCategoria.value = '';
        subSelectModulo.style.display = 'none';
        subSelectDefensa.style.display = 'none';
        inputModulo.disabled = true;
        inputModulo.value = '';
        inputModulo.max = 99;
        moduloConfirmacion.style.display = 'none';
        selectDefensa.disabled = true;
        selectDefensa.innerHTML = '<option value="">Seleccione un alumno y curso primero...</option>';
        montoInput.value = '';
        montoInput.disabled = true;
        hiddenDetalleId.value = '';
        hiddenInscripcionId.value = '';
        detallesData = null;
        if (alertaPagado) alertaPagado.style.display = 'none';
        if (btnConfirmar) btnConfirmar.disabled = true;
    }

    function resetearDesdeCurso() {
        selectCategoria.disabled = true;
        selectCategoria.value = '';
        subSelectModulo.style.display = 'none';
        subSelectDefensa.style.display = 'none';
        inputModulo.disabled = true;
        inputModulo.value = '';
        inputModulo.max = 99;
        moduloConfirmacion.style.display = 'none';
        selectDefensa.disabled = true;
        selectDefensa.innerHTML = '<option value="">Seleccione un alumno y curso primero...</option>';
        montoInput.value = '';
        montoInput.disabled = true;
        hiddenDetalleId.value = '';
        if (alertaPagado) alertaPagado.style.display = 'none';
        if (btnConfirmar) btnConfirmar.disabled = true;
    }

    function resetearSubSelects() {
        subSelectModulo.style.display = 'none';
        subSelectDefensa.style.display = 'none';
        inputModulo.disabled = true;
        inputModulo.value = '';
        moduloConfirmacion.style.display = 'none';
        selectDefensa.disabled = true;
        selectDefensa.value = '';
        montoInput.value = '';
        montoInput.disabled = true;
        hiddenDetalleId.value = '';
        if (alertaPagado) alertaPagado.style.display = 'none';
        if (btnConfirmar) btnConfirmar.disabled = true;
    }

    function seleccionarAlumno(alumno) {
        alumnoSeleccionadoData = alumno;
        dropdown.classList.remove('active');

        sinAlumno.style.display = 'none';
        alumnoNombre.style.display = 'inline';
        alumnoDatos.style.display = 'inline';
        btnCambiar.style.display = 'inline-block';
        alumnoNombre.textContent = alumno.nombre_completo;
        alumnoDatos.textContent = ' \u2014 CI: ' + alumno.cedula + ' | ' + alumno.registro;

        resetearDesdeAlumno();

        if (alumno.inscripciones_count === 0) {
            selectPrograma.innerHTML = '<option value="">El alumno no tiene programas inscritos</option>';
            selectPrograma.disabled = true;
            return;
        }

        selectPrograma.disabled = false;
        selectPrograma.innerHTML = '<option value="">Seleccionar programa...</option>';
        alumno.inscripciones.forEach(function(insc) {
            var opt = document.createElement('option');
            opt.value = insc.id;
            opt.textContent = insc.curso_nombre + ' (' + insc.tipo_inscripcion + ')';
            selectPrograma.appendChild(opt);
        });
    }

    function cargarModuloPorNumero(numero) {
        if (!detallesData || !detallesData.modulos || !numero) {
            moduloConfirmacion.style.display = 'none';
            montoInput.value = '';
            montoInput.disabled = true;
            hiddenDetalleId.value = '';
            if (alertaPagado) alertaPagado.style.display = 'none';
            if (btnConfirmar) btnConfirmar.disabled = true;
            return;
        }

        var detalle = null;
        for (var i = 0; i < detallesData.modulos.length; i++) {
            var m = detallesData.modulos[i];
            var match = m.concepto.match(/M\u00f3dulo (\d+)/i);
            var numModulo = match ? parseInt(match[1]) : m.nro_cuota;
            if (numModulo === parseInt(numero)) {
                detalle = m;
                break;
            }
        }

        if (!detalle) {
            moduloConfirmacion.style.display = 'none';
            montoInput.value = '';
            montoInput.disabled = true;
            hiddenDetalleId.value = '';
            if (alertaPagado) alertaPagado.style.display = 'none';
            if (btnConfirmar) btnConfirmar.disabled = true;
            return;
        }

        hiddenDetalleId.value = detalle.id;
        montoInput.value = detalle.saldo_cuota.toFixed(2);
        montoInput.disabled = false;

        moduloConfirmacion.textContent = detalle.concepto + ' \u2014 Bs ' + detalle.saldo_cuota.toFixed(2);
        moduloConfirmacion.style.display = 'block';

        if (detalle.esta_pagado) {
            moduloConfirmacion.className = 'pago-modulo-conf pagado';
            if (alertaPagado) {
                alertaPagado.style.display = 'flex';
                alertaPagadoTexto.textContent = 'Este m\u00f3dulo ya fue pagado. Seleccione otro.';
            }
            if (btnConfirmar) btnConfirmar.disabled = true;
        } else {
            moduloConfirmacion.className = 'pago-modulo-conf';
            if (alertaPagado) alertaPagado.style.display = 'none';
            if (btnConfirmar) btnConfirmar.disabled = false;
        }
    }

    function cargarDetalles(inscripcionId, callback) {
        resetearDesdeCurso();
        hiddenInscripcionId.value = inscripcionId;

        fetch('/caja/pago/detalles/' + inscripcionId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                detallesData = data;
                selectCategoria.disabled = false;
                selectCategoria.value = '';
                if (callback) callback();
            })
            .catch(function() {
                selectCategoria.disabled = true;
            });
    }

    selectPrograma.addEventListener('change', function() {
        var inscId = parseInt(this.value);
        if (!inscId) {
            resetearDesdeCurso();
            hiddenInscripcionId.value = '';
            return;
        }
        cargarDetalles(inscId);
    });

    selectCategoria.addEventListener('change', function() {
        var val = this.value;
        resetearSubSelects();

        if (!val || !detallesData) return;

        if (val === 'matricula') {
            var mat = detallesData.matricula;
            if (!mat) {
                if (alertaPagado) {
                    alertaPagado.style.display = 'flex';
                    alertaPagadoTexto.textContent = 'No se encontr\u00f3 la cuota de matr\u00edcula en el plan de pagos.';
                }
                return;
            }
            hiddenDetalleId.value = mat.id;
            montoInput.value = mat.saldo_cuota.toFixed(2);
            montoInput.disabled = false;

            if (mat.esta_pagado) {
                if (alertaPagado) {
                    alertaPagado.style.display = 'flex';
                    alertaPagadoTexto.textContent = 'La matr\u00edcula ya fue pagada. Seleccione otro concepto.';
                }
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

            inputModulo.max = maxNum;
            inputModulo.min = 1;
            inputModulo.placeholder = '1 \u2013 ' + maxNum;
            inputModulo.disabled = false;
            inputModulo.value = '';
            moduloConfirmacion.style.display = 'none';
            subSelectModulo.style.display = 'block';
            inputModulo.focus();
        } else if (val === 'defensa') {
            var defensas = detallesData.defensas;
            if (!defensas || defensas.length === 0) return;

            selectDefensa.disabled = false;
            selectDefensa.innerHTML = '<option value="">Seleccionar defensa...</option>';
            defensas.forEach(function(d) {
                var opt = document.createElement('option');
                opt.value = d.id;
                var label = d.concepto + ' \u2014 Bs ' + d.saldo_cuota.toFixed(2);
                if (d.esta_pagado) {
                    opt.disabled = true;
                    opt.textContent = label + ' (PAGADO)';
                } else {
                    opt.textContent = label;
                }
                selectDefensa.appendChild(opt);
            });
            subSelectDefensa.style.display = 'block';
        }
    });

    inputModulo.addEventListener('input', function() {
        clearTimeout(moduloTimeout);
        var num = this.value.trim();
        if (!num || parseInt(num) < 1 || parseInt(num) > parseInt(this.max)) {
            moduloConfirmacion.style.display = 'none';
            montoInput.value = '';
            montoInput.disabled = true;
            hiddenDetalleId.value = '';
            if (alertaPagado) alertaPagado.style.display = 'none';
            if (btnConfirmar) btnConfirmar.disabled = true;
            return;
        }
        moduloTimeout = setTimeout(function() {
            cargarModuloPorNumero(parseInt(num));
        }, 250);
    });

    inputModulo.addEventListener('blur', function() {
        var num = this.value.trim();
        if (num && parseInt(num) >= 1 && parseInt(num) <= parseInt(this.max)) {
            cargarModuloPorNumero(parseInt(num));
        }
    });

    selectDefensa.addEventListener('change', function() {
        var detalleId = parseInt(this.value);
        if (!detalleId || !detallesData || !detallesData.defensas) {
            montoInput.value = '';
            montoInput.disabled = true;
            hiddenDetalleId.value = '';
            if (alertaPagado) alertaPagado.style.display = 'none';
            if (btnConfirmar) btnConfirmar.disabled = true;
            return;
        }
        var detalle = null;
        for (var i = 0; i < detallesData.defensas.length; i++) {
            if (detallesData.defensas[i].id === detalleId) {
                detalle = detallesData.defensas[i];
                break;
            }
        }
        if (!detalle) return;

        hiddenDetalleId.value = detalle.id;
        montoInput.value = detalle.saldo_cuota.toFixed(2);
        montoInput.disabled = false;

        if (detalle.esta_pagado) {
            if (alertaPagado) {
                alertaPagado.style.display = 'flex';
                alertaPagadoTexto.textContent = 'Esta defensa ya fue pagada. Seleccione otra.';
            }
            if (btnConfirmar) btnConfirmar.disabled = true;
        } else {
            if (alertaPagado) alertaPagado.style.display = 'none';
            if (btnConfirmar) btnConfirmar.disabled = false;
        }
    });

    btnCambiar.addEventListener('click', function() {
        alumnoSeleccionadoData = null;
        sinAlumno.style.display = 'inline';
        alumnoNombre.style.display = 'none';
        alumnoDatos.style.display = 'none';
        btnCambiar.style.display = 'none';
        buscarInput.value = '';
        buscarInput.focus();
        resetearDesdeAlumno();
    });

    if (buscarInput) {
        buscarInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            var q = this.value.trim();
            if (q.length < 2) {
                dropdown.classList.remove('active');
                return;
            }
            searchTimeout = setTimeout(function() {
                fetch('/caja/pago/buscar-alumnos?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(function(alumnos) {
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
            setTimeout(function() { dropdown.classList.remove('active'); }, 200);
        });

        buscarInput.addEventListener('focus', function() {
            if (dropdown.children.length > 0) {
                dropdown.classList.add('active');
            }
        });
    }

    var dropzone = document.getElementById('dropzone-comprobante');
    var fileInput = document.getElementById('comprobante');
    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function() { fileInput.click(); });
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        dropzone.addEventListener('dragleave', function() {
            this.classList.remove('dragover');
        });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
            }
        });
    }

    // === MODO PRE-LLENADO: autocompletar campos desde data- attributes ===
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
        var pfFase = form.getAttribute('data-fase');
        var pfMonto = form.getAttribute('data-monto');

        // 1) Mostrar alumno como seleccionado
        alumnoSeleccionadoData = { id: pfEstId, nombre_completo: pfNombre, cedula: pfCedula, registro: pfRegistro };
        sinAlumno.style.display = 'none';
        alumnoNombre.style.display = 'inline';
        alumnoDatos.style.display = 'inline';
        btnCambiar.style.display = 'inline-block';
        alumnoNombre.textContent = pfNombre;
        alumnoDatos.textContent = ' \u2014 CI: ' + pfCedula + ' | ' + pfRegistro;
        buscarInput.style.display = 'none';

        // 2) Poblar select de programa con la inscripcion actual
        selectPrograma.disabled = false;
        selectPrograma.innerHTML = '<option value="">Seleccionar programa...</option>';
        var opt = document.createElement('option');
        opt.value = pfInscId;
        opt.textContent = pfCurso + ' (' + pfTipo + ')';
        selectPrograma.appendChild(opt);
        selectPrograma.value = pfInscId;

        // 3) Cargar detalles y luego autocompletar categoría + monto
        cargarDetalles(parseInt(pfInscId), function() {
            // Detectar categoría desde el concepto
            var conceptoLower = pfConcepto.toLowerCase();
            var catValue = '';
            var numModulo = 0;

            if (conceptoLower.indexOf('matr') !== -1) {
                catValue = 'matricula';
            } else if (conceptoLower.indexOf('defensa') !== -1) {
                catValue = 'defensa';
            } else {
                catValue = 'modulo';
                var matchMod = pfConcepto.match(/(\d+)/);
                if (matchMod) numModulo = parseInt(matchMod[1]);
            }

            // Seleccionar categoría
            selectCategoria.value = catValue;
            selectCategoria.dispatchEvent(new Event('change'));

            if (catValue === 'modulo' && numModulo > 0) {
                // Poner número de módulo y disparar input
                inputModulo.value = numModulo;
                inputModulo.dispatchEvent(new Event('input'));
            } else if (catValue === 'defensa') {
                // Seleccionar defensa por ID del detalle
                for (var i = 0; i < selectDefensa.options.length; i++) {
                    if (selectDefensa.options[i].value === pfDetalleId) {
                        selectDefensa.value = pfDetalleId;
                        selectDefensa.dispatchEvent(new Event('change'));
                        break;
                    }
                }
            }
            // Para matrícula, el change event ya setea el monto y hidden ID

            // Si por alguna razón el monto no se autocompletó desde los eventos, forzarlo
            if (!montoInput.value && pfMonto) {
                montoInput.value = pfMonto;
                montoInput.disabled = false;
            }
            if (!hiddenDetalleId.value && pfDetalleId) {
                hiddenDetalleId.value = pfDetalleId;
            }
            if (btnConfirmar) btnConfirmar.disabled = false;
        });
    }

});
