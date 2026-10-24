<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo') · MediTurno</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('assets/css/compartido/mediturno.css') }}">
</head>
<body class="pantalla-acceso">
    @yield('contenido')
    <script src="{{ url('assets/js/compartido/interfaz.js') }}"></script>
</body>
</html>
