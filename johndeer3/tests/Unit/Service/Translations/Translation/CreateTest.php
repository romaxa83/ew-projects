<?php

namespace Tests\Unit\Service\Translations\Translation;

use App\DTO\Locale\TranslationDTO;
use App\Models\Translate;
use App\Services\Translations\TranslationService;
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
        $dto = TranslationDTO::byArgs([
            "model" => "some model",
            "entity_type" => "some model",
            "entity_id" => 1,
            "text" => "some text",
            "lang" => "en",
            "alias" => "some alias",
            "group" => "some group",
        ]);

        $this->assertNull(Translate::query()->where('alias', $dto->alias)->first());

        app(TranslationService::class)->create($dto);

        /** @var $model Translate */
        $model = Translate::query()->where('alias', $dto->alias)->first();

        $this->assertEquals($model->model, $dto->model);
        $this->assertEquals($model->entity_type, $dto->entity_type);
        $this->assertEquals($model->entity_id, $dto->entity_id);
        $this->assertEquals($model->text, $dto->text);
        $this->assertEquals($model->lang, $dto->lang);
        $this->assertEquals($model->alias, $dto->alias);
        $this->assertEquals($model->group, $dto->group);
    }

    /** @test */
    public function success_required_field(): void
    {
        $dto = TranslationDTO::byArgs([
            "model" => "some model",
            "text" => "some text",
            "lang" => "en",
        ]);

        $this->assertNull(Translate::query()->where('text', $dto->text)->first());

        app(TranslationService::class)->create($dto);

        /** @var $model Translate */
        $model = Translate::query()->where('text', $dto->text)->first();

        $this->assertEquals($model->model, $dto->model);
        $this->assertEquals($model->text, $dto->text);
        $this->assertEquals($model->lang, $dto->lang);
        $this->assertNull($model->entity_type);
        $this->assertNull($model->entity_id);
        $this->assertNull($model->alias);
        $this->assertNull($model->group);
    }
}


