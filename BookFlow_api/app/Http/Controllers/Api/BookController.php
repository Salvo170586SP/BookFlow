<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookIndexRequest;
use App\Http\Requests\BookRequest;
use App\Http\Requests\CopyRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\CopyResource;
use App\Models\Author;
use App\Models\Book;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class BookController extends Controller
{
    public function index(BookIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? 10;

        $query = Book::query();

        if ($request->user()?->hasRole('member')) {
            $query->hasAvailableCopy();
        }

        $books = $query
            // Carichiamo in anticipo le relazioni, e solo le colonne che servono
            ->with(['publisher:id,name', 'category:id,name', 'authors:id,name'])
            ->withCount($this->copyCountDefinitions())
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $term = '%' . addcslashes($search, '%_\\') . '%';
                $query->where(function (Builder $q) use ($term) {
                    $q->where('title', 'like', $term)
                        ->orWhere('isbn', 'like', $term)
                        ->orWhereHas('authors', fn(Builder $a) => $a->where('name', 'like', $term));
                });
            })
            ->when($filters['category_id'] ?? null, fn(Builder $query, $id) => $query->where('category_id', $id))
            ->when(
                isset($filters['published_year']),
                fn(Builder $query) => $query->where('published_year', $filters['published_year'])
            )
            // Ordine stabile: il secondo criterio (id) evita doppioni o salti tra una pagina e l'altra
            ->orderByDesc('updated_at')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return BookResource::collection($books);
    }

    public function years(Request $request): JsonResponse
    {
        $query = Book::query();

        if ($request->user()?->hasRole('member')) {
            $query->hasAvailableCopy();
        }

        return response()->json([
            'data' => $query
                ->whereNotNull('published_year')
                ->distinct()
                ->orderByDesc('published_year')
                ->pluck('published_year'),
        ]);
    }

    public function show(Request $request, Book $book): BookResource
    {
        return new BookResource(
            $book->load([
                'publisher:id,name',
                'category:id,name',
                'authors:id,name',
                'copies.loans.user.detail',
            ])->loadCount($this->copyCountDefinitions())
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
        $conflict = DB::transaction(function () use ($book): ?string {
            $copies = $book->copies()->lockForUpdate()->get();

            if ($copies->contains(fn($copy) => $copy->status !== 'available')) {
                return 'Impossibile eliminare il libro: ci sono copie in prestito o non disponibili.';
            }

            if ($book->copies()->whereHas('loans')->exists()) {
                return 'Impossibile eliminare il libro: una o più copie sono collegate a prestiti.';
            }

            $book->copies()->delete();
            $book->delete();

            return null;
        });

        if ($conflict !== null) {
            return response()->json(['message' => $conflict], 409);
        }

        return response()->noContent();
    }

    /** @return array<int|string, \Closure|string> */
    private function copyCountDefinitions(): array
    {
        return [
            'copies as total_copies_count',
            'copies as borrowed_copies_count' => fn(Builder $query) => $query
                ->whereHas('loans', fn(Builder $loanQuery) => $loanQuery->whereNull('returned_at')),
            'copies as available_copies_count' => fn(Builder $query) => $query
                ->where('status', 'available')
                ->whereDoesntHave('loans', fn(Builder $loanQuery) => $loanQuery->whereNull('returned_at')),
        ];
    }

    public function store(BookRequest $request): BookResource
    {
        $data = $request->validated();
        $cover = $data['cover'] ?? null;
        unset($data['cover']);
        $coverPath = $cover?->store('covers', 'public');

        if ($cover !== null && $coverPath === false) {
            throw new RuntimeException('Impossibile salvare la copertina del libro.');
        }

        if ($coverPath !== null) {
            $data['cover'] = $coverPath;
        }

        $authorIds = $data['author_ids'] ?? [];
        $newAuthorName = $data['new_author_name'] ?? null;
        $newPublisherName = $data['new_publisher_name'] ?? null;
        unset($data['author_ids']);
        unset($data['new_author_name'], $data['new_publisher_name']);
        $isMember = $request->user()?->hasRole('member') ?? false;
        $data['member_id'] = $isMember
            ? $request->user()->getKey()
            : null;

        try {
            $book = DB::transaction(
                function () use ($data, $authorIds, $newAuthorName, $newPublisherName, $isMember): Book {
                    if ($newPublisherName !== null) {
                        $data['publisher_id'] = Publisher::create(['name' => $newPublisherName])->getKey();
                    }

                    if ($newAuthorName !== null) {
                        $authorIds[] = Author::create(['name' => $newAuthorName])->getKey();
                    }

                    $book = Book::create($data);
                    $book->authors()->sync($authorIds);

                    if ($isMember) {
                        $book->copies()->create([
                            'barcode' => 'BF-' . Str::ulid(),
                            'status' => 'available',
                        ]);
                    }

                    return $book;
                }
            );
        } catch (Throwable $exception) {
            if ($coverPath !== null) {
                Storage::disk('public')->delete($coverPath);
            }

            throw $exception;
        }

        return new BookResource($book->load(['publisher', 'category', 'authors']));
    }


    public function update(BookRequest $request, Book $book): BookResource
    {
        $data = $request->validated();
        $previousCover = $book->cover;
        $cover = $data['cover'] ?? null;
        unset($data['cover']);
        $coverPath = $cover?->store('covers', 'public');

        if ($cover !== null && $coverPath === false) {
            throw new RuntimeException('Impossibile salvare la copertina del libro.');
        }

        if ($coverPath !== null) {
            $data['cover'] = $coverPath;
        }

        $authorIds = $data['author_ids'] ?? [];
        $newAuthorName = $data['new_author_name'] ?? null;
        $newPublisherName = $data['new_publisher_name'] ?? null;
        unset($data['author_ids']);
        unset($data['new_author_name'], $data['new_publisher_name']);

        try {
            $book = DB::transaction(
                function () use ($data, $authorIds, $newAuthorName, $newPublisherName, $book): Book {
                    if ($newPublisherName !== null) {
                        $data['publisher_id'] = Publisher::create(['name' => $newPublisherName])->getKey();
                    }

                    if ($newAuthorName !== null) {
                        $authorIds[] = Author::create(['name' => $newAuthorName])->getKey();
                    }

                    $book->update($data);
                    $book->authors()->sync($authorIds);

                    return $book;
                }
            );
        } catch (Throwable $exception) {
            if ($coverPath !== null) {
                Storage::disk('public')->delete($coverPath);
            }

            throw $exception;
        }

        if ($coverPath !== null && $previousCover !== null) {
            Storage::disk('public')->delete($previousCover);
        }

        return new BookResource($book->load(['publisher', 'category', 'authors']));
    }
}
