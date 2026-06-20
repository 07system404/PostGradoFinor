/* ============================================
   CAJA_INDEX.JS - Caja y Facturación (Index)
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Búsqueda de alumnos en tiempo real (client-side) ----------
    const searchInput = document.getElementById('buscar-alumno-finanzas');
    const listaAlumnos = document.querySelector('.lista-alumnos');

    if (searchInput && listaAlumnos) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const items = listaAlumnos.querySelectorAll('.alumno-item');
            let visibles = 0;

            items.forEach(function(item) {
                const nombre = item.getAttribute('data-nombre') || '';
                const cedula = item.getAttribute('data-cedula') || '';
                const registro = item.getAttribute('data-registro') || '';
                const coincide = !query ||
                    nombre.indexOf(query) !== -1 ||
                    cedula.indexOf(query) !== -1 ||
                    registro.indexOf(query) !== -1;

                item.style.display = coincide ? '' : 'none';
                if (coincide) visibles++;
            });

            let emptyMsg = listaAlumnos.querySelector('.search-empty-msg');
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

    // ---------- Cambiar programa seleccionado ----------
    const selectPrograma = document.getElementById('select-programa');
    if (selectPrograma) {
        selectPrograma.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('inscripcion_id', this.value);
            window.location.href = url.toString();
        });
    }

    // ---------- Auto-hide alerts (solo mensajes flash, no resumen cards) ----------
    const alerts = document.querySelectorAll('.alert-success, .alert-error');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

});
