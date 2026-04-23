<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="apple-mobile-web-app-status-bar" content="#01d679">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>@yield('title', 'Tigre teve')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="{{ asset('js/responsive.js') }}"></script>
    @livewireStyles
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#000000">

    <link href="/icons/icon-192.png" sizes="192x192" rel="apple-touch-startup-image">
    <link href="/icons/icon-512.png" sizes="512x512" rel="apple-touch-startup-image">
 @vite(['resources/js/app.js'])

</head>
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js')
    .then(() => console.log('SW registrado'));
}
</script>
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
