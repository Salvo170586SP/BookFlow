<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CopyController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\PublisherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rotte pubbliche
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Serve solo essere loggati (qualsiasi ruolo)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/me/loans', [AuthController::class, 'indexMine']);
    Route::get('/me/books-created', [AuthController::class, 'booksMember']);

    Route::get('/books/years', [BookController::class, 'years']);
    Route::get('/books', [BookController::class, 'index']);
    Route::post('/books', [BookController::class, 'store']);
    Route::get('/books/{book}', [BookController::class, 'show']);
    Route::put('/books/{book}', [BookController::class, 'update']);
    Route::get('/books/{book}/copies/available', [BookController::class, 'availableCopies']);
    Route::get('/books/{book}/copies', [CopyController::class, 'index']);
    Route::put('/books/{book}/copies/{copy}', [CopyController::class, 'update']);
    Route::delete('/books/{book}/copies/{copy}', [CopyController::class, 'destroy']);
    // La richiesta deve essere autenticata; BookController verifica anche il ruolo admin.
    Route::post('/books/{book}/addCopy', [BookController::class, 'addCopy']);
    Route::delete('/books/{book}', [BookController::class, 'destroy']);


    Route::get('/authors/options', [AuthorController::class, 'options']);
    Route::get('/authors', [AuthorController::class, 'index']);
    Route::get('/authors/{author}', [AuthorController::class, 'show']);

    Route::get('/publishers/options', [PublisherController::class, 'options']);

    Route::get('/categories/options', [CategoryController::class, 'options']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::get('/members', [MemberController::class, 'index']);
    Route::get('/members/{member}', [MemberController::class, 'show']);
    Route::put('/members/{member}', [MemberController::class, 'update']);
    Route::delete('/members/{member}', [MemberController::class, 'destroy']);
    Route::post('/members/{member}/loans', [MemberController::class, 'addLoan']);
    Route::delete('/members/{member}/loans/{loan}', [MemberController::class, 'destroyLoan']);
});
