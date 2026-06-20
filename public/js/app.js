//
// APP.JS — PostGrado Pro
// Lógica general del panel administrativo

document.addEventListener('DOMContentLoaded', function () {

    // Marcar nav-item activo según URL actual
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-item').forEach(function (link) {
        if (link.getAttribute('href') && currentPath.startsWith(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });

});