<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_authors_include_their_books_as_a_list(): void
    {
        $author = Author::factory()->create();
        $books = Book::factory()->count(2)->create();
        $author->books()->attach($books->modelKeys());

        $this->actingAs(User::factory()->create())
            ->getJson('/api/authors')
            ->assertOk()
            ->assertJsonPath('data.0.id', $author->id)
            ->assertJsonCount(2, 'data.0.books')
            ->assertJsonStructure([
                'data' => [['books' => [['id', 'title']]]],
                'meta',
            ]);
    }
}
