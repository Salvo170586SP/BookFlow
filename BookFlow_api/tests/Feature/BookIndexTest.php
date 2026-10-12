<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Copy;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_books(): void
    {
        $this->getJson('/api/books')->assertUnauthorized();
    }

    public function test_books_are_paginated(): void
    {
        Book::factory()->count(20)->create();

        $this->actingAs(User::factory()->create())
            ->getJson('/api/books')
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.total', 20)
            ->assertJsonStructure([
                'data' => [['id', 'title', 'isbn', 'publisher', 'category', 'authors']],
                'links',
                'meta',
            ]);
    }

    public function test_books_can_be_filtered_by_category(): void
    {
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        $matchingBook = Book::factory()->create(['category_id' => $category->id]);
        Book::factory()->create(['category_id' => $otherCategory->id]);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/books?category_id=' . $category->id)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $matchingBook->id);
    }

    public function test_book_index_reports_total_borrowed_and_remaining_copies(): void
    {
        $book = Book::factory()->create();
        $copies = collect(range(1, 4))->map(
            fn (int $number) => Copy::create([
                'book_id' => $book->id,
                'barcode' => "BOOK-{$book->id}-{$number}",
            ])
        );
        $members = User::factory()->count(3)->create();

        foreach ($members->take(2) as $index => $member) {
            Loan::create([
                'copy_id' => $copies[$index]->id,
                'user_id' => $member->id,
                'loaned_at' => now()->subDays(2),
                'due_at' => now()->addDays(12),
            ]);
        }

        Loan::create([
            'copy_id' => $copies[2]->id,
            'user_id' => $members[2]->id,
            'loaned_at' => now()->subDays(10),
            'due_at' => now()->subDays(3),
            'returned_at' => now()->subDays(4),
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/books')
            ->assertOk()
            ->assertJsonPath('data.0.total_copies_count', 4)
            ->assertJsonPath('data.0.borrowed_copies_count', 2)
            ->assertJsonPath('data.0.remaining_copies_count', 2)
            ->assertJsonPath('data.0.available_copies_count', 2);
    }

    public function test_listing_does_not_run_one_query_per_book(): void
    {
        Book::factory()->count(20)->create();
        $user = User::factory()->create();

        DB::enableQueryLog();

        $this->actingAs($user)->getJson('/api/books')->assertOk();

        // Con l'eager loading le query restano poche, qualunque sia il numero di libri
        $this->assertLessThan(10, count(DB::getQueryLog()));
    }
}
