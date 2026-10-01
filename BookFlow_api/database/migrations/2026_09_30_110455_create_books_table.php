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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // Codice ISBN: identifica l'edizione, quindi deve essere univoco
            $table->string('isbn', 17)->unique();
            $table->text('description')->nullable();
            // Anno di pubblicazione (facoltativo)
            $table->unsignedSmallInteger('published_year')->nullable();
            // Editore: se viene eliminato, il libro resta ma senza editore
            $table->foreignId('publisher_id')->nullable()->constrained()->nullOnDelete();
            // Categoria: non si può eliminare una categoria che contiene libri
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
