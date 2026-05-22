<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- ★ FAVICON — cukup tulis sekali di sini ★ -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <title>@yield('title', 'LCI Suite')</title>

    <!-- ★ CSS global — cukup tulis sekali di sini ★ -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @yield('head')
</head>
<body>
    @yield('content')
    @yield('scripts')
</body>
</html>