document.addEventListener('DOMContentLoaded', function () {
    // Password toggle
    document.querySelectorAll('.btn-toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = this.previousElementSibling;
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
});
