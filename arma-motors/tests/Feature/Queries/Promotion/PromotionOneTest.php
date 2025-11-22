<?php

namespace Tests\Feature\Queries\Promotion;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\PromotionBuilder;
use Tests\Traits\Builders\SupportBuilder;

class PromotionOneTest extends TestCase
{
    use DatabaseTransactions;
    use PromotionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $model = $this->promotionBuilder()->create();

        $response = $this->graphQL($this->getQueryStr($model->id))
            ->assertOk();

        $responseData = $response->json('data.promotion');
        $this->assertNotEmpty($responseData);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('link', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('lang', $responseData['current']);

        $this->assertEquals($model->id, $responseData['id']);
        $this->assertEquals($model->current->name, $responseData['current']['name']);
    }

    public function getQueryStr(string $id): string
    {
        return  sprintf('{
            promotion(id: %s) {
                id
                active
                sort
                type
                link
                current {
                    name
                    lang
                }
               }
            }',
            $id
        );
    }
}

