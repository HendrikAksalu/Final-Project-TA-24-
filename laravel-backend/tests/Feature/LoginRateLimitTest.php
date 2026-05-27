<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_many_attempts(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('ValidPass1'),
        ]);

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrong',
            ])->assertStatus(401);
        }

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong',
        ])->assertStatus(429);
    }
}
