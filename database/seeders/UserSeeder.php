<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'superadmin@mftc.test',
                'full_name' => 'Super Admin',
                'role' => UserRole::SUPER_ADMIN,
                'phone' => '081200000001',
            ],
            [
                'email' => 'sales@mftc.test',
                'full_name' => 'Sales Officer',
                'role' => UserRole::SALES,
                'phone' => '081200000002',
            ],
            [
                'email' => 'auditor@mftc.test',
                'full_name' => 'Auditor',
                'role' => UserRole::AUDITOR,
                'phone' => '081200000003',
            ],
            [
                'email' => 'pu@mftc.test',
                'full_name' => 'Pelaku Usaha',
                'role' => UserRole::PU,
                'phone' => '081200000004',
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'full_name' => $user['full_name'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'phone' => $user['phone'],
                    'is_active' => true,
                ]
            );
        }
    }
}
