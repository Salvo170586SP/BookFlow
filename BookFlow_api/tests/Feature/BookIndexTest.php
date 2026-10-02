<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
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
