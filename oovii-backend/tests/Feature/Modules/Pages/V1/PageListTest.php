<?php

namespace Tests\Feature\Modules\Pages\V1;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Pages\Models\Page;

class PageListTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    private $userBuilder;
    private $smsVerifyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success(): void
    {
        $count = 4;
        Page::factory($count)->create();

        $this->getJson(route('api.v1.mobile.pages.list'))
            ->assertOk()
            ->assertJsonCount($count, 'data')
            ->assertJsonStructure($this->structureResource([
                "*" => [
                    "id",
                    "type",
                    "title",
                    "text",
                    "locale",
                ]
            ]));
    }
}


