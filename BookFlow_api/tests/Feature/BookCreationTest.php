<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Copy;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_book_can_be_created_with_a_cover_image(): void
    {
        Storage::fake('public');
        $cover = UploadedFile::fake()->image('cover.jpg');

        $response = $this->actingAs(User::factory()->create())
            ->withHeader('Accept', 'application/json')
            ->post('/api/books', [
                'title' => 'Libro con copertina',
                'isbn' => '9781234567898',
                'category_id' => Category::factory()->create()->id,
                'cover' => $cover,
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Libro con copertina');

        $this->assertStringContainsString('/storage/covers/', $response->json('data.cover'));
        $book = Book::query()->where('isbn', '9781234567898')->sole();
        $this->assertDatabaseHas('books', ['id' => $book->id, 'cover' => $book->cover]);
        Storage::disk('public')->assertExists($book->cover);
    }

    public function test_book_cover_must_be_an_image(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create())
            ->withHeader('Accept', 'application/json')
            ->post('/api/books', [
                'title' => 'Libro con file non valido',
                'isbn' => '9781234567899',
                'category_id' => Category::factory()->create()->id,
                'cover' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cover');

        $this->assertDatabaseCount('books', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('covers'));
    }

    public function test_book_can_be_created_with_new_author_and_publisher_names(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/api/books', [
                'title' => 'Libro con nomi nuovi',
                'isbn' => '9781234567895',
                'category_id' => Category::factory()->create()->id,
                'new_author_name' => 'Autore Nuovo',
                'new_publisher_name' => 'Editore Nuovo',
            ])
            ->assertOk()
            ->assertJsonPath('data.publisher.name', 'Editore Nuovo')
            ->assertJsonFragment(['name' => 'Autore Nuovo']);

        $this->assertDatabaseHas('authors', ['name' => 'Autore Nuovo']);
        $this->assertDatabaseHas('publishers', ['name' => 'Editore Nuovo']);
        $this->assertDatabaseCount('books', 1);
    }

    public function test_existing_author_and_publisher_names_are_rejected_without_creating_a_book(): void
    {
        Author::factory()->create(['name' => 'Autore Esistente']);
        Publisher::factory()->create(['name' => 'Editore Esistente']);

        $this->actingAs(User::factory()->create())
            ->postJson('/api/books', [
                'title' => 'Libro duplicato',
                'isbn' => '9781234567896',
                'category_id' => Category::factory()->create()->id,
                'new_author_name' => ' Autore Esistente ',
                'new_publisher_name' => 'Editore Esistente',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['new_author_name', 'new_publisher_name']);

        $this->assertDatabaseCount('books', 0);
        $this->assertDatabaseCount('authors', 1);
        $this->assertDatabaseCount('publishers', 1);
    }

    public function test_existing_and_new_publisher_cannot_be_submitted_together(): void
    {
        $publisher = Publisher::factory()->create();

        $this->actingAs(User::factory()->create())
            ->postJson('/api/books', [
                'title' => 'Libro con editore ambiguo',
                'isbn' => '9781234567897',
                'category_id' => Category::factory()->create()->id,
                'publisher_id' => $publisher->id,
                'new_publisher_name' => 'Editore Nuovo',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['publisher_id', 'new_publisher_name']);

        $this->assertDatabaseCount('books', 0);
        $this->assertDatabaseCount('publishers', 1);
    }

    public function test_book_created_by_a_member_is_associated_with_that_member(): void
    {
        $member = User::factory()->create()->assignRole('member');

        $this->actingAs($member)
            ->postJson('/api/books', [
                'title' => 'Libro donato',
                'isbn' => '9781234567893',
                'category_id' => Category::factory()->create()->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.member_id', $member->id);

        $this->assertDatabaseHas('books', [
            'isbn' => '9781234567893',
            'member_id' => $member->id,
        ]);

        $this->assertDatabaseCount('copies', 1);
        $copy = Copy::query()->sole();
        $this->assertSame('available', $copy->status);
        $this->assertStringStartsWith('BF-', $copy->barcode);
    }

    public function test_book_created_by_a_non_member_has_no_associated_member(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($admin)
            ->postJson('/api/books', [
                'title' => 'Libro della biblioteca',
                'isbn' => '9781234567894',
                'category_id' => Category::factory()->create()->id,
                'member_id' => User::factory()->create()->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.member_id', null);

        $this->assertDatabaseHas('books', [
            'isbn' => '9781234567894',
            'member_id' => null,
        ]);
        $this->assertDatabaseCount('copies', 0);
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
