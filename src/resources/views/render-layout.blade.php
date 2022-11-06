<!DOCTYPE html>

<html>

<head>
    <link rel='stylesheet' href={{ asset('css/app.css') }} />
    <link rel='stylesheet' href={{ asset('css/render.css') }} />
    @yield('styles')
</head>

<body>
    @yield('content')
</body>

</html>
