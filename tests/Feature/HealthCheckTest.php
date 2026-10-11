<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_returns_json_structure(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'app',
                'timestamp',
                'services' => [
                    'database',
                    'redis',
                ],
            ]);
    }

    public function test_root_endpoint_returns_online_status(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'online',
                'client' => 'Northwind Ceramics',
            ]);
    }
}
