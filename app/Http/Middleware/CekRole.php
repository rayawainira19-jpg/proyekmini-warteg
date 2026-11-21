<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi akses halaman berdasarkan peran (FSD F-01).
 * Pemakaian pada route: ->middleware('role:pemilik') atau 'role:kasir,pemilik'.
 * Pengguna yang belum login diarahkan ke halaman login; peran yang tidak
 * diizinkan dialihkan ke halaman utamanya dengan pesan penolakan (US-09, TC-07).
 */
class CekRole
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $peran, true)) {
            // US-09: akses ditolak dan dialihkan ke halaman utama sesuai peran.
            return redirect(AuthController::beranda($user))
                ->with('galat', 'Akses ditolak: Hak akses tidak memadai');
        }

        return $next($request);
    }
}
