<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    use ApiResponse;

    public function __construct(private TransactionService $service) {}

    /**
     * List transaksi dengan filter & pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::forUser($request->user()->id)
            ->with(['account:id,name,color,icon', 'category:id,name,color,icon'])
            ->latest('date')->latest('created_at');

        // Filter
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        }
        if ($request->filled('search')) {
            $query->where('note', 'like', '%' . $request->search . '%');
        }

        $perPage      = min($request->get('per_page', 20), 100);
        $transactions = $query->paginate($perPage);

        return $this->paginated($transactions, TransactionResource::class);
    }

    /**
     * Detail satu transaksi.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $transaction = Transaction::forUser($request->user()->id)
            ->with(['account', 'category', 'transferPair.account'])
            ->find($id);

        if (! $transaction) return $this->notFound();

        return $this->success(new TransactionResource($transaction));
    }

    /**
     * Tambah transaksi baru.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_id'  => [
                'required', 'uuid',
                Rule::exists('accounts', 'id')->where('user_id', $request->user()->id),
            ],
            'category_id' => [
                'nullable', 'uuid',
                Rule::exists('categories', 'id')->where(fn($q) =>
                    $q->whereNull('user_id')->orWhere('user_id', $request->user()->id)
                ),
            ],
            'type'   => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:1'],
            'date'   => ['required', 'date', 'before_or_equal:today'],
            'note'   => ['nullable', 'string', 'max:500'],
            'tags'   => ['nullable', 'array'],
            'tags.*' => ['string', 'max:30'],
        ]);

        $transaction = $this->service->create($data);
        $transaction->load(['account:id,name,color,icon', 'category:id,name,color,icon']);

        return $this->created(new TransactionResource($transaction), 'Transaksi berhasil ditambahkan.');
    }

    /**
     * Update transaksi.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $transaction = Transaction::forUser($request->user()->id)->find($id);
        if (! $transaction) return $this->notFound();

        $data = $request->validate([
            'account_id'  => [
                'sometimes', 'uuid',
                Rule::exists('accounts', 'id')->where('user_id', $request->user()->id),
            ],
            'category_id' => [
                'nullable', 'uuid',
                Rule::exists('categories', 'id')->where(fn($q) =>
                    $q->whereNull('user_id')->orWhere('user_id', $request->user()->id)
                ),
            ],
            'type'   => ['sometimes', 'in:income,expense'],
            'amount' => ['sometimes', 'numeric', 'min:1'],
            'date'   => ['sometimes', 'date', 'before_or_equal:today'],
            'note'   => ['nullable', 'string', 'max:500'],
            'tags'   => ['nullable', 'array'],
        ]);

        $transaction = $this->service->update($transaction, $data);
        $transaction->load(['account:id,name,color,icon', 'category:id,name,color,icon']);

        return $this->success(new TransactionResource($transaction), 'Transaksi berhasil diperbarui.');
    }

    /**
     * Hapus transaksi.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $transaction = Transaction::forUser($request->user()->id)->find($id);
        if (! $transaction) return $this->notFound();

        $this->service->delete($transaction);

        return $this->success(null, 'Transaksi berhasil dihapus.');
    }

    /**
     * Transfer antar rekening.
     */
    public function transfer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from_account_id' => ['required', 'uuid', Rule::exists('accounts', 'id')->where('user_id', $request->user()->id)],
            'to_account_id'   => ['required', 'uuid', Rule::exists('accounts', 'id')->where('user_id', $request->user()->id), 'different:from_account_id'],
            'amount'          => ['required', 'numeric', 'min:1'],
            'date'            => ['required', 'date', 'before_or_equal:today'],
            'note'            => ['nullable', 'string', 'max:255'],
        ]);

        $from = \App\Models\Account::find($data['from_account_id']);
        $to   = \App\Models\Account::find($data['to_account_id']);

        if ($from->balance < $data['amount']) {
            return $this->error('Saldo rekening asal tidak mencukupi.', 422);
        }

        [$out, $in] = $this->service->createTransfer($from, $to, (float) $data['amount'], $data['date'], $data['note'] ?? null);
        $out->load('account:id,name');
        $in->load('account:id,name');

        return $this->created([
            'outgoing' => new TransactionResource($out),
            'incoming' => new TransactionResource($in),
        ], 'Transfer berhasil.');
    }

    /**
     * Ringkasan bulanan.
     */
    public function summary(Request $request): JsonResponse
    {
        $month   = (int) $request->get('month', now()->month);
        $year    = (int) $request->get('year',  now()->year);
        $summary = $this->service->monthlySummary($request->user()->id, $month, $year);

        return $this->success([
            ...$summary,
            'by_category' => $this->service->expenseByCategory($request->user()->id, $month, $year),
        ]);
    }
}
