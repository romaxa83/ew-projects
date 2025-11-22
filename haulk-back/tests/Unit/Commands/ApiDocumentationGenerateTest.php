<?php

namespace Tests\Unit\Commands;

use Tests\TestCase;

class ApiDocumentationGenerateTest extends TestCase
{
    public function test_it_generate_documentation_success()
    {
        $response = $this->artisan('l5-swagger:generate');
        $response->assertExitCode(0);
    }
}
