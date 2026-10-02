<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
