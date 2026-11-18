<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Warung Tegal Ende')</title>
    <link rel="stylesheet" href="{{ asset('css/kasir.css') }}">
</head>
<body>
    <header class="topbar">
        <strong>Warung Tegal Ende</strong>
        <a href="{{ route('produk.index') }}">Produk</a>
    </header>
    <main class="wrap">
        @if (session('sukses'))<p class="alert ok">&#10003; {{ session('sukses') }}</p>@endif
        @if (session('galat'))<p class="alert err">{{ session('galat') }}</p>@endif
        @if (session('gagal'))<p class="alert err">{{ session('gagal') }}</p>@endif
        @yield('content')
    </main>
</body>
</html>
