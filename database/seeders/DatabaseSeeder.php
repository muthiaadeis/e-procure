<?php

namespace Database\Seeders;

use App\Models\ApprovalSlotHolder;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $suparlan = User::firstOrCreate(
            ['email' => 'suparlan@prkkemari.com'],
            [
                'name' => 'Suparlan',
                'jabatan' => 'Site Manager',
                'password' => Hash::make('password'),
                'is_approver' => true,
                'role' => 'approver',
            ]
        );

        $dedi = User::firstOrCreate(
            ['email' => 'dedinovendri@prkkemari.com'],
            [
                'name' => 'Dedi Novendri',
                'jabatan' => 'Project Director',
                'password' => Hash::make('password'),
                'is_approver' => true,
                'role' => 'approver',
            ]
        );

        $mila = User::firstOrCreate(
            ['email' => 'mila@prkkemari.com'],
            [
                'name' => 'Nurmila Sari M',
                'jabatan' => 'Finance & Accounting',
                'password' => Hash::make('password'),
                'is_approver' => true,
                'role' => 'approver',
            ]
        );

        User::firstOrCreate(
            ['email' => 'ulfa@prkkemari.com'],
            [
                'name' => 'Ulfa Gustianti',
                'jabatan' => 'Procurement Officer',
                'password' => Hash::make('password'),
                'is_approver' => false,
                'role' => 'input',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@prkkemari.com'],
            [
                'name' => 'Admin',
                'jabatan' => 'System Administrator',
                'password' => Hash::make('password'),
                'is_approver' => false,
                'role' => 'admin',
                'is_admin' => true,
            ]
        );

        // Posisi awal MR (bisa diubah administrator lewat halaman Approval Positions)
        ApprovalSlotHolder::firstOrCreate(['role_label' => 'MR Approval 1', 'user_id' => $suparlan->id]);
        ApprovalSlotHolder::firstOrCreate(['role_label' => 'MR Approval 2', 'user_id' => $dedi->id]);
        ApprovalSlotHolder::firstOrCreate(['role_label' => 'MR Finance', 'user_id' => $mila->id]);
    }
}
