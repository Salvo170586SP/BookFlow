<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CopyRequest;
use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CopyController extends Controller
{
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
                    'barcode' => 'BF-' . Str::ulid(),
                    'status' => 'available',
                ]);
            }

            // Comunica al chiamante quante copie sono state create.
            return $quantity;
        });
    }
}
