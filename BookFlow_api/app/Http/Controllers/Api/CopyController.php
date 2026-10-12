<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CopyRequest;
use App\Http\Requests\UpdateCopyRequest;
use App\Http\Resources\CopyResource;
use App\Models\Book;
use App\Models\Copy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CopyController extends Controller
{
    public function index(Book $book, Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        return CopyResource::collection(
            $book->copies()->orderBy('id')->get()
        );
    }

    public function update(UpdateCopyRequest $request, Book $book, Copy $copy): CopyResource
    {
        $copy = DB::transaction(function () use ($book, $copy, $request): Copy {
            $copy = $book->copies()->lockForUpdate()->findOrFail($copy->id);
            $copy->update($request->validated());

            return $copy;
        });

        return new CopyResource($copy);
    }

    public function destroy(Request $request, Book $book, Copy $copy): JsonResponse|Response
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $deleted = DB::transaction(function () use ($book, $copy): bool {
            $copy = $book->copies()->lockForUpdate()->findOrFail($copy->id);

            if ($copy->loans()->exists()) {
                return false;
            }

            $copy->delete();

            return true;
        });

        if (! $deleted) {
            return response()->json([
                'message' => 'Impossibile eliminare questa copia: è collegata a uno o più prestiti.',
            ], 409);
        }

        return response()->noContent();
    }

    // Crea la quantità richiesta di copie per il libro, tutte disponibili inizialmente.
    public function store(CopyRequest $request, Book $book): int
    {
        // Usa solo la quantità già validata dal Form Request.
        $quantity = (int) $request->validated('quantity');

        // Se una creazione fallisce, la transazione annulla anche le copie già inserite.
        return DB::transaction(function () use ($book, $quantity): int {
            for ($index = 0; $index < $quantity; $index++) {
                $book->copies()->create([
                    // ULID genera un identificativo praticamente univoco per ciascuna copia.
                    'barcode' => 'BF-'.Str::ulid(),
                    'status' => 'available',
                ]);
            }

            // Comunica al chiamante quante copie sono state create.
            return $quantity;
        });
    }
}
