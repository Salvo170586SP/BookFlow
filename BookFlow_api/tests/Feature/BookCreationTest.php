<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_can_be_created_with_multiple_authors(): void
    {
        $authors = Author::factory()->count(2)->create();

        $this->actingAs(User::factory()->create())
            ->postJson('/api/books', [
                'title' => 'Libro di test',
                'isbn' => '9781234567890',
                'category_id' => Category::factory()->create()->id,
                'author_ids' => $authors->pluck('id')->all(),
            ])
            ->assertOk()
            ->assertJsonCount(2, 'data.authors')
            ->assertJsonFragment(['id' => $authors[0]->id, 'name' => $authors[0]->name])
            ->assertJsonFragment(['id' => $authors[1]->id, 'name' => $authors[1]->name]);
    }

    public function test_book_can_be_created_without_authors(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/api/books', [
                'title' => 'Libro senza autore',
                'isbn' => '9781234567890',
                'category_id' => Category::factory()->create()->id,
                'author_ids' => [],
            ])
            ->assertOk()
            ->assertJsonCount(0, 'data.authors');
    }

    public function test_book_can_be_created_with_an_empty_author_option(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/api/books', [
                'title' => 'Libro senza autore',
                'isbn' => '9781234567892',
                'category_id' => Category::factory()->create()->id,
                'author_ids' => [''],
            ])
            ->assertOk()
            ->assertJsonCount(0, 'data.authors');
    }

    public function test_book_can_be_created_when_author_ids_are_omitted(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/api/books', [
                'title' => 'Libro senza autore',
                'isbn' => '9781234567891',
                'category_id' => Category::factory()->create()->id,
            ])
            ->assertOk()
            ->assertJsonCount(0, 'data.authors');
    }
}
