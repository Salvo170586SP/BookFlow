<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MemberLoanCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_is_assigned_a_random_available_copy_for_the_requested_book(): void
    {
        Role::findOrCreate('member', 'web');
        $member = User::factory()->create()->assignRole('member');
        $book = Book::factory()->create();
        $availableCopy = Copy::create([
            'book_id' => $book->id,
            'barcode' => 'available-001',
            'status' => 'available',
        ]);
        $anotherAvailableCopy = Copy::create([
            'book_id' => $book->id,
            'barcode' => 'available-002',
            'status' => 'available',
        ]);
        $loanedCopy = Copy::create([
            'book_id' => $book->id,
            'barcode' => 'loaned-001',
            'status' => 'available',
        ]);
        $unavailableCopy = Copy::create([
            'book_id' => $book->id,
            'barcode' => 'unavailable-001',
            'status' => 'maintenance',
        ]);
        $otherBookCopy = Copy::create([
            'book_id' => Book::factory()->create()->id,
            'barcode' => 'other-book-001',
            'status' => 'available',
        ]);
        Loan::create([
            'user_id' => User::factory()->create()->id,
            'copy_id' => $loanedCopy->id,
            'loaned_at' => now(),
            'due_at' => now()->addDays(14),
        ]);

        $response = $this->actingAs($member)
            ->postJson("/api/members/{$member->id}/loans", [
                'book_id' => $book->id,
                'due_at' => now()->addDays(14)->toDateString(),
            ])
            ->assertOk();

        $assignedCopyId = $response->json('data.copy.id');
        $this->assertContains($assignedCopyId, [$availableCopy->id, $anotherAvailableCopy->id]);

        $this->assertDatabaseHas('loans', [
            'user_id' => $member->id,
            'copy_id' => $assignedCopyId,
        ]);
        $this->assertDatabaseMissing('loans', ['copy_id' => $unavailableCopy->id]);
        $this->assertDatabaseMissing('loans', ['copy_id' => $otherBookCopy->id]);
    }

    public function test_member_cannot_book_for_another_member(): void
    {
        Role::findOrCreate('member', 'web');
        $member = User::factory()->create()->assignRole('member');
        $otherMember = User::factory()->create()->assignRole('member');
        $book = Book::factory()->create();
        Copy::create([
            'book_id' => $book->id,
            'barcode' => 'available-002',
            'status' => 'available',
        ]);

        $this->actingAs($member)
            ->postJson("/api/members/{$otherMember->id}/loans", [
                'book_id' => $book->id,
                'due_at' => now()->addDays(14)->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_member_cannot_book_when_no_available_copy_exists(): void
    {
        Role::findOrCreate('member', 'web');
        $member = User::factory()->create()->assignRole('member');
        $book = Book::factory()->create();
        Copy::create([
            'book_id' => $book->id,
            'barcode' => 'maintenance-001',
            'status' => 'maintenance',
        ]);

        $this->actingAs($member)
            ->postJson("/api/members/{$member->id}/loans", [
                'book_id' => $book->id,
                'due_at' => now()->addDays(14)->toDateString(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('book_id');

        $this->assertDatabaseCount('loans', 0);
    }

    public function test_admin_can_still_create_a_loan_with_a_selected_copy(): void
    {
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('member', 'web');
        $admin = User::factory()->create()->assignRole('admin');
        $member = User::factory()->create()->assignRole('member');
        $book = Book::factory()->create();
        $copy = Copy::create([
            'book_id' => $book->id,
            'barcode' => 'admin-selected-001',
            'status' => 'available',
        ]);

        $this->actingAs($admin)
            ->postJson("/api/members/{$member->id}/loans", [
                'copy_id' => $copy->id,
                'due_at' => now()->addDays(14)->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('data.copy.id', $copy->id);

        $this->assertDatabaseHas('loans', [
            'user_id' => $member->id,
            'copy_id' => $copy->id,
        ]);
    }
}
