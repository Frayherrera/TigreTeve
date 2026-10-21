<x-app-layout>
    <style>
        /* Contenedor del envío masivo. Se usa el prefijo .wa- para no chocar
           con las clases de Bootstrap 4 (.container, .row, .alert, .form-control…)
           que layouts/app.blade.php carga por CDN. */
        .wa-page {
            max-width: 1120px;
            margin: 0 auto;
            padding: 32px 16px 64px;
            color: #111b21;
        }

        .wa-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        .wa-head h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            letter-spacing: -0.01em;
        }

        .wa-head p {
            margin: 4px 0 0;
            font-size: 0.875rem;
            color: #667781;
        }

        .wa-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 24px;
            align-items: start;
        }

        .wa-col-preview {
            position: sticky;
            top: 88px;
        }

        .wa-card {
            background: #fff;
            border: 1px solid #e9edef;
            border-radius: 16px;
            box-shadow: 0 1px 2px rgba(11, 20, 26, 0.04);
            overflow: hidden;
        }

        .wa-alert {
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 20px;
            border: 1px solid;
        }

        .wa-alert--info {
            background: #d9fdd3;
            border-color: #c5f0bd;
            color: #075e54;
        }

        .wa-alert--error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #b91c1c;
        }

        .wa-section {
            padding: 22px 24px;
            border-bottom: 1px solid #eef1f3;
        }

        .wa-section:last-of-type {
            border-bottom: 0;
        }

        .wa-section-title {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #3b4a54;
            letter-spacing: 0.01em;
            margin: 0 0 4px;
        }

        .wa-section-note {
            font-size: 0.75rem;
            color: #8696a0;
            margin: 0 0 16px;
        }

        .wa-field {
            margin-bottom: 16px;
        }

        .wa-field:last-child {
            margin-bottom: 0;
        }

        .wa-field label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #3b4a54;
            margin-bottom: 6px;
        }

        .wa-field .wa-hint {
            display: block;
            font-weight: 400;
            color: #8696a0;
            margin-top: 2px;
        }

        .wa-input,
        .wa-textarea {
            width: 100%;
            border: 1px solid #d1d7db;
            border-radius: 10px;
            padding: 11px 13px;
            font-family: inherit;
            font-size: 0.9375rem;
            color: #111b21;
            background: #fff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .wa-input::placeholder,
        .wa-textarea::placeholder {
            color: #a9b4bb;
        }

        .wa-input:focus,
        .wa-textarea:focus {
            outline: none;
            border-color: #008069;
            box-shadow: 0 0 0 3px rgba(0, 128, 105, 0.14);
        }

        .wa-input.is-invalid,
        .wa-textarea.is-invalid {
            border-color: #e53935;
            box-shadow: 0 0 0 3px rgba(229, 57, 53, 0.12);
        }

        .wa-textarea {
            min-height: 132px;
            resize: vertical;
            line-height: 1.5;
        }

        .wa-row2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .wa-counter {
            text-align: right;
            font-size: 0.75rem;
            color: #8696a0;
            margin-top: 5px;
            font-variant-numeric: tabular-nums;
        }

        .wa-counter.is-warn {
            color: #b45309;
        }

        .wa-counter.is-over {
            color: #dc2626;
        }

        /* Dropzone */
        .wa-drop {
            position: relative;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border: 1.5px dashed #c3ccd1;
            border-radius: 12px;
            background: #fbfcfc;
            cursor: pointer;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        .wa-drop:hover,
        .wa-drop.is-drag {
            border-color: #008069;
            background: #f2faf7;
        }

        .wa-drop input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .wa-drop-icon {
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: #e7f7f1;
            color: #008069;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }

        .wa-drop-text {
            min-width: 0;
        }

        .wa-drop-text strong {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #3b4a54;
        }

        .wa-drop-text span {
            display: block;
            font-size: 0.75rem;
            color: #8696a0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        /* Fila de origen de contactos */
        .wa-source {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid #d9fdd3;
            background: #f4fcf2;
            border-radius: 12px;
        }

        .wa-switch {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            margin: 0;
            font-size: 0.875rem;
            font-weight: 500;
            color: #3b4a54;
        }

        .wa-switch input {
            appearance: none;
            -webkit-appearance: none;
            width: 40px;
            height: 22px;
            border-radius: 999px;
            background: #c9d2d6;
            position: relative;
            cursor: pointer;
            transition: background-color 0.18s ease;
            flex-shrink: 0;
        }

        .wa-switch input::after {
            content: "";
            position: absolute;
            top: 2px;
            left: 2px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 2px rgba(11, 20, 26, 0.3);
            transition: transform 0.18s ease;
        }

        .wa-switch input:checked {
            background: #008069;
        }

        .wa-switch input:checked::after {
            transform: translateX(18px);
        }

        .wa-switch input:focus-visible {
            outline: 2px solid #008069;
            outline-offset: 2px;
        }

        .wa-source .wa-count {
            margin-left: auto;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #075e54;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .wa-source .wa-manage {
            font-size: 0.75rem;
            font-weight: 600;
            color: #008069;
            text-decoration: none;
            white-space: nowrap;
        }

        .wa-source .wa-manage:hover {
            text-decoration: underline;
        }

        .wa-note {
            display: flex;
            gap: 10px;
            padding: 13px 15px;
            border-radius: 12px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            font-size: 0.8125rem;
            line-height: 1.45;
        }

        .wa-note i {
            margin-top: 2px;
        }

        /* Pie de acciones */
        .wa-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
            padding: 18px 24px;
            background: #f7f9fa;
            border-top: 1px solid #eef1f3;
        }

        .wa-btn-send {
            flex: 1 1 240px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 24px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(to right, #128c7e, #128c7e);
            color: #fff;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: filter 0.2s ease, transform 0.12s ease;
        }

        .wa-btn-send:hover {
            filter: brightness(1.05);
        }

        .wa-btn-send:active {
            transform: scale(0.99);
        }

        .wa-btn-send:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            filter: none;
            transform: none;
        }

        .wa-btn-send i {
            font-size: 1.15rem;
        }

        .wa-foot-note {
            flex: 1 1 200px;
            font-size: 0.75rem;
            color: #8696a0;
            line-height: 1.4;
        }

        /* Columna de vista previa */
        .wa-preview-panel {
            background: #fff;
            border: 1px solid #e9edef;
            border-radius: 16px;
            box-shadow: 0 1px 2px rgba(11, 20, 26, 0.04);
            overflow: hidden;
        }

        .wa-preview-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            background: #008069;
            color: #fff;
        }

        .wa-preview-head .wa-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .wa-preview-head strong {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .wa-preview-head small {
            display: block;
            font-size: 0.6875rem;
            opacity: 0.85;
        }

        .wa-preview-chat {
            background-color: #efeae2;
            background-image: radial-gradient(rgba(255, 255, 255, 0.45) 1px, transparent 1px);
            background-size: 18px 18px;
            padding: 20px 16px 18px;
            min-height: 220px;
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
        }

        .wa-bubble {
            position: relative;
            background: #d9fdd3;
            color: #111b21;
            border-radius: 12px 2px 12px 12px;
            padding: 11px 13px 22px;
            max-width: 88%;
            font-size: 0.9375rem;
            line-height: 1.45;
            box-shadow: 0 1px 2px rgba(11, 20, 26, 0.13);
        }

        .wa-bubble-media {
            display: block;
            width: 100%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 8px;
            background: #d1d7db;
        }

        .wa-bubble-title {
            font-size: 0.9375rem;
            font-weight: 600;
            margin: 0 0 4px;
            overflow-wrap: break-word;
        }

        .wa-bubble-body {
            margin: 0;
            overflow-wrap: break-word;
            white-space: pre-wrap;
        }

        .wa-bubble-title:empty,
        .wa-bubble-body:empty {
            display: none;
        }

        .wa-bubble-cta {
            display: block;
            margin-top: 10px;
            padding: 9px 12px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 8px;
            text-align: center;
            font-size: 0.875rem;
            font-weight: 500;
            color: #008069;
        }

        .wa-bubble-meta {
            position: absolute;
            right: 11px;
            bottom: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.6875rem;
            color: #667781;
        }

        .wa-bubble-meta i {
            color: #53bdeb;
            font-size: 0.75rem;
        }

        .wa-placeholder {
            max-width: 88%;
            font-size: 0.8125rem;
            color: #667781;
            background: rgba(255, 255, 255, 0.75);
            border-radius: 12px 2px 12px 12px;
            padding: 12px 14px;
            line-height: 1.45;
        }

        .wa-preview-audience {
            padding: 14px 16px;
            border-top: 1px solid #eef1f3;
        }

        .wa-audience-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            font-size: 0.8125rem;
            padding: 5px 0;
        }

        .wa-audience-row .label {
            color: #667781;
        }

        .wa-audience-row .value {
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            color: #111b21;
        }

        .wa-audience-total {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-top: 8px;
            padding-top: 10px;
            border-top: 1px dashed #d1d7db;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .wa-audience-total .value {
            font-size: 1.05rem;
            font-variant-numeric: tabular-nums;
            color: #008069;
        }

        .wa-preview-warn {
            margin: 0 16px 16px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
            font-size: 0.75rem;
            line-height: 1.4;
            display: none;
        }

        .wa-preview-warn.is-visible {
            display: block;
        }

        /* Overlay de envío */
        .wa-loading {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 18px;
            padding: 24px;
            text-align: center;
            color: #fff;
            background: rgba(11, 20, 26, 0.72);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }

        .wa-loading.is-active {
            display: flex;
        }

        .wa-spinner {
            width: 62px;
            height: 62px;
            border: 6px solid rgba(255, 255, 255, 0.25);
            border-top-color: #25d366;
            border-radius: 50%;
            animation: wa-spin 0.9s linear infinite;
        }

        @keyframes wa-spin {
            to { transform: rotate(360deg); }
        }

        .wa-loading p {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
        }

        .wa-loading small {
            opacity: 0.85;
            font-size: 0.8125rem;
        }

        /* Modal de confirmación */
        .wa-modal {
            position: fixed;
            inset: 0;
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(11, 20, 26, 0.55);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        .wa-modal.is-open {
            display: flex;
            animation: wa-fade 0.2s ease-out;
        }

        @keyframes wa-fade {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .wa-modal-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(11, 20, 26, 0.35);
            animation: wa-rise 0.26s cubic-bezier(0.2, 0.9, 0.3, 1);
        }

        @keyframes wa-rise {
            from { opacity: 0; transform: translateY(16px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .wa-modal-panel {
            padding: 22px 24px 24px;
        }

        .wa-modal-panel h3 {
            margin: 0 0 4px;
            font-size: 1rem;
            font-weight: 600;
        }

        .wa-modal-panel .wa-modal-sub {
            margin: 0 0 18px;
            font-size: 0.8125rem;
            color: #667781;
        }

        .wa-modal-panel .wa-modal-sub strong {
            color: #111b21;
            font-variant-numeric: tabular-nums;
        }

        .wa-btn-confirm {
            width: 100%;
            padding: 13px 16px;
            border: none;
            border-radius: 10px;
            background: #008069;
            color: #fff;
            font-family: inherit;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.2s ease;
        }

        .wa-btn-confirm:hover {
            background: #017561;
        }

        .wa-btn-confirm:active {
            transform: scale(0.99);
        }

        .wa-btn-cancel {
            width: 100%;
            margin-top: 8px;
            padding: 10px;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: #667781;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .wa-btn-cancel:hover {
            background: #f0f2f5;
            color: #3b4a54;
        }

        .wa-btn-confirm:focus-visible,
        .wa-btn-cancel:focus-visible {
            outline: 2px solid #008069;
            outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            .wa-modal.is-open,
            .wa-modal-card,
            .wa-loading.is-active {
                animation: none;
            }
        }

        @media (max-width: 900px) {
            .wa-grid {
                grid-template-columns: 1fr;
            }

            .wa-col-preview {
                position: static;
            }
        }

        @media (max-width: 560px) {
            .wa-row2 {
                grid-template-columns: 1fr;
            }

            .wa-section {
                padding: 18px 18px;
            }

            .wa-foot {
                padding: 16px 18px;
            }
        }
    </style>

    <div class="wa-page" id="waPage">
        <div class="wa-head">
            <div>
                <h1>Envío masivo de WhatsApp</h1>
                <p>Redacta el mensaje, revisa cómo se verá y envíalo a tu lista de contactos.</p>
            </div>
            <a href="{{ route('numeros.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">
                <i class="fas fa-address-book"></i>
                Administrar números
            </a>
        </div>

        @if (session('msg'))
            <div class="wa-alert wa-alert--info">
                <i class="fas fa-circle-check" style="margin-top:2px;"></i>
                <div>{!! session('msg') !!}</div>
            </div>
        @endif

        @error('file')
            <div class="wa-alert wa-alert--error">
                <i class="fas fa-circle-exclamation" style="margin-top:2px;"></i>
                <div>{{ $message }}</div>
            </div>
        @enderror

        <form action="{{ route('whatsapp.masivo') }}" method="POST" enctype="multipart/form-data" id="formMasivo">
            @csrf

            <div class="wa-grid">
                <div>
                    <div class="wa-card">
                        <div class="wa-section">
                            <h2 class="wa-section-title">Destinatarios</h2>
                            <p class="wa-section-note">Elige de dónde salen los números: la lista guardada o un archivo.</p>

                            <div class="wa-source">
                                <label class="wa-switch" for="list_from_db">
                                    <input type="checkbox" name="list_from_db" id="list_from_db" value="1" checked>
                                    <span>Usar la lista de contactos guardada</span>
                                </label>
                                <span class="wa-count" id="dbCountLabel">{{ $dbCount ?? 0 }} en la lista</span>
                            </div>

                            <div class="wa-field" style="margin-top:16px;">
                                <label for="file">
                                    Archivo CSV
                                    <span class="wa-hint">Opcional. Se suman a los números de la lista.</span>
                                </label>
                                <label class="wa-drop" id="fileDrop" for="file">
                                    <input type="file" name="file" id="file" accept=".csv,.txt">
                                    <span class="wa-drop-icon"><i class="fas fa-file-csv"></i></span>
                                    <span class="wa-drop-text">
                                        <strong id="fileLabel">Sube un archivo CSV</strong>
                                        <span id="fileHint">Una columna «teléfono» o varias; se limpian los repetidos.</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="wa-section">
                            <h2 class="wa-section-title">Mensaje</h2>
                            <p class="wa-section-note">La imagen y el botón son obligatorios en la plantilla de WhatsApp.</p>

                            <div class="wa-field">
                                <label for="titulo">Título</label>
                                <input type="text" class="wa-input" id="titulo" name="titulo" required
                                    autocomplete="off" placeholder="Ej: ¡Nueva oferta especial!">
                            </div>

                            <div class="wa-field">
                                <label for="image">Link de la imagen</label>
                                <input type="url" class="wa-input" id="image" name="image" required
                                    placeholder="https://ejemplo.com/imagen.jpg">
                            </div>

                            <div class="wa-field">
                                <label for="link">Link del botón</label>
                                <input type="url" class="wa-input" id="link" name="link" required
                                    placeholder="https://tigreteve.com/noticias/slug">
                            </div>

                            <div class="wa-field">
                                <label for="cuerpo">Cuerpo del mensaje</label>
                                <textarea class="wa-textarea" id="cuerpo" name="cuerpo" required
                                    placeholder="Escribe aquí el contenido principal del mensaje..."></textarea>
                                <div class="wa-counter" id="charCounter">0 / 1000 caracteres</div>
                            </div>
                        </div>

                        <div class="wa-section">
                            <div class="wa-note">
                                <i class="fas fa-triangle-exclamation"></i>
                                <span>Envía solo a quien te haya autorizado. La plantilla de WhatsApp no admite saltos de línea: se convierten en espacios.</span>
                            </div>
                        </div>

                        <div class="wa-foot">
                            <button type="submit" class="wa-btn-send" id="sendButton">
                                <i class="fab fa-whatsapp"></i>
                                <span id="sendButtonText">Enviar mensajes</span>
                            </button>
                            <p class="wa-foot-note" id="footNote">Cada contacto se procesa en segundo plano desde la cola.</p>
                        </div>
                    </div>
                </div>

                <div class="wa-col-preview">
                    <div class="wa-preview-panel">
                        <div class="wa-preview-head">
                            <span class="wa-avatar"><i class="fab fa-whatsapp"></i></span>
                            <span>
                                <strong>Tigre Teve</strong>
                                <small>Vista previa del mensaje</small>
                            </span>
                        </div>

                        <div class="wa-preview-chat">
                            <div class="wa-placeholder" id="previewPlaceholder">
                                Completa el título, la imagen y el cuerpo para ver aquí el mensaje tal como llegará.
                            </div>
                            <div class="wa-bubble" id="previewBubble" style="display:none;">
                                <img class="wa-bubble-media" id="previewImage" alt="" style="display:none;">
                                <p class="wa-bubble-title" id="previewTitle"></p>
                                <p class="wa-bubble-body" id="previewBody"></p>
                                <span class="wa-bubble-cta">Ver más</span>
                                <span class="wa-bubble-meta">ahora <i class="fas fa-check-double"></i></span>
                            </div>
                        </div>

                        <div class="wa-preview-audience">
                            <div class="wa-audience-row">
                                <span class="label">Lista guardada</span>
                                <span class="value" id="audienceDb">0</span>
                            </div>
                            <div class="wa-audience-row">
                                <span class="label">Archivo adjunto</span>
                                <span class="value" id="audienceCsv">0</span>
                            </div>
                            <div class="wa-audience-total">
                                <span>Total de destinatarios</span>
                                <span class="value" id="audienceTotal">0</span>
                            </div>
                        </div>

                        <div class="wa-preview-warn" id="zeroWarning">
                            No hay destinatarios. Activa la lista guardada o sube un archivo CSV.
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="wa-loading" id="loadingOverlay" aria-hidden="true">
        <div class="wa-spinner"></div>
        <p>Enviando mensajes…</p>
        <small>No cierres ni recargues la página mientras se encola el envío.</small>
    </div>

    <div class="wa-modal" id="confirmModal" aria-hidden="true">
        <div class="wa-modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
            <div class="wa-preview-chat" style="min-height:auto;">
                <div class="wa-bubble">
                    <img class="wa-bubble-media" id="confirmImage" alt="" style="display:none;">
                    <p class="wa-bubble-title" id="confirmTitleText"></p>
                    <p class="wa-bubble-body" id="confirmBody"></p>
                    <span class="wa-bubble-cta">Ver más</span>
                    <span class="wa-bubble-meta">ahora <i class="fas fa-check-double"></i></span>
                </div>
            </div>
            <div class="wa-modal-panel">
                <h3 id="confirmTitle">Confirmar envío</h3>
                <p class="wa-modal-sub">Este mensaje llegará a <strong id="confirmTotal">0</strong> número(s).</p>
                <button type="button" class="wa-btn-confirm" id="confirmSend">
                    <i class="fab fa-whatsapp"></i>
                    <span id="confirmSendText">Enviar mensajes</span>
                </button>
                <button type="button" class="wa-btn-cancel" id="cancelSend">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const dbCount = Number(@json($dbCount ?? 0));
            const nf = new Intl.NumberFormat('es-CO');

            const el = (id) => document.getElementById(id);

            const form = el('formMasivo');
            const sourceToggle = el('list_from_db');
            const fileInput = el('file');
            const fileDrop = el('fileDrop');
            const fileLabel = el('fileLabel');
            const fileHint = el('fileHint');

            const inputTitulo = el('titulo');
            const inputImage = el('image');
            const inputCuerpo = el('cuerpo');

            const previewPlaceholder = el('previewPlaceholder');
            const previewBubble = el('previewBubble');
            const previewImage = el('previewImage');
            const previewTitle = el('previewTitle');
            const previewBody = el('previewBody');

            const audienceDb = el('audienceDb');
            const audienceCsv = el('audienceCsv');
            const audienceTotal = el('audienceTotal');
            const zeroWarning = el('zeroWarning');

            let csvCount = 0;

            function currentTotal() {
                const db = sourceToggle.checked ? dbCount : 0;
                return db + csvCount;
            }

            function currentDb() {
                return sourceToggle.checked ? dbCount : 0;
            }

            function refreshAudience() {
                audienceDb.textContent = nf.format(currentDb());
                audienceCsv.textContent = nf.format(csvCount);
                audienceTotal.textContent = nf.format(currentTotal());

                const total = currentTotal();
                zeroWarning.classList.toggle('is-visible', total === 0);
                el('sendButtonText').textContent = total > 0 ? `Enviar a ${nf.format(total)}` : 'Enviar mensajes';
            }

            function refreshPreview() {
                const titulo = inputTitulo.value.trim();
                const cuerpo = inputCuerpo.value.replace(/\s+/g, ' ').trim();
                const imageUrl = inputImage.value.trim();

                const hasContent = titulo !== '' || cuerpo !== '' || imageUrl !== '';

                if (!hasContent) {
                    previewPlaceholder.style.display = 'block';
                    previewBubble.style.display = 'none';
                } else {
                    previewPlaceholder.style.display = 'none';
                    previewBubble.style.display = 'block';
                }

                previewTitle.textContent = titulo;
                previewBody.textContent = cuerpo;

                if (imageUrl !== '') {
                    previewImage.src = imageUrl;
                    previewImage.style.display = 'block';
                } else {
                    previewImage.removeAttribute('src');
                    previewImage.style.display = 'none';
                }
            }

            // Dropzone
            fileInput.addEventListener('change', function () {
                csvCount = 0;

                if (this.files[0]) {
                    fileLabel.textContent = this.files[0].name;
                    fileHint.textContent = 'Contando números…';

                    const reader = new FileReader();
                    reader.onload = function (ev) {
                        const lines = String(ev.target.result).split(/\r?\n/).filter((l) => l.trim() !== '');
                        csvCount = Math.max(0, lines.length - 1);
                        fileHint.textContent = `${nf.format(csvCount)} número(s) estimado(s)`;
                        refreshAudience();
                    };
                    reader.readAsText(this.files[0]);
                } else {
                    fileLabel.textContent = 'Sube un archivo CSV';
                    fileHint.textContent = 'Una columna «teléfono» o varias; se limpian los repetidos.';
                }

                refreshAudience();
            });

            ['dragenter', 'dragover'].forEach((evt) => {
                fileDrop.addEventListener(evt, (e) => {
                    e.preventDefault();
                    fileDrop.classList.add('is-drag');
                });
            });

            ['dragleave', 'drop'].forEach((evt) => {
                fileDrop.addEventListener(evt, () => fileDrop.classList.remove('is-drag'));
            });

            // Contador de caracteres del cuerpo
            inputCuerpo.addEventListener('input', function () {
                const max = 1000;
                const count = this.value.length;
                const counter = el('charCounter');

                counter.textContent = `${nf.format(count)} / ${nf.format(max)} caracteres`;
                counter.classList.toggle('is-warn', count > max * 0.9 && count <= max);
                counter.classList.toggle('is-over', count > max);
                this.classList.toggle('is-invalid', count > max);

                refreshPreview();
            });

            inputTitulo.addEventListener('input', refreshPreview);
            inputImage.addEventListener('input', refreshPreview);
            previewImage.addEventListener('error', function () {
                this.style.display = 'none';
            });

            sourceToggle.addEventListener('change', refreshAudience);

            // Confirmación antes de enviar
            const loadingOverlay = el('loadingOverlay');
            const confirmModal = el('confirmModal');
            let confirmed = false;

            form.addEventListener('submit', function (e) {
                if (!confirmed) {
                    e.preventDefault();

                    const total = currentTotal();

                    el('confirmTitleText').textContent = inputTitulo.value.trim();
                    el('confirmBody').textContent = inputCuerpo.value.replace(/\s+/g, ' ').trim();

                    const confirmImage = el('confirmImage');
                    const imageUrl = inputImage.value.trim();
                    if (imageUrl !== '') {
                        confirmImage.src = imageUrl;
                        confirmImage.style.display = 'block';
                    } else {
                        confirmImage.removeAttribute('src');
                        confirmImage.style.display = 'none';
                    }

                    el('confirmTotal').textContent = nf.format(total);
                    el('confirmSendText').textContent = total > 0 ? `Enviar a ${nf.format(total)}` : 'Enviar mensajes';

                    confirmModal.classList.add('is-open');
                    confirmModal.setAttribute('aria-hidden', 'false');
                    el('confirmSend').focus();
                    return;
                }

                // Sin saltos de línea (regla de la plantilla de WhatsApp)
                inputCuerpo.value = inputCuerpo.value.replace(/\s*\n+\s*/g, ' ').replace(/[ \t]{2,}/g, ' ').trim();

                closeConfirm();

                loadingOverlay.classList.add('is-active');
                loadingOverlay.setAttribute('aria-hidden', 'false');

                const btn = el('sendButton');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando…';
            });

            function closeConfirm() {
                confirmed = false;
                confirmModal.classList.remove('is-open');
                confirmModal.setAttribute('aria-hidden', 'true');
            }

            el('confirmSend').addEventListener('click', function () {
                confirmed = true;
                form.requestSubmit();
            });

            el('cancelSend').addEventListener('click', closeConfirm);

            confirmModal.addEventListener('click', function (e) {
                if (e.target === confirmModal) closeConfirm();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && confirmModal.classList.contains('is-open')) closeConfirm();
            });

            window.addEventListener('pageshow', function () {
                loadingOverlay.classList.remove('is-active');
                loadingOverlay.setAttribute('aria-hidden', 'true');
            });

            refreshAudience();
            refreshPreview();
        })();
    </script>
</x-app-layout>
