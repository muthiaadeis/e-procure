<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->get();

        // Summary stats
        $totalUsers = User::count();
        $totalAdmins = User::where('is_admin', true)->count();
        $totalApprovers = User::whereIn('role', ['approver_a', 'approver_c'])->count();
        $pendingPasswordChanges = User::where('must_change_password', true)->count();

        return view('admin.users.index', compact(
            'users',
            'search',
            'totalUsers',
            'totalAdmins',
            'totalApprovers',
            'pendingPasswordChanges'
        ));
    }

    public function create(): View
    {
        $suggestedPassword = $this->generateStrongPassword(12);

        return view('admin.users.create', compact('suggestedPassword'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'in:input,approver_a,approver_c,finance'],
            'is_admin' => ['nullable', 'boolean'],
            'password_type' => ['required', 'in:auto,manual'],
            'password' => ['nullable', 'required_if:password_type,manual', 'string', 'min:6'],
            'must_change_password' => ['nullable', 'boolean'],
        ]);

        if ($request->password_type === 'manual' && filled($request->password)) {
            $plainPassword = $request->password;
        } else {
            $plainPassword = $this->generateStrongPassword(12);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_approver' => in_array($validated['role'], ['approver_a', 'approver_c']),
            'is_admin' => $request->boolean('is_admin'),
            'password' => $plainPassword,
            'must_change_password' => $request->has('must_change_password') ? $request->boolean('must_change_password') : true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User account for {$user->name} created successfully!")
            ->with('generated_password', $plainPassword)
            ->with('generated_password_for', $user->name)
            ->with('generated_email_for', $user->email)
            ->with('is_new_user', true);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'string', 'in:input,approver_a,approver_c,finance'],
            'is_admin' => ['nullable', 'boolean'],
            'must_change_password' => ['nullable', 'boolean'],
        ]);

        $isAdmin = ($user->id === auth()->id()) ? true : $request->boolean('is_admin');

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_approver' => in_array($validated['role'], ['approver_a', 'approver_c']),
            'is_admin' => $isAdmin,
            'must_change_password' => $request->boolean('must_change_password'),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own administrator account.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$userName} has been removed successfully.");
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $newPassword = $this->generateStrongPassword();

        $user->update([
            'password' => $newPassword,
            'must_change_password' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "New password generated for {$user->name}.")
            ->with('generated_password', $newPassword)
            ->with('generated_password_for', $user->name)
            ->with('generated_email_for', $user->email)
            ->with('is_new_user', false);
    }

    private function generateStrongPassword(int $length = 12): string
    {
        $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower   = 'abcdefghijkmnpqrstuvwxyz';
        $numbers = '23456789';
        $symbols = '!@#$%^&*?';
        $all     = $upper . $lower . $numbers . $symbols;

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
