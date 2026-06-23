@props(['alumnoId' => null, 'alumnoNombre' => null, 'redirectTo' => 'index'])

<div class="cd-modal-overlay" id="cd-modal-documento">
    <div class="cd-modal-box">
        <div class="cd-modal-header">
            <h3 class="cd-modal-title" id="cd-modal-titulo">Cargar Documento{{ $alumnoNombre ? ' — ' . $alumnoNombre : '' }}</h3>
            <button type="button" class="cd-modal-close" id="cd-cerrar-modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="cd-form-documento" action="{{ $alumnoId ? route('documentos.store', $alumnoId) : '#' }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            <div class="cd-modal-body">
                <div class="cd-form-group">
                    <label for="cd-tipo">Tipo de Documento <span class="cd-required">*</span></label>
                    <select id="cd-tipo" name="tipo" required>
                        <option value="">Seleccione un tipo...</option>
                        <option value="Grado de Bachiller">Grado de Bachiller</option>
                        <option value="Certificado de Idiomas">Certificado de Idiomas</option>
                        <option value="Copia de DNI / Pasaporte">Copia de DNI / Pasaporte</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="cd-form-group">
                    <label for="cd-archivo">Archivo (PNG, JPG, PDF) <span class="cd-required">*</span></label>
                    <div class="cd-dropzone" id="cd-dropzone">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <p class="cd-dropzone-text">Arrastra el archivo aquí</p>
                        <p class="cd-dropzone-subtext">o haz clic para seleccionar</p>
                    </div>
                    <input type="file" id="cd-archivo" name="archivo" accept=".jpg,.jpeg,.png,.pdf" class="cd-hidden" required>
                </div>
            </div>
            <div class="cd-modal-footer">
                <button type="button" class="cd-btn-cancelar" id="cd-cancelar">Cancelar</button>
                <button type="submit" class="cd-btn-subir" id="cd-btn-subir">Cargar Documento</button>
            </div>
        </form>
    </div>
</div>

<script src="/js/form-cargar-documento.js"></script>
