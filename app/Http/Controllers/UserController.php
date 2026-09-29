<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('q'), fn ($query) =>
                $query->where(fn ($sub) =>
                    $sub->where('name', 'like', '%' . $request->q . '%')
                        ->orWhere('email', 'like', '%' . $request->q . '%')
                        ->orWhere('nim_nip', 'like', '%' . $request->q . '%')
                ))
            ->when($request->filled('role'), fn ($query) =>
                $query->where('role', $request->role))
            ->latest()
            ->paginate(15)
            ->withQueryString(); // filter tetap bertahan saat berpindah halaman

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // role sengaja TIDAK lewat $fillable — diisi eksplisit di sini
        // untuk mencegah mass assignment (konsep minggu 3)
        $user = new User($validated);
        $user->password = Hash::make($validated['password']);
        $user->role = $validated['role'];
        $user->save();

        return redirect()->route('admin.users.show', $user)
            ->with('status', 'Pengguna berhasil dibuat.');
    }

    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->fill([
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'nim_nip' => $validated['nim_nip'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // role di-set eksplisit, bukan lewat fill() massal
        $user->role = $validated['role'];
        $user->save();

        return redirect()->route('admin.users.show', $user)
            ->with('status', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', 'Pengguna berhasil dihapus.');
    }
}
