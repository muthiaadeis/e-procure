<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $newPassword = $this->generateStrongPassword();

        // Cukup assign plain text, karena User model sudah punya
        // 'password' => 'hashed' di casts() -> otomatis di-bcrypt.
        $user->update([
            'password' => $newPassword,
            'must_change_password' => true,
        ]);

        // Ditaruh di session flash: hanya muncul 1x di reload berikutnya, lalu hilang.
        return redirect()->route('admin.users.index')
            ->with('generated_password', $newPassword)
            ->with('generated_password_for', $user->name);
    }

    private function generateStrongPassword(int $length = 12): string
    {
        $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // tanpa I, O biar gak ketuker
        $lower   = 'abcdefghijkmnpqrstuvwxyz';
        $numbers = '23456789';
        $symbols = '!@#$%^&*?';
        $all     = $upper.$lower.$numbers.$symbols;

        // Pastikan minimal 1 karakter dari tiap kategori (huruf besar, kecil, angka, simbol).
        $password = [
            $upper[random_int(0, strlen($upper) - 1)],
            $lower[random_int(0, strlen($lower) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        for ($i = count($password); $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($password);

        return implode('', $password);
    }
}
