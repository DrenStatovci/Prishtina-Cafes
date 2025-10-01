<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cafe;
use App\Models\StaffProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CafeOwnerStaffProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_cafe_owner_gets_staff_profile_automatically()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'owner@test.com',
            'name' => 'Test Owner'
        ]);

        // Create a cafe with the user as owner
        $cafe = Cafe::create([
            'owner_id' => $user->id,
            'name' => 'Test Cafe',
            'slug' => 'test-cafe-' . uniqid()
        ]);

        // Check if staff profile was created automatically
        $staffProfile = StaffProfile::where('user_id', $user->id)
            ->where('cafe_id', $cafe->id)
            ->where('position', 'owner')
            ->first();

        $this->assertNotNull($staffProfile, 'Staff profile should be created for cafe owner');
        $this->assertEquals('owner', $staffProfile->position);
        $this->assertTrue($staffProfile->is_active);
        $this->assertEquals($user->id, $staffProfile->user_id);
        $this->assertEquals($cafe->id, $staffProfile->cafe_id);
    }

    public function test_cafe_owner_change_creates_new_staff_profile()
    {
        // Create two users
        $oldOwner = User::factory()->create(['email' => 'oldowner@test.com']);
        $newOwner = User::factory()->create(['email' => 'newowner@test.com']);

        // Create a cafe with first user as owner
        $cafe = Cafe::create([
            'owner_id' => $oldOwner->id,
            'name' => 'Test Cafe',
            'slug' => 'test-cafe-' . uniqid()
        ]);

        // Verify old owner has staff profile
        $oldStaffProfile = StaffProfile::where('user_id', $oldOwner->id)
            ->where('cafe_id', $cafe->id)
            ->where('position', 'owner')
            ->first();
        $this->assertNotNull($oldStaffProfile);

        // Change owner
        $cafe->update(['owner_id' => $newOwner->id]);

        // Check that old owner's staff profile is removed
        $oldStaffProfileAfterChange = StaffProfile::where('user_id', $oldOwner->id)
            ->where('cafe_id', $cafe->id)
            ->where('position', 'owner')
            ->first();
        $this->assertNull($oldStaffProfileAfterChange, 'Old owner staff profile should be removed');

        // Check that new owner has staff profile
        $newStaffProfile = StaffProfile::where('user_id', $newOwner->id)
            ->where('cafe_id', $cafe->id)
            ->where('position', 'owner')
            ->first();
        $this->assertNotNull($newStaffProfile, 'New owner should have staff profile');
        $this->assertEquals('owner', $newStaffProfile->position);
        $this->assertTrue($newStaffProfile->is_active);
    }
}
