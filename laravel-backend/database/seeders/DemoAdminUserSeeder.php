<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAdminUserSeeder extends Seeder
{
    /**
     * Demo-kasutaja juhendaja jaoks, et kogu API läbi käia.
     * Idempotentne: võib korduvalt käivitada.
     */
    public function run(): void
    {
        $email = 'demo@fototeek.ee';
        $password = 'Demo1234!';

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $user = new User;
            $user->email = $email;
        }

        $user->name = 'Demo Admin';
        $user->password = Hash::make($password);
        $user->forceFill(['is_admin' => true]);
        $user->save();
    }
}
