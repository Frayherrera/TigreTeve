<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="apple-mobile-web-app-status-bar" content="#01d679">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>@yield('title', 'Tigre teve')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bitter:ital,wght@0,400;0,600;0,700;0,800;1,400&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">

    @livewireStyles
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1a1a2e">

    <link href="/build/assets/icon-192-DMXoe3Dq.png" sizes="192x192" rel="apple-touch-startup-image">
    <link href="/build/assets/icon-192-DMXoe3Dq.png" sizes="512x512" rel="apple-touch-startup-image">
    @vite(['resources/css/app.css', 'resources/css/home.css', 'resources/js/app.js'])

</head>
<body>
    <div class="header">
        <div class="container">
            @include('partials.nav', ['categorias' => $categorias])
        </div>
    </div>
    <main>
        @yield('content')
    </main>

    @include('partials.footer')
    @livewireScripts

</body>

</html>
