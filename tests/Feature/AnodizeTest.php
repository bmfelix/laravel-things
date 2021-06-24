<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnodizeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIsRouteAvailable()
    {
        $response = $this->get('/anodize');
        $response->assertStatus(302);
    }

    public function testIsMoveTagRouteAvailable()
    {
        $response = $this->get('/anodize/movetag');
        $response->assertStatus(302);
    }
}
