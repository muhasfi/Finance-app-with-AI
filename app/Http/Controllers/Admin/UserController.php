<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::withTrashed()
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            )
            ->when($request->role,   fn($q) => $q->where('role',   $request->role))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->loadCount('accounts');
        $totalTransactions = \App\Models\Transaction::forUser($user->id)->count();

        return view('admin.users.show', compact('user', 'totalTransactions'));
    }

    public function suspend(User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403, 'Tidak bisa suspend akun admin.');
        $user->update(['status' => 'suspended']);

        return back()->with('success', "Akun {$user->name} berhasil di-suspend.");
    }

    public function activate(User $user): RedirectResponse
    {
        $user->update(['status' => 'active']);

        return back()->with('success', "Akun {$user->name} berhasil diaktifkan.");
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa hapus akun sendiri.');
        abort_if($user->isAdmin(), 403, 'Tidak bisa hapus akun admin.');
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil dihapus.");
    }
}
