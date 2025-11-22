<?php

namespace Tests\Feature\Http\Api\V1\Localizations\Translation;

use App\Foundations\Enums\CacheKeyEnum;
use App\Foundations\Modules\Localization\Enums\Translations\TranslationPlace;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\Builders\Localization\TranslationBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ListTest extends TestCase
{
    use DatabaseTransactions;

    protected TranslationBuilder $translationBuilder;
    protected UserBuilder $userBuilder;

    protected $data;

    public function setUp(): void
    {
        parent::setUp();

        $this->translationBuilder = resolve(TranslationBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);

//        Cache::tags(CacheKeyEnum::Translations->value)->flush();
    }

    /** @test */
    public function success_list_as_default_lang()
    {
        $t_1 = $this->translationBuilder
            ->lang('en')
            ->key('button.create')
            ->place(TranslationPlace::SITE)
            ->text('create')
            ->create();
        $t_2 = $this->translationBuilder
            ->lang('en')
            ->key('button.update')
            ->place(TranslationPlace::SITE)
            ->text('update')
            ->create();
        $t_3 = $this->translationBuilder
            ->lang('ru')
            ->key('button.delete')
            ->place(TranslationPlace::SITE)
            ->text('delete')
            ->create();
        $t_4 = $this->translationBuilder
            ->lang('ru')
            ->key('button.create')
            ->place(TranslationPlace::SITE)
            ->text('create')
            ->create();

        $this->getJson(route('api.v1.localization.translations'))
            ->assertJson([
                'data' => [
                    'button.create' => 'create',
                    'button.update' => 'update'
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_list_as_filter_lang()
    {
        $t_1 = $this->translationBuilder
            ->lang('en')
            ->key('button.create')
            ->place(TranslationPlace::SITE)
            ->text('create')
            ->create();
        $t_2 = $this->translationBuilder
            ->lang('en')
            ->key('button.update')
            ->place(TranslationPlace::SITE)
            ->text('update')
            ->create();
        $t_3 = $this->translationBuilder
            ->lang('ru')
            ->key('button.delete')
            ->place(TranslationPlace::SITE)
            ->text('delete')
            ->create();
        $t_4 = $this->translationBuilder
            ->lang('ru')
            ->key('button.create')
            ->place(TranslationPlace::SITE)
            ->text('create')
            ->create();

        $this->getJson(route('api.v1.localization.translations', ['lang' => 'ru']))
            ->assertJson([
                'data' => [
                    'button.delete' => 'delete',
                    'button.create' => 'create',
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_list_as_user_lang()
    {
        $model = $this->userBuilder->lang('uk')->create();
        $this->loginUserAsAdmin($model);

        $t_1 = $this->translationBuilder
            ->lang('en')
            ->key('button.create')
            ->place(TranslationPlace::SITE)
            ->text('create')
            ->create();
        $t_2 = $this->translationBuilder
            ->lang('en')
            ->key('button.update')
            ->place(TranslationPlace::SITE)
            ->text('update')
            ->create();
        $t_3 = $this->translationBuilder
            ->lang('uk')
            ->key('button.delete')
            ->place(TranslationPlace::SITE)
            ->text('delete')
            ->create();
        $t_4 = $this->translationBuilder
            ->lang('ru')
            ->key('button.create')
            ->place(TranslationPlace::SITE)
            ->text('create')
            ->create();

        $this->getJson(route('api.v1.localization.translations'))
            ->assertJson([
                'data' => [
                    'button.delete' => 'delete',
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_empty()
    {
        $this->getJson(route('api.v1.localization.translations'))
            ->assertJson([
                'data' => []
            ])
            ->assertJsonCount(0, 'data')
        ;
    }
}
