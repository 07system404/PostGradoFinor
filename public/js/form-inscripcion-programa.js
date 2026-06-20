(function() {
    'use strict';

    function initModalInscripcion() {
        var btnAbrir = document.getElementById('btn-nueva-inscripcion');
        var modal = document.getElementById('modal-nueva-inscripcion');
        if (!btnAbrir || !modal) return;

        var btnCerrar = document.getElementById('fi-cerrar-modal');
        var btnCancelar = document.getElementById('fi-cancelar-modal');
        var form = document.getElementById('form-nueva-inscripcion');
        var estudianteIdField = document.getElementById('fi-estudiante-id'); // sólo modo "programa fijo"

        function abrir() {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            var fechaInput = document.getElementById('modal-fecha-inscripcion');
            if (fechaInput && !fechaInput.value) {
                fechaInput.value = new Date().toISOString().split('T')[0];
            }
        }

        function cerrar() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        btnAbrir.addEventListener('click', abrir);

        if (btnCerrar) btnCerrar.addEventListener('click', cerrar);
        if (btnCancelar) btnCancelar.addEventListener('click', cerrar);

        modal.addEventListener('click', function(e) {
            if (e.target === modal) cerrar();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) cerrar();
        });

        if (form) {
            form.addEventListener('submit', function(e) {
                var valido = true;

                // Validación de campos [required] visibles
                var campos = form.querySelectorAll('[required]');
                campos.forEach(function(campo) {
                    var errorEl = campo.parentElement.querySelector('.error');
                    if (errorEl) errorEl.remove();
                    campo.classList.remove('error-input');
                    if (!campo.value.trim()) {
                        valido = false;
                        campo.classList.add('error-input');
                        var msg = document.createElement('span');
                        msg.className = 'error';
                        msg.textContent = 'Este campo es obligatorio';
                        campo.parentElement.appendChild(msg);
                    }
                });

                // Modo "programa fijo": el alumno se elige por búsqueda (hidden)
                if (estudianteIdField && !estudianteIdField.value) {
                    valido = false;
                    var buscar = document.getElementById('fi-buscar-alumno');
                    if (buscar) {
                        buscar.classList.add('error-input');
                        var wrap = document.getElementById('fi-autocomplete-wrap');
                        var cont = wrap ? wrap.parentElement : buscar.parentElement;
                        if (cont && !cont.querySelector('.error')) {
                            var m = document.createElement('span');
                            m.className = 'error';
                            m.textContent = 'Seleccione un estudiante de la lista';
                            cont.appendChild(m);
                        }
                    }
                }

                if (!valido) {
                    e.preventDefault();
                    var firstErr = form.querySelector('.error-input');
                    if (firstErr) firstErr.focus();
                } else {
                    var btn = form.querySelector('button[type="submit"]');
                    if (btn) { btn.disabled = true; btn.textContent = 'Guardando...'; }
                }
            });
        }

        // Autocompletado de alumno (sólo presente en modo "programa fijo")
        initBuscadorAlumno();
    }

    function initBuscadorAlumno() {
        var input = document.getElementById('fi-buscar-alumno');
        var dropdown = document.getElementById('fi-autocomplete-dropdown');
        var hiddenId = document.getElementById('fi-estudiante-id');
        var seleccionado = document.getElementById('fi-alumno-selected');
        var nombreEl = document.getElementById('fi-alumno-nombre');
        var datosEl = document.getElementById('fi-alumno-datos');
        var btnCambiar = document.getElementById('fi-cambiar-alumno');
        if (!input || !dropdown || !hiddenId) return;

        var buscarUrl = input.getAttribute('data-buscar-url') || '/caja/pago/buscar-alumnos';
        var searchTimeout;

        function limpiarError() {
            input.classList.remove('error-input');
            var wrap = document.getElementById('fi-autocomplete-wrap');
            var cont = wrap ? wrap.parentElement : input.parentElement;
            if (cont) {
                var err = cont.querySelector('.error');
                if (err) err.remove();
            }
        }

        function seleccionar(alumno) {
            hiddenId.value = alumno.id;
            nombreEl.textContent = alumno.nombre_completo;
            datosEl.textContent = ' — CI: ' + alumno.cedula + ' | Reg: ' + alumno.registro;
            dropdown.classList.remove('active');
            dropdown.innerHTML = '';
            input.style.display = 'none';
            seleccionado.style.display = 'flex';
            limpiarError();
        }

        function cambiar() {
            hiddenId.value = '';
            input.value = '';
            input.style.display = 'block';
            seleccionado.style.display = 'none';
            input.focus();
        }

        if (btnCambiar) btnCambiar.addEventListener('click', cambiar);

        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            var q = this.value.trim();
            if (q.length < 2) {
                dropdown.classList.remove('active');
                return;
            }
            searchTimeout = setTimeout(function() {
                fetch(buscarUrl + '?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(function(alumnos) {
                        dropdown.innerHTML = '';
                        if (!alumnos || alumnos.length === 0) {
                            dropdown.innerHTML = '<div class="fi-autocomplete-item fi-autocomplete-empty">No se encontraron alumnos</div>';
                            dropdown.classList.add('active');
                            return;
                        }
                        alumnos.forEach(function(a) {
                            var item = document.createElement('div');
                            item.className = 'fi-autocomplete-item';
                            item.innerHTML =
                                '<span class="fi-item-nombre">' + a.nombre_completo + '</span>' +
                                '<span class="fi-item-detalle">CI: ' + a.cedula + ' &middot; Reg: ' + a.registro + '</span>';
                            item.addEventListener('mousedown', function(e) {
                                e.preventDefault();
                                seleccionar(a);
                            });
                            dropdown.appendChild(item);
                        });
                        dropdown.classList.add('active');
                    })
                    .catch(function() {
                        dropdown.classList.remove('active');
                    });
            }, 300);
        });

        input.addEventListener('blur', function() {
            setTimeout(function() { dropdown.classList.remove('active'); }, 150);
        });

        input.addEventListener('focus', function() {
            if (dropdown.children.length > 0) dropdown.classList.add('active');
        });
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initModalInscripcion();
    } else {
        document.addEventListener('DOMContentLoaded', initModalInscripcion);
    }
})();
