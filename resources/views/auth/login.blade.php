@extends('layouts.app')
@section('title', 'Masuk')

@section('content')
    <div class="kotak kiri login">
        <h1>Masuk</h1>
        <p class="sub">Sistem Kasir Warung Tegal Ende. Masuk sebagai Pemilik atau Kasir.</p>

        <form method="POST" action="{{ route('login.proses') }}">
            @csrf
            <div class="f">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autofocus>
                @error('email')<small class="e">{{ $message }}</small>@enderror
            </div>
            <div class="f" style="margin-top:1rem">
                <label for="password">Kata sandi</label>
                <input id="password" name="password" type="password">
                @error('password')<small class="e">{{ $message }}</small>@enderror
            </div>
            <div class="tombol">
                <button type="submit">Masuk</button>
            </div>
        </form>
    </div>
@endsection
