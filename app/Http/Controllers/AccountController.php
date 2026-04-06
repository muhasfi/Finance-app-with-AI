<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = auth()->user()->accounts()->withTrashed()->latest()->get();

        return view('accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = AccountType::cases();

        return view('accounts.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'type'         => ['required', 'in:cash,bank,ewallet,credit,savings'],
            'balance'      => ['required', 'numeric', 'min:0'],
            'currency'     => ['required', 'string', 'size:3'],
            'color'        => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon'         => ['nullable', 'string', 'max:50'],
            'description'  => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'due_date_day' => ['nullable', 'integer', 'min:1', 'max:31'],
        ]);

        auth()->user()->accounts()->create($data);

        return redirect()->route('accounts.index')
            ->with('success', 'Rekening berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        $this->authorize('update', $account);
        $types = AccountType::cases();

        return view('accounts.edit', compact('account', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        $this->authorize('update', $account);

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'type'         => ['required', 'in:cash,bank,ewallet,credit,savings'],
            'currency'     => ['required', 'string', 'size:3'],
            'color'        => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon'         => ['nullable', 'string', 'max:50'],
            'description'  => ['nullable', 'string', 'max:255'],
            'is_active'    => ['boolean'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'due_date_day' => ['nullable', 'integer', 'min:1', 'max:31'],
        ]);

        $account->update($data);

        return redirect()->route('accounts.index')
            ->with('success', 'Rekening berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);
        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Rekening berhasil dihapus.');
    }
}
