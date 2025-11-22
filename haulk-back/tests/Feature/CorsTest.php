<?php

namespace Tests\Feature;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CorsTest extends TestCase
{
    public function test_it_not_get_cors_errors(): void
    {
        $host = config('cors.test_host');

        if (empty($host)) {
            self::markTestSkipped();
        }

        $response = Http::post(
            sprintf('http://%s/api/login', $host),
            [
                'email' => 'some_not_exists_email@example.com',
                'password' => 'password',
            ]
        );

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->status());

        self::assertArrayHasKey('Access-Control-Allow-Origin', $response->headers());
    }
}
