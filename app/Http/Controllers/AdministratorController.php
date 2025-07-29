<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdministratorController extends Controller
{
    /**
     * Menampilkan daftar semua administrator.
     */
    public function index()
    {
        $administrators = User::where('role', 'admin')->get();
        return view('admin.administrator.index', compact('administrators'));
    }

    /**
     * Menampilkan form untuk menjadikan user sebagai administrator.
     */
    public function create()
    {
        $users = User::where('role', '!=', 'admin')->get();
        return view('admin.administrator.create', compact('users'));
    }

    /**
     * Menyimpan user yang dipilih sebagai administrator baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($validated['user_id']);
        $user->role = 'admin';
        $user->save();

        return redirect()->route('admin.administrator.index')->with('success', 'User berhasil dijadikan administrator.');
    }

    /**
     * Menampilkan form untuk mengedit bio data administrator.
     */
    public function edit(User $administrator)
    {
        return view('admin.administrator.edit', compact('administrator'));
    }

    /**
     * Memperbarui bio data administrator.
     */
    public function update(Request $request, User $administrator)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $administrator->id,
            'avatar' => 'nullable|string|max:255',
        ]);

        $administrator->update($validated);

        return redirect()->route('admin.administrator.index')->with('success', 'Data administrator berhasil diperbarui.');
    }

    /**
     * Menghapus seorang administrator (mengubah rolenya kembali menjadi user).
     */
    public function destroy(User $administrator)
    {
        $administrator->role = 'user';
        $administrator->save();

        return redirect()->route('admin.administrator.index')->with('success', 'Administrator berhasil dihapus (role diubah menjadi user).');
    }
}
