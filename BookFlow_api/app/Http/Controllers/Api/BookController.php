<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookIndexRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    public function index(BookIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? 15;

        $books = Book::query()
            // Carichiamo in anticipo le relazioni, e solo le colonne che servono
            ->with(['publisher:id,name', 'category:id,name', 'authors:id,name'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $term = '%' . addcslashes($search, '%_\\') . '%';
                $query->where(function (Builder $q) use ($term) {
                    $q->where('title', 'like', $term)
                        ->orWhere('isbn', 'like', $term)
                        ->orWhereHas('authors', fn(Builder $a) => $a->where('name', 'like', $term));
                });
            })
            ->when($filters['category_id'] ?? null, fn(Builder $query, $id) => $query->where('category_id', $id))
            // Ordine stabile: il secondo criterio (id) evita doppioni o salti tra una pagina e l'altra
            ->orderBy('title')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return BookResource::collection($books);
    }
}
