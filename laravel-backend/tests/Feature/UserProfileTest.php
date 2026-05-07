<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_patch_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/user', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertOk();
        $response->assertJsonPath('user.name', 'New Name');
        $response->assertJsonPath('user.email', 'new@example.com');

        $this->assertSame('New Name', $user->fresh()->name);
        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_patch_password_requires_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'CorrectHorse1!',
        ]);

        Sanctum::actingAs($user);

        $bad = $this->patchJson('/api/user/password', [
            'current_password' => 'wrong-pass',
            'password' => 'NewSecure99!',
            'password_confirmation' => 'NewSecure99!',
        ]);

        $bad->assertStatus(422);
        $bad->assertJsonPath('message', 'Praegune salasõna on vale.');

        $good = $this->patchJson('/api/user/password', [
            'current_password' => 'CorrectHorse1!',
            'password' => 'NewSecure99!',
            'password_confirmation' => 'NewSecure99!',
        ]);

        $good->assertOk();
    }

    public function test_profile_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => 'mine@example.com']);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/user', [
            'email' => 'taken@example.com',
        ]);

        $response->assertStatus(422);
    }
}
