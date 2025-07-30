@php use Illuminate\Support\Facades\Auth; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $title ?? 'Dashboard Admin' }} - Pemesanan Lapangan Tenis</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    @stack('head')
</head>
<body>
    @include('admin.layouts.sidebar')
    <main>
        <header>
            <h1>Dashboard Admin</h1>
            <div class="admin-profile">
                <span>{{ Auth::user()->name ?? 'Admin' }}</span>
                <img src="https://img.icons8.com/color/36/administrator-male.png" alt="Admin"/>
            </div>
        </header>
        <section class="cards">
            @yield('content')
        </section>
        @include('admin.layouts.footer')
    </main>
    <script src="{{ asset('js/admin-dashboard.js') }}"></script>
    @stack('scripts')
</body>
</html>