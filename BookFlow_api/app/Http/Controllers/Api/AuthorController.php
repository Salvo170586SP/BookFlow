<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
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
}
