<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_include_their_book_count(): void
    {
        $categoryWithBooks = Category::factory()->create(['name' => 'Fiction']);
        $emptyCategory = Category::factory()->create(['name' => 'History']);
        Book::factory()->count(2)->create(['category_id' => $categoryWithBooks->id]);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/categories')
            ->assertOk()
            ->assertJsonPath('data.0.books_count', 2)
            ->assertJsonPath('data.1.books_count', 0);
    }
}