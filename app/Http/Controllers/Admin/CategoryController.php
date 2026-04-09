<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::whereNull('user_id')
            ->parentsOnly()
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parents = Category::whereNull('user_id')->parentsOnly()->get(['id', 'name', 'type']);

        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'type'       => ['required', 'in:income,expense,both'],
            'color'      => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon'       => ['nullable', 'string', 'max:50'],
            'parent_id'  => ['nullable', 'uuid', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['id']         = Str::uuid();
        $data['user_id']    = null;
        $data['is_default'] = true;

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori default berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        $parents = Category::whereNull('user_id')
            ->parentsOnly()
            ->where('id', '!=', $category->id)
            ->get(['id', 'name', 'type']);

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'type'       => ['required', 'in:income,expense,both'],
            'color'      => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon'       => ['nullable', 'string', 'max:50'],
            'parent_id'  => ['nullable', 'uuid', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Pindahkan transaksi yang pakai kategori ini ke null
        $category->transactions()->update(['category_id' => null]);

        // Hapus sub-kategori
        $category->children()->delete();
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
