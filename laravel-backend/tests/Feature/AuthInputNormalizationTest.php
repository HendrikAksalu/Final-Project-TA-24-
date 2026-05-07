<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthInputNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_login_work_with_spaces_and_email_case(): void
    {
        $register = $this->postJson('/api/register', [
            'name' => '  Hendrik Test  ',
            'email' => '  AKSALUHENDRIK@GMAIL.COM ',
            'password' => 'Hannahanna66  ',
            'password_confirmation' => 'Hannahanna66  ',
        ]);

        $register->assertCreated();
        $register->assertJsonPath('user.email', 'aksaluhendrik@gmail.com');

        $login = $this->postJson('/api/login', [
            'email' => '  aksaluhendrik@gmail.com ',
            'password' => 'Hannahanna66',
        ]);

        $login->assertOk();
        $this->assertNotEmpty($login->json('token'));
    }
}
