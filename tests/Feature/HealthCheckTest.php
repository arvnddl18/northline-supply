<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
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
                    'database' => ['connection', 'status'],
                    'redis' => ['status'],
                    'queue' => ['connection', 'status', 'threshold'],
                ],
            ]);
    }

    public function test_root_endpoint_returns_online_status(): void
    {
        $response = $this->getJson('/');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'online',
                'client' => 'Northwind Ceramics',
            ]);
    }

    public function test_request_correlation_id_assigned_when_missing(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $this->assertTrue($response->headers->has('X-Request-ID'));
        $this->assertNotEmpty($response->headers->get('X-Request-ID'));
    }

    public function test_request_correlation_id_preserved_when_provided(): void
    {
        $customId = 'trace-northwind-12345';
        $response = $this->withHeader('X-Request-ID', $customId)->get('/health');

        $response->assertStatus(200);
        $this->assertEquals($customId, $response->headers->get('X-Request-ID'));
    }

    public function test_structured_logging_channels_resolve(): void
    {
        $jsonChannel = Log::channel('json');
        $this->assertNotNull($jsonChannel);

        $stderrChannel = Log::channel('stderr');
        $this->assertNotNull($stderrChannel);
    }

    public function test_sentry_before_send_scrubs_sensitive_headers_and_data(): void
    {
        $config = require config_path('sentry.php');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('before_send', $config);
        $this->assertFalse($config['send_default_pii']);

        $beforeSend = $config['before_send'];

        $event = \Sentry\Event::createEvent();
        $event->setRequest([
            'headers' => [
                'authorization' => 'Bearer shpat_secret_token_123',
                'x-shopify-hmac-sha256' => 'sample_hmac_hash',
                'x-custom-header' => 'safe_value',
            ],
            'data' => [
                'card' => 'tok_test_card',
                'secret' => 'super_secret',
                'safe_field' => 'ok_data',
            ],
        ]);

        $sanitizedEvent = $beforeSend($event, null);
        $sanitizedRequest = $sanitizedEvent->getRequest();

        $this->assertEquals('[REDACTED]', $sanitizedRequest['headers']['authorization']);
        $this->assertEquals('[REDACTED]', $sanitizedRequest['headers']['x-shopify-hmac-sha256']);
        $this->assertEquals('safe_value', $sanitizedRequest['headers']['x-custom-header']);
        $this->assertEquals('[REDACTED]', $sanitizedRequest['data']['card']);
        $this->assertEquals('[REDACTED]', $sanitizedRequest['data']['secret']);
        $this->assertEquals('ok_data', $sanitizedRequest['data']['safe_field']);
    }
}
