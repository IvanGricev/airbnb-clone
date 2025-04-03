<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Airbnb Clone')</title>
    

    <meta http-equiv="Content-Security-Policy" content="
        script-src 'self' 
                https://cdn.jsdelivr.net;
        style-src 'self' 
                'unsafe-inline' 
                https://cdn.jsdelivr.net 
                https://cdnjs.cloudflare.com;
    ">

    <link rel="stylesheet" href="{{ url('/css/app.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CDN FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
</head>
<body>
    @include('partials.navbar')
    <div class="container mt-4">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </div>
    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="{{ url('js/app.js') }}"></script>

</body>
</html>