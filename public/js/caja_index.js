/* ============================================
   CAJA_INDEX.JS - Caja y Facturación (Index)
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Búsqueda de alumnos en tiempo real ----------
    const searchInput = document.getElementById('buscar-alumno-finanzas');
    const searchForm = document.getElementById('form-buscar-finanzas');
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

    // ---------- Cambiar programa seleccionado ----------
    const selectPrograma = document.getElementById('select-programa');
    if (selectPrograma) {
        selectPrograma.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('inscripcion_id', this.value);
            window.location.href = url.toString();
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

    // ---------- Animación de cards de resumen ----------
    const resumenCards = document.querySelectorAll('.resumen-card');
    resumenCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(15px)';
        setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // ---------- Animación de filas del cronograma ----------
    const filasCronograma = document.querySelectorAll('.cronograma-table tbody tr');
    filasCronograma.forEach((fila, index) => {
        fila.style.opacity = '0';
        fila.style.transform = 'translateX(-10px)';
        setTimeout(() => {
            fila.style.transition = 'all 0.3s ease';
            fila.style.opacity = '1';
            fila.style.transform = 'translateX(0)';
        }, index * 60);
    });

});
