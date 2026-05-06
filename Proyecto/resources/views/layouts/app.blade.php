<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>@yield('title')</title>

    <!-- CSS de la Web  -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    
    @yield('content')
</body>
</html>