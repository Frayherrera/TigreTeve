<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Tigre teve')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @vite('resources/js/app.js')
    @livewireStyles

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
