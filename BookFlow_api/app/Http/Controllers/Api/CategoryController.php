<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->withCount('books')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(12);

        return CategoryResource::collection($categories);
    }

    // Elenco leggero e completo delle categorie, per menu a tendina e filtri
    public function options(): JsonResponse
    {
        return response()->json([
            'data' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function store(CategoryStoreRequest $request): CategoryResource
    {
        $data = $request->validated();
        $category =  Category::create($data);

        return new CategoryResource($category);
    }

    public function update(CategoryStoreRequest $request,  Category $category): CategoryResource
    {
        $data = $request->validated();
        $category->update($data);

        return new CategoryResource($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'message' => 'Categoria eliminata con successo.'
        ]);
    }
}
