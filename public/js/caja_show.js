/* ============================================
   CAJA_SHOW.JS - Detalle de Cuenta
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {
    // Password toggle (si existe en la vista)
    document.querySelectorAll('.btn-toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = this.previousElementSibling;
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
});
