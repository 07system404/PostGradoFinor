/* ============================================
   INSCRIPCIONES.JS - Registro de Estudiante
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---------- Toggle Inscripción Opcional ----------
    const toggle = document.getElementById('toggle-inscripcion');
    const fieldsContainer = document.getElementById('inscripcion-fields');
    const btnText = document.getElementById('btn-text');

    const cardInscripcion = document.getElementById('card-inscripcion');

    function toggleInscripcion(checked) {
        if (checked) {
            fieldsContainer.classList.remove('is-disabled');
            if (cardInscripcion) cardInscripcion.classList.add('toggle-on');
            document.querySelectorAll('#inscripcion-fields select, #inscripcion-fields input').forEach(el => {
                el.removeAttribute('disabled');
                if (el.hasAttribute('data-required')) {
                    el.setAttribute('required', '');
                }
            });
            btnText.textContent = 'Guardar e Inscribir';
        } else {
            fieldsContainer.classList.add('is-disabled');
            if (cardInscripcion) cardInscripcion.classList.remove('toggle-on');
            document.querySelectorAll('#inscripcion-fields select, #inscripcion-fields input').forEach(el => {
                el.setAttribute('disabled', 'disabled');
                // Save required state and remove it
                if (el.hasAttribute('required')) {
                    el.setAttribute('data-required', 'true');
                    el.removeAttribute('required');
                }
            });
            btnText.textContent = 'Guardar Estudiante';
        }
    }

    if (toggle && fieldsContainer) {
        // Initialize state
        toggleInscripcion(toggle.checked);

        toggle.addEventListener('change', function() {
            toggleInscripcion(this.checked);
        });
    }

    // ---------- Botón superior: enviar formulario aunque esté fuera del <form> ----------
    const form = document.getElementById('form-inscripcion');
    const submitBtn = document.getElementById('btn-submit');
    if (submitBtn && form) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            form.submit();
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

    // ---------- Animación de tarjetas ----------
    const cards = document.querySelectorAll('.form-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(15px)';
        setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 120);
    });

});
