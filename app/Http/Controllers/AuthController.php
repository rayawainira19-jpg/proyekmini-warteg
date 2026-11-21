<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** F-01: login dan logout untuk dua peran, Pemilik dan Kasir. */
class AuthController extends Controller
{
    /** Halaman awal setiap peran setelah login. */
    public static function beranda(User $user): string
    {
        return $user->adalahPemilik() ? route('produk.index') : route('transaksi.create');
    }

    public function form(): View
    {
        return view('auth.login');
    }

    public function masuk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (! Auth::attempt($data)) {
            return back()->withInput($request->only('email'))
                ->with('galat', 'Email atau kata sandi salah.');
        }

        $request->session()->regenerate();

        return redirect()->intended(self::beranda($request->user()));
    }

    public function keluar(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
