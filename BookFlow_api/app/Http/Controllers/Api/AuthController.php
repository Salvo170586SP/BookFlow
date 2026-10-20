<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Http\Requests\BookIndexRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\LoanResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserResource;
use App\Models\Book;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuthController extends Controller
{
    public function register(AuthRegisterRequest $request): Response
    {
        $user = User::create($request->validated());
        $user->assignRole('member');

        // Effettuiamo il login subito dopo la registrazione
        Auth::login($user);
        $request->session()->regenerate();

        return response()->noContent();
    }

    public function login(AuthLoginRequest $request): Response
    {
        // attempt() cerca l'utente, verifica la password e, se è corretta, apre la sessione
        if (! Auth::attempt($request->validated())) {
            throw ValidationException::withMessages([
                'email' => ['Le credenziali non sono corrette.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->noContent();
    }

    public function logout(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load('roles', 'loans'));
    }

    public function indexMine(Request $request): AnonymousResourceCollection
    {
        $loans = $request->user()
            ->loans()
            ->whereNull('returned_at')
            ->with([
                'copy.book' => fn($query) => $query
                    ->with([
                        'publisher:id,name',
                        'category:id,name',
                        'authors:id,name',
                    ])
                    ->withCount([
                        'copies as available_copies_count' => fn(Builder $query) => $query
                            ->where('status', 'available')
                            ->whereDoesntHave(
                                'loans',
                                fn(Builder $loanQuery) => $loanQuery->whereNull('returned_at')
                            ),
                    ]),
            ])
            ->latest('loaned_at')
            ->paginate(12);

        return LoanResource::collection($loans);
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


    public function booksMember(BookIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? 10;

        $books = Book::query()
            // Carichiamo in anticipo le relazioni, e solo le colonne che servono
            ->where('member_id', Auth::id())
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
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return BookResource::collection($books);
    }
}
