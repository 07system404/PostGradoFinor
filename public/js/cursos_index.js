/* ============================================
   CURSOS_INDEX.JS - Listado de Programas
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

    // ---------- Confirmar activar/desactivar (delegación, funciona tras AJAX) ----------
    document.addEventListener('click', function(e) {
        const btnInactivar = e.target.closest('.btn-inactivar-prog');
        const btnActivar = e.target.closest('.btn-icon-activate');
        
        if (btnInactivar) {
            if (!confirm('¿Desactivar este programa?')) {
                e.preventDefault();
            }
        } else if (btnActivar) {
            if (!confirm('¿Activar este programa?')) {
                e.preventDefault();
            }
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
