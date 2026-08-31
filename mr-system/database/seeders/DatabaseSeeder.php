<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'suparlan@prkkemari.com'],
            [
                'name' => 'Suparlan',
                'password' => Hash::make('password'),
                'is_approver' => true,
                'role' => 'approver_a',
            ]
        );

        User::firstOrCreate(
            ['email' => 'dedinovendri@prkkemari.com'],
            [
                'name' => 'Dedi Novendri',
                'password' => Hash::make('password'),
                'is_approver' => true,
                'role' => 'approver_c',
            ]
        );

        User::firstOrCreate(
            ['email' => 'mila@prkkemari.com'],
            [
                'name' => 'Nurmila Sari M',
                'password' => Hash::make('password'),
                'is_approver' => false,
                'role' => 'finance',
            ]
        );

        User::firstOrCreate(
            ['email' => 'ulfa@prkkemari.com'],
            [
                'name' => 'Ulfa Gustianti',
                'password' => Hash::make('password'),
                'is_approver' => false,
                'role' => 'input',
            ]
        );

        User::firstOrCreate(
            ['email' => 'rlp@prkkemari.com'],
            [
                'name' => 'User RLP',
                'password' => Hash::make('password'),
                'is_approver' => false,
                'role' => 'input',
            ]
        );
    }
}
