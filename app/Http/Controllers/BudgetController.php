<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);
        $user  = auth()->user();

        // Ambil semua budget bulan ini beserta data pengeluaran aktual
        $budgets = Budget::where('user_id', $user->id)
            ->forMonth($month, $year)
            ->with('category:id,name,color,icon')
            ->get()
            ->map(function (Budget $budget) {
                $budget->spent_amount = $budget->spent();
                $budget->percentage   = $budget->percentage();
                $budget->status       = $budget->status();
                return $budget;
            })
            ->sortByDesc('percentage');

        // Ringkasan
        $totalBudget  = $budgets->sum('amount');
        $totalSpent   = $budgets->sum('spent_amount');
        $exceededCount = $budgets->where('status', 'exceeded')->count();
        $warningCount  = $budgets->whereIn('status', ['danger', 'warning'])->count();

        // Kategori yang belum punya budget bulan ini (untuk saran)
        $usedCategoryIds = $budgets->pluck('category_id');
        $unusedCategories = Category::forUser($user->id)
            ->ofType('expense')
            ->whereNotIn('id', $usedCategoryIds)
            ->parentsOnly()
            ->get(['id', 'name', 'color', 'icon']);

        return view('budgets.index', compact(
            'budgets', 'month', 'year',
            'totalBudget', 'totalSpent',
            'exceededCount', 'warningCount',
            'unusedCategories'
        ));
    }

    public function create(Request $request): View
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        // Hanya tampilkan kategori yang belum ada budget bulan ini
        $usedIds = Budget::where('user_id', auth()->id())
            ->forMonth($month, $year)
            ->pluck('category_id');

        $categories = Category::forUser(auth()->id())
            ->ofType('expense')
            ->whereNotIn('id', $usedIds)
            ->parentsOnly()
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return view('budgets.create', compact('categories', 'month', 'year'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id'     => ['required', 'uuid', 'exists:categories,id'],
            'amount'          => ['required', 'numeric', 'min:1000'],
            'month'           => ['required', 'integer', 'between:1,12'],
            'year'            => ['required', 'integer', 'min:2020', 'max:2099'],
            'alert_threshold' => ['required', 'integer', 'between:50,100'],
        ]);

        // Cek duplikat
        $exists = Budget::where('user_id', auth()->id())
            ->where('category_id', $data['category_id'])
            ->where('month', $data['month'])
            ->where('year', $data['year'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'category_id' => 'Budget untuk kategori ini di bulan tersebut sudah ada.',
            ])->withInput();
        }

        Budget::create([
            ...$data,
            'user_id'   => auth()->id(),
            'is_active' => true,
        ]);

        return redirect()->route('budgets.index', [
            'month' => $data['month'],
            'year'  => $data['year'],
        ])->with('success', 'Budget berhasil ditambahkan.');
    }

    public function edit(Budget $budget): View
    {
        abort_if($budget->user_id !== auth()->id(), 403);

        $categories = Category::forUser(auth()->id())
            ->ofType('expense')
            ->parentsOnly()
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return view('budgets.edit', compact('budget', 'categories'));
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        abort_if($budget->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'amount'          => ['required', 'numeric', 'min:1000'],
            'alert_threshold' => ['required', 'integer', 'between:50,100'],
            'is_active'       => ['boolean'],
        ]);

        $budget->update($data);

        return redirect()->route('budgets.index', [
            'month' => $budget->month,
            'year'  => $budget->year,
        ])->with('success', 'Budget berhasil diperbarui.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        abort_if($budget->user_id !== auth()->id(), 403);

        $month = $budget->month;
        $year  = $budget->year;

        $budget->delete();

        return redirect()->route('budgets.index', compact('month', 'year'))
            ->with('success', 'Budget berhasil dihapus.');
    }

    /**
     * Salin semua budget bulan lalu ke bulan ini.
     */
    public function copyFromLastMonth(Request $request): RedirectResponse
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        // Hitung bulan sebelumnya
        $prevDate  = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth();
        $prevMonth = $prevDate->month;
        $prevYear  = $prevDate->year;

        $lastMonthBudgets = Budget::where('user_id', auth()->id())
            ->forMonth($prevMonth, $prevYear)
            ->get();

        if ($lastMonthBudgets->isEmpty()) {
            return back()->with('error', 'Tidak ada budget di bulan sebelumnya.');
        }

        $copied = 0;
        foreach ($lastMonthBudgets as $old) {
            $alreadyExists = Budget::where('user_id', auth()->id())
                ->where('category_id', $old->category_id)
                ->forMonth($month, $year)
                ->exists();

            if (! $alreadyExists) {
                Budget::create([
                    'user_id'         => auth()->id(),
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

        return redirect()->route('budgets.index', compact('month', 'year'))
            ->with('success', "{$copied} budget berhasil disalin dari bulan sebelumnya.");
    }
}
