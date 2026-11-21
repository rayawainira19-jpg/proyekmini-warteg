<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProdukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->adalahPemilik() ?? false; // F-02: hanya Pemilik yang mengelola menu
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['aktif' => $this->boolean('aktif')]);
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'kode'        => ['required', 'string', 'max:20', Rule::unique('produk', 'kode')->ignore($id)],
            'nama'        => ['required', 'string', 'max:100', Rule::unique('produk', 'nama')->ignore($id)],
            'kategori_id' => ['required', 'integer', 'exists:kategori,id'],
            'harga'       => ['required', 'integer', 'min:0'],
            'stok'        => ['required', 'integer', 'min:0'],
            'aktif'       => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required'        => 'Kode produk wajib diisi.',
            'kode.max'             => 'Kode produk maksimal 20 karakter.',
            'kode.unique'          => 'Kode sudah digunakan.',
            'nama.required'        => 'Nama wajib diisi.',
            'nama.max'             => 'Nama maksimal 100 karakter.',
            'nama.unique'          => 'Nama produk sudah digunakan.',
            'kategori_id.required' => 'Kategori tidak valid.',
            'kategori_id.exists'   => 'Kategori tidak valid.',
            'kategori_id.integer'  => 'Kategori tidak valid.',
            'harga.required'       => 'Harga tidak valid.',
            'harga.integer'        => 'Harga tidak valid.',
            'harga.min'            => 'Harga tidak valid.',
            'stok.required'        => 'Stok tidak valid.',
            'stok.integer'         => 'Stok tidak valid.',
            'stok.min'             => 'Stok tidak valid.',
        ];
    }
}
