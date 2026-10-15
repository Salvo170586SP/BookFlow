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

    public function test_books_can_be_searched_across_all_pages(): void
    {
        foreach (range(1, 15) as $number) {
            Book::factory()->create(['title' => sprintf('A Catalog Book %02d', $number)]);
        }
        $matchingBook = Book::factory()->create(['title' => 'Z Global Search Result']);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/books?search=Global%20Search%20Result')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $matchingBook->id);
    }

    public function test_books_can_be_filtered_by_published_year_across_all_pages(): void
    {
        foreach (range(1, 15) as $number) {
            Book::factory()->create([
                'title' => sprintf('A Catalog Book %02d', $number),
                'published_year' => 1990,
            ]);
        }
        $matchingBook = Book::factory()->create([
            'title' => 'Z Book From Another Year',
            'published_year' => 2020,
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/books?published_year=2020')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $matchingBook->id);
    }

    public function test_book_year_options_include_distinct_years_from_the_full_catalog(): void
    {
        Book::factory()->create(['published_year' => 2000]);
        Book::factory()->create(['published_year' => 2020]);
        Book::factory()->create(['published_year' => 2000]);
        Book::factory()->create(['published_year' => null]);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/books/years')
            ->assertOk()
            ->assertExactJson(['data' => [2020, 2000]]);
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

    public function test_book_show_includes_loaned_members_for_each_copy(): void
    {
        $book = Book::factory()->create();
        $copy = Copy::create([
            'book_id' => $book->id,
            'barcode' => "BOOK-{$book->id}-1",
        ]);
        $member = User::factory()->create();

        Loan::create([
            'copy_id' => $copy->id,
            'user_id' => $member->id,
            'loaned_at' => now()->subDays(2),
            'due_at' => now()->addDays(12),
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/books/' . $book->id)
            ->assertOk()
            ->assertJsonPath('data.copies.0.loans.0.user.id', $member->id)
            ->assertJsonPath('data.copies.0.loans.0.user.name', $member->name);
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
