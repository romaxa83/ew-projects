<?php

namespace Tests\Unit\Service\Locale\Language;

use App\DTO\Locale\LanguageDTO;
use App\Services\Locale\LanguageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success(): void
    {
        $dto = LanguageDTO::byArgs([
            "name" => 'name',
            "native" => 'native',
            "slug" => 'nt',
            "locale" => 'nt',
            "default" => false,
        ]);

        $service = app(LanguageService::class);

        $model = $service->create($dto);

        $this->assertEquals($model->name, $dto->name);
        $this->assertEquals($model->native, $dto->native);
        $this->assertEquals($model->slug, $dto->slug);
        $this->assertEquals($model->locale, $dto->locale);
        $this->assertEquals($model->default, $dto->default);
    }
}

