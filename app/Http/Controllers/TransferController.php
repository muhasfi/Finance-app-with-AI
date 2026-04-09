<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function __construct(private TransactionService $service) {}

    public function create(): View
    {
        $accounts = auth()->user()->activeAccounts()->get(['id', 'name', 'type', 'color', 'icon', 'balance', 'currency']);

        return view('transfer.create', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_account_id' => ['required', 'uuid', 'exists:accounts,id'],
            'to_account_id'   => ['required', 'uuid', 'exists:accounts,id', 'different:from_account_id'],
            'amount'          => ['required', 'numeric', 'min:1'],
            'date'            => ['required', 'date', 'before_or_equal:today'],
            'note'            => ['nullable', 'string', 'max:255'],
        ]);

        $from = Account::findOrFail($data['from_account_id']);
        $to   = Account::findOrFail($data['to_account_id']);

        // Validasi kepemilikan
        abort_if($from->user_id !== auth()->id(), 403);
        abort_if($to->user_id   !== auth()->id(), 403);

        // Validasi saldo cukup
        if ($from->balance < $data['amount']) {
            return back()->withErrors(['amount' => 'Saldo rekening asal tidak mencukupi.'])->withInput();
        }

        $this->service->createTransfer(
            $from,
            $to,
            (float) $data['amount'],
            $data['date'],
            $data['note'] ?? null
        );

        return redirect()->route('transactions.index')
            ->with('success', 'Transfer berhasil dicatat.');
    }
}
