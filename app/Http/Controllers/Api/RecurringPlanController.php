<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecurringPlanResource;
use App\Models\RecurringPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecurringPlanController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $plans = RecurringPlan::whereHas('account', fn($q) =>
                $q->where('user_id', $request->user()->id)
            )
            ->when(
                $request->has('is_active'),
                fn($q) => $q->where('is_active', $request->boolean('is_active'))
            )
            ->with(['account:id,name,color,icon', 'category:id,name,color,icon'])
            ->orderBy('next_run_at')
            ->get();

        return $this->success(RecurringPlanResource::collection($plans));
    }

    public function store(Request $request): JsonResponse
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

        // Validasi rekening milik user
        $account = \App\Models\Account::where('user_id', $request->user()->id)
            ->find($data['account_id']);
        if (! $account) return $this->forbidden('Rekening bukan milik Anda.');

        $plan = RecurringPlan::create([
            ...$data,
            'next_run_at' => $data['start_date'],
            'is_active'   => true,
        ]);

        $plan->load(['account:id,name', 'category:id,name,color,icon']);

        return $this->created(new RecurringPlanResource($plan), 'Rencana berhasil ditambahkan.');
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $plan = RecurringPlan::whereHas('account', fn($q) =>
                $q->where('user_id', $request->user()->id)
            )
            ->with(['account:id,name', 'category:id,name,color,icon'])
            ->find($id);

        if (! $plan) return $this->notFound();

        return $this->success(new RecurringPlanResource($plan));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $plan = RecurringPlan::whereHas('account', fn($q) =>
                $q->where('user_id', $request->user()->id)
            )->find($id);

        if (! $plan) return $this->notFound();

        $data = $request->validate([
            'name'      => ['sometimes', 'string', 'max:100'],
            'amount'    => ['sometimes', 'numeric', 'min:1'],
            'frequency' => ['sometimes', 'in:daily,weekly,monthly,yearly'],
            'ends_at'   => ['nullable', 'date'],
            'note'      => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $plan->update($data);
        $plan->load(['account:id,name', 'category:id,name,color,icon']);

        return $this->success(new RecurringPlanResource($plan->fresh()), 'Rencana berhasil diperbarui.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $plan = RecurringPlan::whereHas('account', fn($q) =>
                $q->where('user_id', $request->user()->id)
            )->find($id);

        if (! $plan) return $this->notFound();

        $plan->delete();

        return $this->success(null, 'Rencana berhasil dihapus.');
    }

    public function toggle(Request $request, string $id): JsonResponse
    {
        $plan = RecurringPlan::whereHas('account', fn($q) =>
                $q->where('user_id', $request->user()->id)
            )->find($id);

        if (! $plan) return $this->notFound();

        $plan->update(['is_active' => ! $plan->is_active]);

        $status = $plan->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return $this->success(
            new RecurringPlanResource($plan->fresh()->load(['account:id,name', 'category:id,name,color,icon'])),
            "Rencana berhasil {$status}."
        );
    }
}
