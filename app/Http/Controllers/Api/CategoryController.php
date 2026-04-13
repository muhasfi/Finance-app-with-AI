<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Semua kategori — default sistem + milik user.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::forUser($request->user()->id)
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->parentsOnly()
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return $this->success(CategoryResource::collection($categories));
    }

    /**
     * List flat (tanpa nested) — berguna untuk dropdown Flutter.
     */
    public function flat(Request $request): JsonResponse
    {
        $categories = Category::forUser($request->user()->id)
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->orderBy('sort_order')
            ->get();

        return $this->success(CategoryResource::collection($categories));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'type'       => ['required', 'in:income,expense,both'],
            'color'      => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon'       => ['nullable', 'string', 'max:50'],
            'parent_id'  => ['nullable', 'uuid', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category = Category::create([
            ...$data,
            'id'         => Str::uuid(),
            'user_id'    => $request->user()->id,
            'is_default' => false,
        ]);

        return $this->created(new CategoryResource($category), 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::where('user_id', $request->user()->id)->find($id);
        if (! $category) return $this->notFound('Kategori tidak ditemukan atau bukan milik Anda.');

        $data = $request->validate([
            'name'       => ['sometimes', 'string', 'max:100'],
            'type'       => ['sometimes', 'in:income,expense,both'],
            'color'      => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon'       => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category->update($data);

        return $this->success(new CategoryResource($category->fresh()), 'Kategori berhasil diperbarui.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $category = Category::where('user_id', $request->user()->id)->find($id);
        if (! $category) return $this->notFound('Kategori tidak ditemukan atau bukan milik Anda.');

        // Pindahkan transaksi ke null sebelum hapus
        $category->transactions()->update(['category_id' => null]);
        $category->children()->each(function ($child) {
            $child->transactions()->update(['category_id' => null]);
            $child->delete();
        });
        $category->delete();

        return $this->success(null, 'Kategori berhasil dihapus.');
    }
}
