<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $budgets = Budget::where('user_id', $request->user()->id)
            ->forMonth($month, $year)
            ->when($request->boolean('active_only', true), fn($q) => $q->active())
            ->with('category:id,name,color,icon')
            ->get()
            ->map(function ($b) {
                $b->spent_amount = $b->spent();
                $b->percentage   = $b->percentage();
                $b->status       = $b->status();
                return $b;
            })
            ->sortByDesc('percentage');

        $totalBudget = $budgets->sum('amount');
        $totalSpent  = $budgets->sum('spent_amount');

        return $this->success([
            'budgets'       => BudgetResource::collection($budgets),
            'total_budget'  => (float) $totalBudget,
            'total_spent'   => (float) $totalSpent,
            'total_remaining' => max(0, $totalBudget - $totalSpent),
            'exceeded_count'  => $budgets->where('status', 'exceeded')->count(),
            'month'         => $month,
            'year'          => $year,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id'     => ['required', 'uuid', 'exists:categories,id'],
            'amount'          => ['required', 'numeric', 'min:1000'],
            'month'           => ['required', 'integer', 'between:1,12'],
            'year'            => ['required', 'integer', 'min:2020', 'max:2099'],
            'alert_threshold' => ['sometimes', 'integer', 'between:50,100'],
        ]);

        $exists = Budget::where('user_id', $request->user()->id)
            ->where('category_id', $data['category_id'])
            ->where('month', $data['month'])
            ->where('year', $data['year'])
            ->exists();

        if ($exists) {
            return $this->error('Budget untuk kategori ini di bulan tersebut sudah ada.', 422);
        }

        $budget = Budget::create([
            ...$data,
            'user_id'         => $request->user()->id,
            'alert_threshold' => $data['alert_threshold'] ?? 80,
            'is_active'       => true,
        ]);

        $budget->load('category:id,name,color,icon');

        return $this->created(new BudgetResource($budget), 'Budget berhasil ditambahkan.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $budget = Budget::where('user_id', $request->user()->id)->find($id);
        if (! $budget) return $this->notFound();

        $data = $request->validate([
            'amount'          => ['sometimes', 'numeric', 'min:1000'],
            'alert_threshold' => ['sometimes', 'integer', 'between:50,100'],
            'is_active'       => ['sometimes', 'boolean'],
        ]);

        $budget->update($data);
        $budget->load('category:id,name,color,icon');

        return $this->success(new BudgetResource($budget->fresh()), 'Budget berhasil diperbarui.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $budget = Budget::where('user_id', $request->user()->id)->find($id);
        if (! $budget) return $this->notFound();

        $budget->delete();

        return $this->success(null, 'Budget berhasil dihapus.');
    }

    /**
     * Salin semua budget dari bulan sebelumnya.
     */
    public function copyFromLastMonth(Request $request): JsonResponse
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $prevDate  = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth();
        $prevMonth = $prevDate->month;
        $prevYear  = $prevDate->year;

        $lastMonthBudgets = Budget::where('user_id', $request->user()->id)
            ->forMonth($prevMonth, $prevYear)
            ->get();

        if ($lastMonthBudgets->isEmpty()) {
            return $this->error('Tidak ada budget di bulan sebelumnya.', 404);
        }

        $copied = 0;
        foreach ($lastMonthBudgets as $old) {
            $exists = Budget::where('user_id', $request->user()->id)
                ->where('category_id', $old->category_id)
                ->forMonth($month, $year)
                ->exists();

            if (! $exists) {
                Budget::create([
                    'user_id'         => $request->user()->id,
                    'category_id'     => $old->category_id,
                    'amount'          => $old->amount,
                    'month'           => $month,
                    'year'            => $year,
                    'alert_threshold' => $old->alert_threshold,
                    'is_active'       => true,
                ]);
                $copied++;
            }
        }

        return $this->success(['copied' => $copied], "{$copied} budget berhasil disalin.");
    }
}
