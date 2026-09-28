<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // per_page limitato a 100: la stessa rotta serve sia la tabella admin
        // sia le select con ricerca (autocomplete) nel form dei libri
        /*    $perPage = min($request->integer('per_page', 15), 100); */

        $authors = Author::query()
            ->when($request->query('search'), fn($query, $search) =>
            $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10);

        return AuthorResource::collection($authors);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AuthorRequest $request)
    {
        $data = $request->validated();
        $author = Author::create($data);

        return (new AuthorResource($author))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Author $author)
    {
        return new AuthorResource($author);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AuthorRequest $request, Author $author)
    {
        $author->update($request->validated());

        return new AuthorResource($author);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author)
    {
        // Da attivare quando esisterà la tabella pivot author_book:
        // if ($author->books()->exists()) {
        //     return response()->json([
        //         'message' => 'Impossibile eliminare un autore con libri associati.',
        //     ], 409);
        // }

        $author->delete();

        return response()->noContent();
    }
}
