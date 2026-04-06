<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{

    public function __construct(private TransactionService $service) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Transaction::forUser(auth()->id())
            ->with(['account:id,name,color,icon', 'category:id,name,color,icon'])
            ->latest('date')->latest('created_at');

        // Filter bulan
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->month)->whereYear('date', $request->year);
        }

        // Filter tipe
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter akun
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $accounts     = auth()->user()->activeAccounts()->get(['id', 'name']);
        $categories   = Category::forUser(auth()->id())->parentsOnly()->get(['id', 'name']);

        return view('transactions.index', compact('transactions', 'accounts', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts   = auth()->user()->activeAccounts()->get(['id', 'name', 'type', 'color', 'icon', 'balance']);
        $categories = Category::forUser(auth()->id())->parentsOnly()->with('children')->orderBy('sort_order')->get();

        return view('transactions.create', compact('accounts', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        $this->service->create($request->validated(), $request->file('receipt'));

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        $transaction->load(['account', 'category', 'transferPair.account']);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        $accounts   = auth()->user()->activeAccounts()->get(['id', 'name', 'type', 'color', 'icon', 'balance']);
        $categories = Category::forUser(auth()->id())->parentsOnly()->with('children')->orderBy('sort_order')->get();

        return view('transactions.edit', compact('transaction', 'accounts', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
         $this->authorize('update', $transaction);
        $this->service->update($transaction, $request->validated(), $request->file('receipt'));

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);
        $this->service->delete($transaction);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }
}
