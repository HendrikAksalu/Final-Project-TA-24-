<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_root_redirects_to_logi_sisse(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/logi-sisse');
    }
}
