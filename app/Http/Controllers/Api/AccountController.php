<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $accounts = $request->user()->accounts()
            ->when(
                $request->boolean('active_only', false),
                fn($q) => $q->where('is_active', true)
            )
            ->get();

        return $this->success(AccountResource::collection($accounts));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'type'         => ['required', 'in:cash,bank,ewallet,credit,savings'],
            'balance'      => ['required', 'numeric', 'min:0'],
            'currency'     => ['nullable', 'string', 'size:3'],
            'color'        => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon'         => ['nullable', 'string', 'max:50'],
            'description'  => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'due_date_day' => ['nullable', 'integer', 'min:1', 'max:31'],
        ]);

        $data['currency'] = $data['currency'] ?? $request->user()->currency;
        $account = $request->user()->accounts()->create($data);

        return $this->created(new AccountResource($account), 'Rekening berhasil ditambahkan.');
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $account = Account::where('user_id', $request->user()->id)->find($id);
        if (! $account) return $this->notFound();

        return $this->success(new AccountResource($account));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $account = Account::where('user_id', $request->user()->id)->find($id);
        if (! $account) return $this->notFound();

        $data = $request->validate([
            'name'         => ['sometimes', 'string', 'max:100'],
            'type'         => ['sometimes', 'in:cash,bank,ewallet,credit,savings'],
            'color'        => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon'         => ['nullable', 'string', 'max:50'],
            'description'  => ['nullable', 'string', 'max:255'],
            'is_active'    => ['sometimes', 'boolean'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'due_date_day' => ['nullable', 'integer', 'min:1', 'max:31'],
        ]);

        $account->update($data);

        return $this->success(new AccountResource($account->fresh()), 'Rekening berhasil diperbarui.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $account = Account::where('user_id', $request->user()->id)->find($id);
        if (! $account) return $this->notFound();

        if ($account->transactions()->exists()) {
            return $this->error('Tidak dapat menghapus rekening yang masih memiliki transaksi.', 422);
        }

        $account->delete();

        return $this->success(null, 'Rekening berhasil dihapus.');
    }

    /**
     * Total saldo semua rekening.
     */
    public function totalBalance(Request $request): JsonResponse
    {
        $total    = $request->user()->activeAccounts()->sum('balance');
        $accounts = $request->user()->activeAccounts()->get(['id', 'name', 'balance', 'currency', 'color', 'icon', 'type']);

        return $this->success([
            'total'    => (float) $total,
            'accounts' => AccountResource::collection($accounts),
        ]);
    }
}
