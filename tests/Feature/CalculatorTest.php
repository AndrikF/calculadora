<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_calc_stores_history()
    {
        $response = $this->postJson('/api/calc', [
            'a' => 4,
            'b' => 2,
            'op' => 'add',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['result','history']);
        $this->assertEquals(6, $response->json('result'));
        $this->assertNotEmpty(session('calc_history'));
    }
}
