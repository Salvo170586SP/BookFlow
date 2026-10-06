<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
             $table->foreignId('copy_id')->constrained()->restrictOnDelete();
            // A chi è stata prestata
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->dateTime('loaned_at');            // quando è uscita
            $table->dateTime('due_at');               // entro quando va restituita
            $table->dateTime('returned_at')->nullable(); // null = ancora fuori
            $table->timestamps();
            // "Questa copia è attualmente in prestito?" è la domanda più frequente
            $table->index(['copy_id', 'returned_at']);
            // "Quali prestiti sono in ritardo?" e "prestiti attivi di un utente"
            $table->index(['user_id', 'returned_at']);
            $table->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
