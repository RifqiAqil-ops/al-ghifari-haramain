<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $role = $request->input('role');

        $query = User::with('roles');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->whereHas('roles', fn($rq) => $rq->where('name', $role));
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('users.index', compact('users', 'roles', 'search', 'role'));
    }

    public function createAdmin(): View
    {
        return view('users.create-admin');
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.max' => 'Alamat email maksimal 255 karakter.',
            'email.unique' => 'Alamat email ini sudah terdaftar dalam sistem.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        try {
            $user = DB::transaction(function () use ($validated) {
                // 1. Verifikasi keberadaan role Admin pada sistem role yang sudah ada
                $adminRole = Role::where('name', 'Admin')->where('guard_name', 'web')->first()
                    ?? Role::whereRaw('LOWER(name) = ?', ['admin'])->first();

                if (!$adminRole) {
                    throw new \RuntimeException('Role Admin tidak ditemukan dalam konfigurasi sistem.');
                }

                // 2. Buat user baru dengan password terenkripsi dan status aktif
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                // 3. Tetapkan role Admin (jika gagal, DB transaction otomatis me-rollback pembuatan user)
                $user->assignRole($adminRole);

                return $user;
            });
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('toast_danger', 'Gagal membuat akun Admin. Silakan coba kembali atau hubungi administrator.');
        }

        // 4. Catat aktivitas audit secara aman tanpa mengekspos password
        try {
            ActivityLogger::log('CREATE_ADMIN', 'User', "Membuat akun Admin baru: {$user->name} ({$user->email})");
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('users.index')
            ->with('toast_success', "Akun Admin baru ({$user->name}) berhasil dibuat dan dapat langsung digunakan untuk masuk.");
    }

    public function create(): View
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'exists:roles,name'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Nama lengkap pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'role.required' => 'Hak akses (Role) wajib dipilih.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        ActivityLogger::log('CREATE_USER', 'User', "Membuat user baru: {$user->name} ({$user->email}) dengan Role: {$validated['role']}");

        return redirect()->route('users.index')
            ->with('toast_success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'exists:roles,name'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'is_active' => ['boolean'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->is_active = $request->boolean('is_active', true);

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        ActivityLogger::log('UPDATE_USER', 'User', "Memperbarui data user: {$user->name} ({$user->email})");

        return redirect()->route('users.index')
            ->with('toast_success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('toast_danger', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLogger::log('TOGGLE_USER_STATUS', 'User', "Akun pengguna {$user->name} {$statusStr}.");

        return back()->with('toast_success', "Status pengguna {$user->name} berhasil {$statusStr}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('toast_danger', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLogger::log('DELETE_USER', 'User', "Menghapus akun pengguna: {$name}");

        return redirect()->route('users.index')
            ->with('toast_success', "Pengguna {$name} berhasil dihapus.");
    }

    public function roles(): View
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy(fn($p) => explode('.', $p->name)[0]);

        return view('users.roles', compact('roles', 'permissions'));
    }

    public function updateRolePermissions(Request $request, Role $role): RedirectResponse
    {
        $perms = $request->input('permissions', []);
        $role->syncPermissions($perms);

        ActivityLogger::log('UPDATE_ROLE_PERMS', 'User', "Memperbarui izin akses untuk role {$role->name}");

        return back()->with('toast_success', "Hak akses role {$role->name} berhasil diperbarui.");
    }
}
