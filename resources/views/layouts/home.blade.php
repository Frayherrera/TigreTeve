<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="apple-mobile-web-app-status-bar" content="#01d679">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>@yield('title', 'Tigre teve')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @livewireStyles
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#000000">

    <link href="/build/assets/icon-192-DMXoe3Dq.png" sizes="192x192" rel="apple-touch-startup-image">
    <link href="/build/assets/icon-192-DMXoe3Dq.png" sizes="512x512" rel="apple-touch-startup-image">
    
    @vite(['resources/css/app.css', 'resources/css/home.css', 'resources/js/app.js'])

</head>
<body class="bg-gray-100 text-gray-900">
    <div class="header">
        <div class="container">
            @include('partials.nav', ['categorias' => $categorias])
        </div>
    </div>
    <main>
        @yield('content') <!-- Contenido dinámico -->
    </main>

    @include('partials.footer')
    @livewireScripts

</body>

</html>
