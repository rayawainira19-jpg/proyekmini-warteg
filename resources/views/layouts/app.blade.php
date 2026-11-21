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
        @auth
            @if (auth()->user()->adalahPemilik())
                <a href="{{ route('produk.index') }}">Produk</a>
            @else
                <a href="{{ route('produk.index') }}">Katalog</a>
            @endif
            <a href="{{ route('transaksi.create') }}">Transaksi</a>
            <a href="{{ route('kasbon.index') }}">Kasbon</a>
            @if (auth()->user()->adalahPemilik())
                <a href="{{ route('laporan.index') }}">Laporan</a>
            @endif
            <span class="spacer"></span>
            <span class="user">{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</span>
            <form method="POST" action="{{ route('logout') }}" class="keluar">
                @csrf
                <button type="submit">Keluar</button>
            </form>
        @endauth
    </header>
    <main class="wrap">
        @if (session('sukses'))<p class="alert ok">&#10003; {{ session('sukses') }}</p>@endif
        @if (session('galat'))<p class="alert err">{{ session('galat') }}</p>@endif
        @yield('content')
    </main>
</body>
</html>
