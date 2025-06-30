<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/topnav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/topnav.js') }}"></script>
</head>
<body>
    <!-- Top Navigation -->
    @include('layouts.frontend.topnav')

    <!-- Sidebar -->
    @include('layouts.frontend.sidebar')

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay"></div>
</body>
</html>
