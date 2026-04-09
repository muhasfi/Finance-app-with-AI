<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\RecurringPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringPlanController extends Controller
{
    public function index(): View
    {
        $plans = RecurringPlan::whereHas('account', fn($q) => $q->where('user_id', auth()->id()))
            ->with(['account:id,name,color,icon', 'category:id,name,color'])
            ->orderBy('is_active', 'desc')
            ->orderBy('next_run_at')
            ->get();

        return view('recurring.index', compact('plans'));
    }

    public function create(): View
    {
        $accounts   = auth()->user()->activeAccounts()->get(['id', 'name', 'type', 'color', 'icon']);
        $categories = Category::forUser(auth()->id())->parentsOnly()->with('children')->orderBy('sort_order')->get();

        return view('recurring.create', compact('accounts', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account_id'  => ['required', 'uuid', 'exists:accounts,id'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:100'],
            'type'        => ['required', 'in:income,expense'],
            'amount'      => ['required', 'numeric', 'min:1'],
            'frequency'   => ['required', 'in:daily,weekly,monthly,yearly'],
            'start_date'  => ['required', 'date'],
            'ends_at'     => ['nullable', 'date', 'after:start_date'],
            'note'        => ['nullable', 'string', 'max:255'],
        ]);

        $data['next_run_at'] = $data['start_date'];
        $data['is_active']   = true;

        RecurringPlan::create($data);

        return redirect()->route('recurring.index')
            ->with('success', 'Rencana transaksi berulang berhasil ditambahkan.');
    }

    public function edit(RecurringPlan $recurring): View
    {
        abort_if($recurring->account->user_id !== auth()->id(), 403);

        $accounts   = auth()->user()->activeAccounts()->get(['id', 'name', 'type', 'color', 'icon']);
        $categories = Category::forUser(auth()->id())->parentsOnly()->with('children')->orderBy('sort_order')->get();

        return view('recurring.edit', compact('recurring', 'accounts', 'categories'));
    }

    public function update(Request $request, RecurringPlan $recurring): RedirectResponse
    {
        abort_if($recurring->account->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'account_id'  => ['required', 'uuid', 'exists:accounts,id'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:100'],
            'type'        => ['required', 'in:income,expense'],
            'amount'      => ['required', 'numeric', 'min:1'],
            'frequency'   => ['required', 'in:daily,weekly,monthly,yearly'],
            'ends_at'     => ['nullable', 'date'],
            'note'        => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ]);

        $recurring->update($data);

        return redirect()->route('recurring.index')
            ->with('success', 'Rencana berhasil diperbarui.');
    }

    public function destroy(RecurringPlan $recurring): RedirectResponse
    {
        abort_if($recurring->account->user_id !== auth()->id(), 403);

        $recurring->delete();

        return redirect()->route('recurring.index')
            ->with('success', 'Rencana berhasil dihapus.');
    }

    /**
     * Toggle aktif / nonaktif tanpa hapus.
     */
    public function toggle(RecurringPlan $recurring): RedirectResponse
    {
        abort_if($recurring->account->user_id !== auth()->id(), 403);

        $recurring->update(['is_active' => ! $recurring->is_active]);

        $status = $recurring->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Rencana \"{$recurring->name}\" berhasil {$status}.");
    }
}
