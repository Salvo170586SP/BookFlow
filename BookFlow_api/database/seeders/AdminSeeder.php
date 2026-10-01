<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Le categorie sono un elenco fisso, non dati casuali
        $categories = collect([
            'Romanzo',
            'Saggio',
            'Fantascienza',
            'Fantasy',
            'Giallo',
            'Storia',
            'Biografia',
            'Informatica',
            'Bambini',
            'Poesia',
        ])->map(fn(string $name) => Category::firstOrCreate(['name' => $name]));

        $publishers = Publisher::factory()->count(10)->create();
        $authors = Author::factory()->count(40)->create();

        // recycle() fa riusare gli editori e le categorie già creati,
        // invece di crearne di nuovi per ogni libro
        Book::factory()
            ->count(200)
            ->recycle($publishers)
            ->recycle($categories)
            ->create()
            ->each(function (Book $book) use ($authors) {
                // Da 1 a 3 autori casuali per ogni libro
                $book->authors()->attach($authors->random(rand(1, 3))->pluck('id'));
            });
    }
}
