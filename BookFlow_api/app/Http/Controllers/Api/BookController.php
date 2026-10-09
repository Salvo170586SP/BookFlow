<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookIndexRequest;
use App\Http\Requests\CopyRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\CopyResource;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(BookIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? 15;

        $books = Book::query()
            // Carichiamo in anticipo le relazioni, e solo le colonne che servono
            ->with(['publisher:id,name', 'category:id,name', 'authors:id,name'])
            ->withCount([
                'copies as available_copies_count' => fn(Builder $query) => $query
                    ->where('status', 'available')
                    ->whereDoesntHave('loans', fn(Builder $loanQuery) => $loanQuery->whereNull('returned_at')),
            ])
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

    public function show(Book $book): BookResource
    {
        return new BookResource(
            $book->load(['publisher:id,name', 'category:id,name', 'authors:id,name'])
                ->loadCount([
                    'copies as available_copies_count' => fn(Builder $query) => $query
                        ->where('status', 'available')
                        ->whereDoesntHave('loans', fn(Builder $loanQuery) => $loanQuery->whereNull('returned_at')),
                ])
        );
    }

    public function availableCopies(Book $book): AnonymousResourceCollection
    {
        $copies = $book->copies()
            ->where('status', 'available')
            ->whereDoesntHave('loans', fn(Builder $query) => $query->whereNull('returned_at'))
            ->orderBy('barcode')
            ->get();

        return CopyResource::collection($copies);
    }

    public function addCopy(CopyRequest $request, Book $book, CopyController $copyController): JsonResponse
    {
        // Il middleware garantisce l'accesso autenticato; qui limitiamo l'operazione agli admin.
        abort_unless($request->user()?->hasRole('admin'), 403);

        // Delega la creazione in blocco e riceve il numero effettivamente creato.
        $createdCount = $copyController->store($request, $book);

        // Restituisce al frontend un messaggio e il conteggio per confermare l'operazione.
        return response()->json([
            'message' => "{$createdCount} copie create con successo.",
            'created_count' => $createdCount,
        ]);
    }

    public function destroy(Book $book): JsonResponse|Response
    {
        if ($book->copies()->where('status', '!=', 'available')->exists()) {
            return response()->json([
                'message' => 'Impossibile eliminare il libro: ci sono copie in prestito o non disponibili.',
            ], 409);
        }

        DB::transaction(function () use ($book) {
            $book->copies()->delete();
            $book->delete();
        });

        return response()->noContent();
    }
}
