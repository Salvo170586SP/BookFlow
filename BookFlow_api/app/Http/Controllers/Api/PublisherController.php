<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use Illuminate\Http\JsonResponse;
 
class PublisherController extends Controller
{
    // Elenco leggero e completo degli editori, per menu a tendina e filtri
    public function options(): JsonResponse
    {
        return response()->json([
            'data' => Publisher::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
