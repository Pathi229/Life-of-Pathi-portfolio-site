<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmptyGardenTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_empty_garden_still_has_a_working_homepage(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
