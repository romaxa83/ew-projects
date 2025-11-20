<?php

namespace Tests\Unit\Service\Page;

use App\DTO\Page\PageDto;
use App\DTO\SimpleTranslationDto;
use App\Models\Page\Page;
use App\Services\PageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\DTO\SimpleTranslationDtoTest;

class PageServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success(): void
    {
        $trans_en = SimpleTranslationDtoTest::data();
        $trans_ua = SimpleTranslationDtoTest::data("ua");

        $dto = PageDto::byArgs([
            "type" => "some type",
            "active" => true,
            "translations" => [
                "ua" => $trans_ua,
                "en" => $trans_en
            ]
        ]);

        $this->assertNull(Page::query()->where('alias', $dto->type)->first());

        app(PageService::class)->create($dto);

        /** @var $model Page */
        $model = Page::query()->where('alias', $dto->type)->first();

        $this->assertEquals($model->alias, $dto->type);
        $this->assertTrue($model->active, $dto->type);

        foreach ($dto->getTranslations() as $item){
            /** @var $item SimpleTranslationDto */
            $t = $model->translations->where('lang', $item->lang)->first();
            $this->assertEquals($t->name , $item->name);
            $this->assertEquals($t->text , $item->text);
        }
    }
}


