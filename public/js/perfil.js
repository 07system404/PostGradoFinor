/* ============================================
   PERFIL.JS - Detalle del Alumno
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Actualizar datos personales ----------
    const formDatos = document.getElementById('form-datos-personales');
    if (formDatos) {
        formDatos.addEventListener('submit', function(e) {
            const cedula = document.getElementById('cedula');
            if (cedula && cedula.value) {
                // Quitar puntos
                cedula.value = cedula.value.replace(/\./g, '');

                if (!/^\d+$/.test(cedula.value)) {
                    e.preventDefault();
                    alert('La cédula debe contener solo números, sin puntos');
                    cedula.focus();
                    return;
                }
            }

            // Mostrar loading
            const btn = formDatos.querySelector('.btn-actualizar');
            if (btn) {
                const originalHTML = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20">
                            <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
                        </circle>
                    </svg>
                    <span>Guardando...</span>
                `;
            }
        });
    }

    // ---------- Formatear cédula en tiempo real ----------
    const cedulaInput = document.getElementById('cedula');
    if (cedulaInput) {
        cedulaInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    // ---------- Formatear teléfono ----------
    const telefonoInput = document.getElementById('celular');
    if (telefonoInput) {
        telefonoInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\s]/g, '');
        });
    }

    // ---------- Acciones de documentos ----------
    const btnsVerDoc = document.querySelectorAll('.btn-ver-doc');
    btnsVerDoc.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const docUrl = this.dataset.url;
            if (docUrl) {
                window.open(docUrl, '_blank');
            } else {
                alert('Documento no disponible');
            }
        });
    });

    const btnsDescargarDoc = document.querySelectorAll('.btn-descargar-doc');
    btnsDescargarDoc.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const docUrl = this.dataset.url;
            if (docUrl) {
                const a = document.createElement('a');
                a.href = docUrl;
                a.download = '';
                a.click();
            } else {
                alert('Documento no disponible para descargar');
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

    // ---------- Animación de entrada ----------
    const cards = document.querySelectorAll('.perfil-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

});
