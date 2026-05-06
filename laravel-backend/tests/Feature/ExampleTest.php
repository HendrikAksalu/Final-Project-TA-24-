<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_root_serves_the_spa(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('app');
        $response->assertSee('id="app"', false);
    }
}
