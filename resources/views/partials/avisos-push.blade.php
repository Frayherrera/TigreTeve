{{-- Invitación a recibir notificaciones push. Solo aparece si hay claves VAPID y el navegador lo soporta. --}}
@if (config('services.webpush.public_key'))
    <div id="avisoPush" class="ap-caja" role="dialog" aria-labelledby="apTitulo" aria-describedby="apTexto" hidden>
        <span class="ap-icono" aria-hidden="true"><i class="fa-solid fa-bell"></i></span>
        <div class="ap-cuerpo">
            <p id="apTitulo" class="ap-titulo">¿Te avisamos de las noticias importantes?</p>
            <p id="apTexto" class="ap-texto">Te llega una notificación cuando publiquemos algo de última hora. Puedes desactivarla cuando quieras.</p>
            <div class="ap-acciones">
                <button type="button" class="ap-si" data-ap="activar">Activar avisos</button>
                <button type="button" class="ap-no" data-ap="cerrar">Ahora no</button>
            </div>
        </div>
    </div>

    <style>
        .ap-caja {
            position: fixed;
            left: 16px;
            right: 16px;
            bottom: 16px;
            z-index: 1000;
            display: flex;
            gap: 14px;
            max-width: 420px;
            padding: 16px 18px;
            border-radius: 14px;
            background: #1a1a2e;
            color: #fff;
            box-shadow: 0 12px 32px rgba(26, 26, 46, 0.35);
            border-left: 4px solid #f5ad1d;
            font-family: 'Source Sans 3', sans-serif;
            animation: apEntrada 0.35s ease-out;
        }

        .ap-caja[hidden] {
            display: none;
        }

        @media (min-width: 640px) {
            .ap-caja {
                right: auto;
                left: 24px;
                bottom: 24px;
            }
        }

        @keyframes apEntrada {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (prefers-reduced-motion: reduce) {
            .ap-caja { animation: none; }
        }

        .ap-icono {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 9999px;
            background: linear-gradient(135deg, #f5ad1d, #e74c3c);
        }

        .ap-titulo {
            margin: 0 0 4px;
            font-family: 'Bitter', serif;
            font-weight: 700;
            font-size: 1rem;
            line-height: 1.3;
        }

        .ap-texto {
            margin: 0 0 12px;
            font-size: 0.875rem;
            line-height: 1.45;
            color: #cbd5e1;
        }

        .ap-acciones {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .ap-si,
        .ap-no {
            border: 0;
            border-radius: 9999px;
            padding: 8px 16px;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
        }

        .ap-si {
            background: #f5ad1d;
            color: #1a1a2e;
        }

        .ap-si:hover {
            background: #ffbe3d;
        }

        .ap-no {
            background: transparent;
            color: #cbd5e1;
        }

        .ap-no:hover {
            color: #fff;
        }

        .ap-si:focus-visible,
        .ap-no:focus-visible {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
    </style>

    <script>
        (function () {
            var CLAVE_PUBLICA = @json(config('services.webpush.public_key'));
            var URL_SUSCRIBIR = @json(route('push.suscribir'));
            var DESCARTE = 'avisoPushDescartado';
            var caja = document.getElementById('avisoPush');

            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) return;

            function leer(clave) { try { return localStorage.getItem(clave); } catch (e) { return null; } }
            function guardar(clave, valor) { try { localStorage.setItem(clave, valor); } catch (e) {} }

            function claveABytes(base64) {
                var relleno = '='.repeat((4 - base64.length % 4) % 4);
                var b64 = (base64 + relleno).replace(/-/g, '+').replace(/_/g, '/');
                var crudo = atob(b64);
                var bytes = new Uint8Array(crudo.length);
                for (var i = 0; i < crudo.length; i++) bytes[i] = crudo.charCodeAt(i);
                return bytes;
            }

            function enviarAlServidor(suscripcion) {
                return fetch(URL_SUSCRIBIR, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(suscripcion.toJSON())
                });
            }

            function suscribir() {
                return navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .then(function () { return navigator.serviceWorker.ready; })
                    .then(function (reg) {
                        return reg.pushManager.getSubscription().then(function (actual) {
                            return actual || reg.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: claveABytes(CLAVE_PUBLICA)
                            });
                        });
                    })
                    .then(enviarAlServidor);
            }

            // Ya aceptó antes: se re-sincroniza en silencio (por si la suscripción cambió).
            if (Notification.permission === 'granted') {
                suscribir().catch(function () {});
                return;
            }
            if (Notification.permission === 'denied') return;

            var descartado = parseInt(leer(DESCARTE) || '0', 10);
            if (descartado && Date.now() - descartado < 14 * 24 * 3600 * 1000) return;

            setTimeout(function () { caja.hidden = false; }, 8000);

            caja.addEventListener('click', function (e) {
                var accion = e.target.getAttribute('data-ap');
                if (accion === 'cerrar') {
                    guardar(DESCARTE, String(Date.now()));
                    caja.hidden = true;
                }
                if (accion === 'activar') {
                    Notification.requestPermission().then(function (permiso) {
                        caja.hidden = true;
                        if (permiso === 'granted') {
                            suscribir().catch(function () {});
                        } else {
                            guardar(DESCARTE, String(Date.now()));
                        }
                    });
                }
            });
        })();
    </script>
@endif
