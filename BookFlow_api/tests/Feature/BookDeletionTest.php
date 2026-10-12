<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_with_a_returned_loan_cannot_be_deleted(): void
    {
        $book = Book::factory()->create();
        $copy = Copy::create([
            'book_id' => $book->id,
            'barcode' => 'book-deletion-copy-001',
        ]);
        $member = User::factory()->create();
        $loan = Loan::create([
            'copy_id' => $copy->id,
            'user_id' => $member->id,
            'loaned_at' => now()->subDays(10),
            'due_at' => now()->subDays(3),
            'returned_at' => now()->subDays(2),
        ]);

        $this->actingAs($this->admin())
            ->deleteJson("/api/books/{$book->id}")
            ->assertConflict()
            ->assertJsonPath(
                'message',
                'Impossibile eliminare il libro: una o più copie sono collegate a prestiti.',
            );

        $this->assertDatabaseHas('books', ['id' => $book->id]);
        $this->assertDatabaseHas('copies', ['id' => $copy->id]);
        $this->assertDatabaseHas('loans', ['id' => $loan->id]);
    }

    private function admin(): User
    {
        Role::findOrCreate('admin', 'web');

        return User::factory()->create()->assignRole('admin');
    }
}
