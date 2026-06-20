// BACKUP.JS — PostGrado Pro

document.addEventListener('DOMContentLoaded', function () {

    const input     = document.getElementById('archivoBackup');
    const dropZone  = document.getElementById('dropZone');
    const fileName  = document.getElementById('fileName');
    const uploadLbl = document.getElementById('uploadLabel');

    if (!input) return;

    // Clic en la zona abre el selector — pero NO cuando el clic viene del propio input
    dropZone.addEventListener('click', function (e) {
        if (e.target === input) return; // evita el doble disparo
        input.click();
    });

    // Mostrar nombre al seleccionar
    input.addEventListener('change', function () {
        mostrarArchivo(this.files[0]);
    });

    // Drag & drop
    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });
    dropZone.addEventListener('dragleave', function () {
        dropZone.classList.remove('dragover');
    });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && file.name.endsWith('.sqlite')) {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            mostrarArchivo(file);
        } else {
            alert('Solo se aceptan archivos .sqlite');
        }
    });

    function mostrarArchivo(file) {
        if (!file) return;
        fileName.textContent    = file.name + ' (' + formatBytes(file.size) + ')';
        uploadLbl.style.display = 'none';
        dropZone.classList.add('has-file');
    }

    function formatBytes(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        if (bytes >= 1024)    return (bytes / 1024).toFixed(1) + ' KB';
        return bytes + ' B';
    }

    // Cerrar modal al hacer clic fuera
    const overlay = document.getElementById('modalConfirm');
    if (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) cerrarModal();
        });
    }
});

// Modal de confirmación
function confirmarRestaurar() {
    const input = document.getElementById('archivoBackup');
    if (!input || !input.files.length) {
        alert('Primero selecciona un archivo .sqlite para restaurar.');
        return;
    }
    document.getElementById('modalConfirm').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modalConfirm').style.display = 'none';
}