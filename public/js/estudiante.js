/* ============================================
   ESTUDIANTES.JS - Gestión de Alumnos
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Búsqueda en tiempo real ----------
    const searchInput = document.getElementById('buscar-alumno');
    const searchForm = document.getElementById('form-buscar-alumnos');
    let searchTimeout;

    if (searchInput && searchForm) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2 || this.value.length === 0) {
                    searchForm.submit();
                }
            }, 500);
        });
    }

    // ---------- Filtro por curso ----------
    const filterSelect = document.getElementById('filtro-curso');

    if (filterSelect && searchForm) {
        filterSelect.addEventListener('change', function() {
            searchForm.submit();
        });
    }

    // ---------- Confirmación al subir documentos ----------
    const btnsSubir = document.querySelectorAll('.btn-subir-doc');
    btnsSubir.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const alumno = this.dataset.alumno || 'este alumno';

            // Crear modal simple
            if (confirm('¿Desea subir documentos para ' + alumno + '?')) {
                // Aquí iría la lógica de subida de archivos
                window.location.href = this.dataset.href || '#';
            }
        });
    });

    // ---------- Tooltips en acciones ----------
    const acciones = document.querySelectorAll('.btn-accion');
    acciones.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            const tooltip = this.getAttribute('title');
            if (tooltip) {
                this.dataset.tooltip = tooltip;
            }
        });
    });

    // ---------- Animación de filas ----------
    const filas = document.querySelectorAll('table.data-table tbody tr');
    filas.forEach((fila, index) => {
        fila.style.opacity = '0';
        fila.style.transform = 'translateY(10px)';
        setTimeout(() => {
            fila.style.transition = 'all 0.3s ease';
            fila.style.opacity = '1';
            fila.style.transform = 'translateY(0)';
        }, index * 80);
    });

    // ---------- Exportar reporte con loading ----------
    const btnExportar = document.getElementById('btn-exportar');
    if (btnExportar) {
        btnExportar.addEventListener('click', function(e) {
            const originalText = this.innerHTML;
            this.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20">
                        <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
                    </circle>
                </svg>
                <span>Exportando...</span>
            `;
            this.style.pointerEvents = 'none';

            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.pointerEvents = 'auto';
            }, 2000);
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
