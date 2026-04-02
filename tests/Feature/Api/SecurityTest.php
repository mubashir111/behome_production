<?php
/*
|--------------------------------------------------------------------------
| Security Performance Test
|--------------------------------------------------------------------------
|
| This test verifies that the application is running in a production-safe
| manner, with debug mode off and mandatory security headers present.
*/

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Support\Facades\Config;

class SecurityTest extends TestCase
{
    /** @test */
    public function app_debug_is_false_in_production()
    {
        // This test only runs if APP_ENV is production
        if (app()->isProduction()) {
            $this->assertFalse(config('app.debug'), 'APP_DEBUG must be false in production environments!');
        }
    }

    /** @test */
    public function security_headers_are_present()
    {
        $response = $this->withHeader('x-api-key', config('app.mix_api_key'))
                         ->get('/api/frontend/setting');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /** @test */
    public function api_endpoints_return_json()
    {
        $response = $this->withHeader('x-api-key', config('app.mix_api_key'))
                         ->get('/api/frontend/setting');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'company_name',
                'company_email',
            ]
        ]);
    }
}
