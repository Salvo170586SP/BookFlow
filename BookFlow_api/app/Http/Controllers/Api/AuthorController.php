<?php

namespace App\Http\Controllers\Api;

use Closure;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorIndexRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuthorController extends Controller
{
    public function index(AuthorIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        $authors = Author::query()
            ->with(['books' => $this->bookVisibility($request)])
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $term = '%' . addcslashes($search, '%_\\') . '%';
                $query->where('name', 'like', $term);
            })
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10);

        return AuthorResource::collection($authors);
    }

    public function show(Request $request, Author $author): AuthorResource
    {
        return new AuthorResource($author->load(['books' => $this->bookVisibility($request)]));
    }

    // Elenco leggero e completo degli autori, per menu a tendina e filtri
    public function options(): JsonResponse
    {
        return response()->json([
            'data' => Author::orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function bookVisibility(Request $request): Closure
    {
        return function (Builder $query) use ($request): void {
            if ($request->user()?->hasRole('member')) {
                $query->hasAvailableCopy();
            }
        };
    }
}
