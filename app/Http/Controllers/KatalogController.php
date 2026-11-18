<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KatalogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $pencarian = trim($validated['q'] ?? '');
        $query = Produk::with('kategori');

        if ($pencarian !== '') {
            $query->where('nama', 'like', '%'.$pencarian.'%');
        }

        $produk = $query->orderBy('nama')->get();

        return view('katalog.index', compact('produk', 'pencarian'));
    }
}
