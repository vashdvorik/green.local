<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) config('admin.email');
        $password = (string) config('admin.password');
        $name = (string) config('admin.name', 'Administrator');

        if ($email === '' || $password === '') {
            throw new RuntimeException(
                'ADMIN_EMAIL and ADMIN_PASSWORD must be set in the environment before seeding.'
            );
        }

        if (mb_strlen($password) < 12) {
            throw new RuntimeException('ADMIN_PASSWORD must contain at least 12 characters.');
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->email_verified_at ??= now();

        if (! $user->exists || ! Hash::check($password, (string) $user->getRawOriginal('password'))) {
            $user->password = $password;
        }

        $user->save();
    }
}
