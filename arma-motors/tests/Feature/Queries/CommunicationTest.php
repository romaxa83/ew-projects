<?php

namespace Tests\Feature\Queries;

use App\Types\Communication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class CommunicationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $response = $this->graphQL($this->getQueryStr())->assertOk();

        $responseData = $response->json('data.communications');

        $this->assertCount(3, $responseData);

        $this->assertEquals(Communication::TELEGRAM, $responseData[0]['key']);
        $this->assertEquals(__('translation.communication.telegram'), $responseData[0]['name']);

        $this->assertEquals(Communication::VIBER, $responseData[1]['key']);
        $this->assertEquals(__('translation.communication.viber'), $responseData[1]['name']);

        $this->assertEquals(Communication::PHONE, $responseData[2]['key']);
        $this->assertEquals(__('translation.communication.phone'), $responseData[2]['name']);
    }

    public static function getQueryStr(): string
    {
        return "{communications {key, name}}";
    }
}




