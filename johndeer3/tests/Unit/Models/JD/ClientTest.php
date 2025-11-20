<?php

namespace Tests\Unit\Models\JD;

use App\Models\JD\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_full_name(): void
    {
        /** @var $client Client */
        $client = Client::query()->first();

        $this->assertEquals(
            $client->full_name,
            "{$client->customer_first_name} {$client->customer_last_name} {$client->customer_second_name}"
        );
    }

    /** @test */
    public function check_model_description_name(): void
    {
        /** @var $client Client */
        $client = Client::query()->first();

        $this->assertNull($client->modelDescriptionName());
    }
}
