<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_update_creates_missing_details(): void
    {
        $member = User::factory()->create();

        $this->actingAs(User::factory()->create())
            ->putJson("/api/members/{$member->id}", [
                'name' => 'Updated member',
                'phone' => '555-0101',
                'address' => '1 Main Street',
                'birth_city' => 'Rome',
                'birth_date' => '1990-01-02',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated member')
            ->assertJsonPath('data.detail.phone', '555-0101')
            ->assertJsonPath('data.detail.address', '1 Main Street')
            ->assertJsonPath('data.detail.birth_city', 'Rome')
            ->assertJsonPath('data.detail.birth_date', '1990-01-02');

        $this->assertDatabaseHas('member_details', [
            'user_id' => $member->id,
            'phone' => '555-0101',
            'address' => '1 Main Street',
            'birth_city' => 'Rome',
            'birth_date' => '1990-01-02',
        ]);
    }

    public function test_member_update_changes_existing_details_without_creating_another_row(): void
    {
        $member = User::factory()->create();
        $member->detail()->create([
            'phone' => '555-0000',
            'address' => 'Old address',
        ]);

        $this->actingAs(User::factory()->create())
            ->putJson("/api/members/{$member->id}", [
                'name' => $member->name,
                'phone' => '555-0101',
                'address' => 'New address',
            ])
            ->assertOk()
            ->assertJsonPath('data.detail.phone', '555-0101')
            ->assertJsonPath('data.detail.address', 'New address');

        $this->assertDatabaseCount('member_details', 1);
        $this->assertDatabaseHas('member_details', [
            'user_id' => $member->id,
            'phone' => '555-0101',
            'address' => 'New address',
        ]);
    }
}
