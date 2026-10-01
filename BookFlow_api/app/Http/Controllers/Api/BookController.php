<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        // Elementi per pagina: default 15, mai più di 50 (altrimenti un client
        // potrebbe chiedere per_page=100000 e mettere in ginocchio il server)
        $perPage = min($request->integer('per_page', 15), 50);

        $books = Book::query()
            // Carichiamo in anticipo le relazioni, e solo le colonne che servono
            ->with(['publisher:id,name', 'category:id,name', 'authors:id,name'])
            // Ordine stabile: il secondo criterio (id) evita doppioni o salti tra una pagina e l'altra
            ->orderBy('title')
            ->orderBy('id')
            ->paginate($perPage);

        return BookResource::collection($books);
    }
}
