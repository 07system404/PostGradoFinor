/* ============================================
   REPORTES.JS - Reportes y Planillas
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ===========================
    // DESCARGAR PLANILLA EXCEL
    // ===========================
    var btn = document.getElementById('btnDescargarExcel');
    var sel = document.getElementById('excel_curso_id');
    if (btn && sel) {
        var exportUrl = btn.getAttribute('data-export-url') || '/reportes/exportar-planilla';
        btn.addEventListener('click', function() {
            var id = sel.value;
            if (!id) { sel.focus(); return; }
            btn.disabled = true;
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Procesando...';
            window.location.href = exportUrl + '/' + id;
            setTimeout(function() {
                btn.disabled = false;
                btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Descargar Planilla (.xlsx)';
            }, 3000);
        });
    }

    // ===========================
    // AUTO-HIDE ALERTS
    // ===========================
    document.querySelectorAll('.alert').forEach(function(a) {
        setTimeout(function() {
            a.style.transition = 'opacity 0.5s';
            a.style.opacity = '0';
            setTimeout(function() { a.remove(); }, 500);
        }, 4000);
    });

});
