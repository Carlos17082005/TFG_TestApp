<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>@yield('title') - Sistema de Tests</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Favicon (icono de la web) -->
    <link rel="icon" type="image/ico" sizes="64x64" href="{{ asset('favicon.ico') }}">


    @stack('styles')
</head>
<body>
    @if(Auth::check())
        <x-header />
    @endif

    <main class="page-wrap">
        @yield('content')
    </main>
</body>
</html>