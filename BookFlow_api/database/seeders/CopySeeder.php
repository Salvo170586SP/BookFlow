<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Copy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CopySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = Book::all();

        foreach ($books as $book) {
            Copy::create([
                'book_id' => $book->id,
                'barcode' =>  fake()->unique()->ean13(),
                'status' => 'available'
            ]);
        }
    }
}
