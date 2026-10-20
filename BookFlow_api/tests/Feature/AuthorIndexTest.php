<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Copy;
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

    public function test_authors_can_be_searched_by_name_across_all_pages(): void
    {
        foreach (range(1, 10) as $number) {
            Author::factory()->create(['name' => sprintf('A Catalog Author %02d', $number)]);
        }
        $matchingAuthor = Author::factory()->create(['name' => 'Z Global Search Result']);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/authors?search=Global%20Search%20Result')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $matchingAuthor->id);
    }

    public function test_show_author_returns_only_that_authors_books(): void
    {
        $author = Author::factory()->create();
        $otherAuthor = Author::factory()->create();
        $books = Book::factory()->count(2)->create();
        $otherBook = Book::factory()->create();
        $author->books()->attach($books->modelKeys());
        $otherAuthor->books()->attach($otherBook->id);

        $this->actingAs(User::factory()->create())
            ->getJson("/api/authors/{$author->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $author->id)
            ->assertJsonCount(2, 'data.books')
            ->assertJsonFragment(['title' => $books[0]->title])
            ->assertJsonFragment(['title' => $books[1]->title])
            ->assertJsonMissing(['title' => $otherBook->title]);
    }

    public function test_member_author_books_only_include_books_with_available_copies(): void
    {
        $member = User::factory()->create()->assignRole('member');
        $author = Author::factory()->create();
        $availableBook = Book::factory()->create();
        $unavailableBook = Book::factory()->create();
        $author->books()->attach([$availableBook->id, $unavailableBook->id]);

        Copy::create([
            'book_id' => $availableBook->id,
            'barcode' => "BOOK-{$availableBook->id}-1",
            'status' => 'available',
        ]);
        Copy::create([
            'book_id' => $unavailableBook->id,
            'barcode' => "BOOK-{$unavailableBook->id}-1",
            'status' => 'maintenance',
        ]);

        $this->actingAs($member)
            ->getJson("/api/authors/{$author->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data.books')
            ->assertJsonPath('data.books.0.id', $availableBook->id);
    }
}
