<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuthorController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $authors = Author::query()
            ->with('books')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(12);

        return AuthorResource::collection($authors);
    }

    public function show(Author $author): AuthorResource
    {
        return new AuthorResource($author->load('books'));
    }

    // Elenco leggero e completo degli autori, per menu a tendina e filtri
    public function options(): JsonResponse
    {
        return response()->json([
            'data' => Author::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
