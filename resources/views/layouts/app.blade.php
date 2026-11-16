<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
    </script>
    <link rel="manifest" href="/manifest.json">

    <style>
        /* Panel: el menú lateral es fijo en escritorio y un cajón deslizable en pantallas < 1024px. */
        .adm-aside {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 60;
            width: 16rem;
            height: 100vh;
            height: 100dvh;
            transform: translateX(-100%);
            transition: transform 0.25s ease;
        }

        .adm-aside .sidebar {
            width: 100%;
            height: 100%;
        }

        .adm-fondo {
            position: fixed;
            inset: 0;
            z-index: 55;
            background: rgba(15, 23, 42, 0.5);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        .adm-cerrar {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            z-index: 1;
        }

        body.adm-menu-abierto {
            overflow: hidden;
        }

        body.adm-menu-abierto .adm-aside {
            transform: none;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.3);
        }

        body.adm-menu-abierto .adm-fondo {
            opacity: 1;
            pointer-events: auto;
        }

        .adm-main {
            min-width: 0;
        }

        @media (min-width: 1024px) {
            .adm-aside {
                transform: none;
                transition: none;
            }

            /* !important: la clase utilitaria "flex" del botón ganaría si no. */
            .adm-fondo,
            .adm-cerrar {
                display: none !important;
            }

            .adm-main {
                margin-left: 16rem;
            }

            body.adm-menu-abierto {
                overflow: auto;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .adm-aside,
            .adm-fondo {
                transition: none;
            }
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <div class="adm-fondo" data-adm-cerrar aria-hidden="true"></div>

        {{-- Menú lateral: fijo en escritorio, cajón en móvil/tablet --}}
        <aside class="adm-aside" id="adm-menu" aria-label="Menú del panel">
            <button type="button" data-adm-cerrar
                class="adm-cerrar flex h-9 w-9 items-center justify-center rounded-lg bg-white/90 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                aria-label="Cerrar menú">
                <i class="fas fa-xmark"></i>
            </button>
            @include('layouts.sidebar')
        </aside>

        <main class="adm-main pt-16">
            {{ $slot }}
        </main>
    </div>

    <script>
        (function () {
            var body = document.body;
            var boton = document.querySelector('[data-adm-abrir]');

            function cambiar(abrir) {
                body.classList.toggle('adm-menu-abierto', abrir);
                if (boton) boton.setAttribute('aria-expanded', abrir ? 'true' : 'false');
            }

            if (boton) boton.addEventListener('click', function () { cambiar(true); });
            document.querySelectorAll('[data-adm-cerrar]').forEach(function (el) {
                el.addEventListener('click', function () { cambiar(false); });
            });
            // Al elegir una opción del menú se cierra el cajón.
            document.querySelectorAll('#adm-menu a').forEach(function (a) {
                a.addEventListener('click', function () { cambiar(false); });
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') cambiar(false);
            });
        })();
    </script>
</body>

</html>
