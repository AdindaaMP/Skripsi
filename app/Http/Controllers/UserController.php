<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\TrainingGroup;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $searchTerm = $request->input('query');
        $groupId = $request->input('group_id');

        // Ambil ID semua user yang sudah ada di kelompok ini
        $existingUserIds = [];
        if ($groupId) {
            $group = TrainingGroup::find($groupId);
            if ($group) {
                $existingUserIds = $group->users()->pluck('users.id')->toArray();
            }
        }

        if (empty($searchTerm)) {
            return response()->json([]);
        }

        // Cari user berdasarkan nama, email, atau nim,
        // yang BUKAN admin, dan BELUM ada di kelompok ini.
        $users = User::where('role', 'user')
                    ->whereNotIn('id', $existingUserIds)
                    ->where(function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%{$searchTerm}%")
                              ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                              ->orWhere('nim', 'LIKE', "%{$searchTerm}%");
                    })
                    ->take(10) // Batasi hasil pencarian agar tidak terlalu banyak
                    ->get(['id', 'name', 'email']);

        return response()->json($users);
    }
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15);

        return view('admin.user.index', compact('users'));
    }

    /**
     * Menampilkan form untuk membuat pengguna baru.
     */
    public function create()
    {
        return view('admin.user.create');
    }

    /**
     * Menyimpan pengguna baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:admin,trainer,proctor,user'],
            'nim' => ['nullable', 'string', 'max:255'],
            'jurusan' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        // Validasi kombinasi email+role unik
        if (User::where('email', $request->email)->where('role', $request->role)->exists()) {
            return back()->withErrors(['error' => 'User dengan email dan role ini sudah ada.'])->withInput();
        }
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'nim' => $request->nim,
            'jurusan' => $request->jurusan,
            'avatar' => $request->avatar,
            'password' => $request->password, // Sudah otomatis di-hash oleh mutator
        ]);
        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit pengguna.
     */
    public function edit(User $user)
    {
        return view('admin.user.edit', compact('user'));
    }

    /**
     * Memperbarui data pengguna di database.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:admin,trainer,proctor,user'],
            'nim' => ['nullable', 'string', 'max:255'],
            'jurusan' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'string', 'max:255'],
        ]);
        // Validasi kombinasi email+role unik (kecuali user ini sendiri)
        if (User::where('email', $request->email)->where('role', $request->role)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['error' => 'User dengan email dan role ini sudah ada.'])->withInput();
        }
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'nim' => $request->nim,
            'jurusan' => $request->jurusan,
            'avatar' => $request->avatar,
        ]);
        return redirect()->route('admin.user.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Menghapus pengguna dari database.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
