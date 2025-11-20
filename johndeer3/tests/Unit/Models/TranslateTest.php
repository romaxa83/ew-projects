<?php

namespace Tests\Unit\Models;

use App\Models\Languages;
use App\Models\Translate;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\TranslationBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;

class TranslateTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;
    protected $translationBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->translationBuilder = resolve(TranslationBuilder::class);
    }

    /** @test */
    public function check_default_lang(): void
    {
        $this->assertEquals(Translate::defaultLang(), Languages::DEFAULT);
    }

    /** @test */
    public function check_entity_relation(): void
    {
        /** @var $user User */
        $user = $this->userBuilder->create();

        /** @var $model Translate */
        $model = $this->translationBuilder->setEntity($user)->create();

        $this->assertTrue($model->entity instanceof User);
        $this->assertEquals($model->entity->id, $user->id);
    }

    /** @test */
    public function check_order_by_group_keys(): void
    {
        $model_1 = $this->translationBuilder->setGroup('b')->create();
        $model_2 = $this->translationBuilder->setGroup('a')->create();

        $data = Translate::query()->orderByGroupKeys(false)->whereNotNull('group')->get();

        $this->assertEquals($data[0]->id, $model_1->id);
        $this->assertEquals($data[1]->id, $model_2->id);

        $data = Translate::query()->orderByGroupKeys(true)->whereNotNull('group')->get();

        $this->assertEquals($data[1]->id, $model_1->id);
        $this->assertEquals($data[0]->id, $model_2->id);
    }

    /** @test */
    public function check_order_by_alias_keys(): void
    {
        $model_1 = $this->translationBuilder->setAlias('b')->create();
        $model_2 = $this->translationBuilder->setAlias('a')->create();

        $data = Translate::query()->orderByGroupKeys(false)->whereNotNull('alias')->get();

        $this->assertEquals($data[0]->id, $model_1->id);
        $this->assertEquals($data[1]->id, $model_2->id);

        $data = Translate::query()->orderByGroupKeys(true)->whereNotNull('alias')->get();

        $this->assertEquals($data[1]->id, $model_1->id);
        $this->assertEquals($data[0]->id, $model_2->id);
    }
}

