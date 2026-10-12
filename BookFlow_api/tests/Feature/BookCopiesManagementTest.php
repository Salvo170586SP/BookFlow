<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookCopiesManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_all_copies_for_a_book(): void
    {
        $book = Book::factory()->create();
        $firstCopy = Copy::create(['book_id' => $book->id, 'barcode' => 'copy-001']);
        $secondCopy = Copy::create([
            'book_id' => $book->id,
            'barcode' => 'copy-002',
            'status' => 'damaged',
        ]);

        $this->actingAs($this->admin())
            ->getJson("/api/books/{$book->id}/copies")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $firstCopy->id)
            ->assertJsonPath('data.1.status', 'damaged')
            ->assertJsonPath('data.1.id', $secondCopy->id);
    }

    public function test_admin_can_update_a_copy_status(): void
    {
        $book = Book::factory()->create();
        $copy = Copy::create(['book_id' => $book->id, 'barcode' => 'copy-003']);

        $this->actingAs($this->admin())
            ->putJson("/api/books/{$book->id}/copies/{$copy->id}", ['status' => 'maintenance'])
            ->assertOk()
            ->assertJsonPath('data.status', 'maintenance');

        $this->assertDatabaseHas('copies', ['id' => $copy->id, 'status' => 'maintenance']);
    }

    public function test_copy_status_must_be_a_supported_value(): void
    {
        $book = Book::factory()->create();
        $copy = Copy::create(['book_id' => $book->id, 'barcode' => 'copy-004']);

        $this->actingAs($this->admin())
            ->putJson("/api/books/{$book->id}/copies/{$copy->id}", ['status' => 'borrowed'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_admin_can_delete_a_copy_without_loans(): void
    {
        $book = Book::factory()->create();
        $copy = Copy::create(['book_id' => $book->id, 'barcode' => 'copy-005']);

        $this->actingAs($this->admin())
            ->deleteJson("/api/books/{$book->id}/copies/{$copy->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('copies', ['id' => $copy->id]);
    }

    public function test_copy_with_loan_history_cannot_be_deleted(): void
    {
        $book = Book::factory()->create();
        $copy = Copy::create(['book_id' => $book->id, 'barcode' => 'copy-006']);
        $member = User::factory()->create();
        Loan::create([
            'copy_id' => $copy->id,
            'user_id' => $member->id,
            'loaned_at' => now()->subDays(2),
            'due_at' => now()->addDays(12),
            'returned_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->deleteJson("/api/books/{$book->id}/copies/{$copy->id}")
            ->assertConflict()
            ->assertJsonPath('message', 'Impossibile eliminare questa copia: è collegata a uno o più prestiti.');

        $this->assertDatabaseHas('copies', ['id' => $copy->id]);
    }

    public function test_copy_cannot_be_managed_through_another_book(): void
    {
        $book = Book::factory()->create();
        $otherBook = Book::factory()->create();
        $copy = Copy::create(['book_id' => $otherBook->id, 'barcode' => 'copy-007']);

        $this->actingAs($this->admin())
            ->putJson("/api/books/{$book->id}/copies/{$copy->id}", ['status' => 'lost'])
            ->assertNotFound();
    }

    public function test_non_admin_cannot_manage_book_copies(): void
    {
        $book = Book::factory()->create();
        $copy = Copy::create(['book_id' => $book->id, 'barcode' => 'copy-008']);

        $this->actingAs(User::factory()->create())
            ->getJson("/api/books/{$book->id}/copies")
            ->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->putJson("/api/books/{$book->id}/copies/{$copy->id}", ['status' => 'lost'])
            ->assertForbidden();
    }

    private function admin(): User
    {
        Role::findOrCreate('admin', 'web');

        return User::factory()->create()->assignRole('admin');
    }
}
