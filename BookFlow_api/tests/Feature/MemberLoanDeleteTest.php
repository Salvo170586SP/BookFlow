<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberLoanDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_loan_can_be_deleted(): void
    {
        $member = User::factory()->create();
        $copy = Copy::create([
            'book_id' => Book::factory()->create()->id,
            'barcode' => 'copy-001',
        ]);
        $loan = Loan::create([
            'user_id' => $member->id,
            'copy_id' => $copy->id,
            'loaned_at' => now(),
            'due_at' => now()->addDays(14),
        ]);

        $this->actingAs(User::factory()->create())
            ->deleteJson("/api/members/{$member->id}/loans/{$loan->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Prestito eliminato con successo.');

        $this->assertDatabaseMissing('loans', ['id' => $loan->id]);
    }

    public function test_member_cannot_delete_another_members_loan(): void
    {
        $member = User::factory()->create();
        $otherMember = User::factory()->create();
        $copy = Copy::create([
            'book_id' => Book::factory()->create()->id,
            'barcode' => 'copy-002',
        ]);
        $loan = Loan::create([
            'user_id' => $otherMember->id,
            'copy_id' => $copy->id,
            'loaned_at' => now(),
            'due_at' => now()->addDays(14),
        ]);

        $this->actingAs(User::factory()->create())
            ->deleteJson("/api/members/{$member->id}/loans/{$loan->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('loans', ['id' => $loan->id]);
    }
}
