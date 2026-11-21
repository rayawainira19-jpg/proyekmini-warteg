<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/** Dua akun contoh untuk F-01: Pemilik dan Kasir. Ubah kata sandi sebelum dipakai sungguhan. */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Pemilik Warteg', 'pemilik@warteg.test', 'pemilik123', 'pemilik'],
            ['Kasir Warteg',   'kasir@warteg.test',   'kasir123',   'kasir'],
        ] as [$name, $email, $password, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => $password, 'role' => $role],
            );
        }
    }
}
