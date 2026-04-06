<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::forUser(auth()->id())
            ->parentsOnly()
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parents = Category::forUser(auth()->id())->parentsOnly()->get(['id', 'name', 'type']);

        return view('categories.create', compact('parents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
         $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'type'      => ['required', 'in:income,expense,both'],
            'color'     => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon'      => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'uuid', 'exists:categories,id'],
        ]);

        $data['user_id']    = auth()->id();
        $data['is_default'] = false;

        Category::create($data);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        // User hanya bisa edit kategori miliknya sendiri (bukan default sistem)
        abort_if($category->user_id !== auth()->id(), 403);

        $parents = Category::forUser(auth()->id())
            ->parentsOnly()
            ->where('id', '!=', $category->id)
            ->get(['id', 'name', 'type']);

        return view('categories.edit', compact('category', 'parents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        abort_if($category->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'type'      => ['required', 'in:income,expense,both'],
            'color'     => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon'      => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'uuid', 'exists:categories,id'],
        ]);

        $category->update($data);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        abort_if($category->user_id !== auth()->id(), 403);
        abort_if($category->is_default, 403, 'Kategori default tidak bisa dihapus.');

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
