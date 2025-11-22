<?php

namespace Tests\Feature\Queries\Recommendation;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\RecommendationBuilder;

class RecommendationPaginatorTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use RecommendationBuilder;

    const QUERY = 'recommendations';

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->create();
        $this->loginAsAdmin($admin);

        $this->recommendationBuilder()->create();
        $this->recommendationBuilder()->create();
        $this->recommendationBuilder()->create();

        $res = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $this->assertArrayHasKey('id', Arr::get($res, "data.".self::QUERY.".data.0"));
        $this->assertEquals(3,  Arr::get($res, "data.".self::QUERY.".paginatorInfo.total"));
    }

    /** @test */
    public function list_empty()
    {
        $admin = $this->adminBuilder()
            ->create();
        $this->loginAsAdmin($admin);

        $res = $this->graphQL($this->getQueryStr());
        $this->assertEmpty( Arr::get($res, "data.".self::QUERY.".data"));
    }

    public function getQueryStr(): string
    {
        return  sprintf('{
            %s {
                data{
                    id
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }',
        self::QUERY
        );
    }
}
