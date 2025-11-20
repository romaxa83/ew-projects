<?php

namespace Tests\Feature\Api\Translations;

use App\Repositories\TranslationRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\TranslationBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class GetTranslationsTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $translationBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->translationBuilder = resolve(TranslationBuilder::class);
    }

    /** @test */
    public function success()
    {
        $t_1 = $this->translationBuilder->setAlias('button')->setLang('ua')->create();
        $t_2 = $this->translationBuilder->setAlias('button')->setLang('en')->create();
        $t_3 = $this->translationBuilder->setAlias('text')->setLang('ua')->create();
        $t_4 = $this->translationBuilder->setAlias('text')->setLang('en')->create();

        $this->assertEquals(\App::getLocale(), 'en');

        $this->getJson(route('api.translate.get'))
            ->assertJson($this->structureSuccessResponse([
                \App::getLocale() => [
                    'button' => $t_2->text,
                    'text' => $t_4->text,
                ]
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.en')
        ;
    }

    /** @test */
    public function success_local_as_header()
    {
        $t_1 = $this->translationBuilder->setAlias('button')->setLang('ua')->create();
        $t_2 = $this->translationBuilder->setAlias('button')->setLang('en')->create();
        $t_3 = $this->translationBuilder->setAlias('text')->setLang('ua')->create();
        $t_4 = $this->translationBuilder->setAlias('text')->setLang('en')->create();

        $this->assertEquals(\App::getLocale(), 'en');

        $this->getJson(route('api.translate.get'),[
            'Content-Language' => 'ua'
        ])
            ->assertJson($this->structureSuccessResponse([
                'ua' => [
                    'button' => $t_1->text,
                    'text' => $t_3->text,
                ]
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.ua')
        ;
    }

    /** @test */
    public function success_local_as_user_lang()
    {
        $user = $this->userBuilder->setLang('ua')->create();
        $this->loginAsUser($user);

        $t_1 = $this->translationBuilder->setAlias('button')->setLang('ua')->create();
        $t_2 = $this->translationBuilder->setAlias('button')->setLang('en')->create();
        $t_3 = $this->translationBuilder->setAlias('text')->setLang('ua')->create();
        $t_4 = $this->translationBuilder->setAlias('text')->setLang('en')->create();

        $this->assertEquals($user->lang, 'ua');

        $this->getJson(route('api.translate.get'),[
            'Content-Language' => 'ua'
            ])
            ->assertJson($this->structureSuccessResponse([
                'ua' => [
                    'button' => $t_1->text,
                    'text' => $t_3->text,
                ]
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.ua')
        ;
    }

    /** @test */
    public function success_by_alias()
    {
        $t_1 = $this->translationBuilder->setAlias('button')->setLang('ua')->create();
        $t_2 = $this->translationBuilder->setAlias('button')->setLang('en')->create();
        $t_3 = $this->translationBuilder->setAlias('text')->setLang('ua')->create();
        $t_4 = $this->translationBuilder->setAlias('text')->setLang('en')->create();

        $this->getJson(route('api.translate.get', ['key' => 'text']))
            ->assertJson($this->structureSuccessResponse([
                'en' => [
                    'text' => $t_4->text,
                ]
            ]))
            ->assertJsonCount(1, 'data.en')
        ;
    }

    /** @test */
    public function success_by_few_locale()
    {
        $t_1 = $this->translationBuilder->setAlias('button')->setLang('ua')->create();
        $t_2 = $this->translationBuilder->setAlias('button')->setLang('en')->create();
        $t_3 = $this->translationBuilder->setAlias('text')->setLang('ua')->create();
        $t_4 = $this->translationBuilder->setAlias('text')->setLang('en')->create();

        $this->getJson(route('api.translate.get', ['lang' => 'ua,en']))
            ->assertJson($this->structureSuccessResponse([
                'ua' => [
                    'button' => $t_1->text,
                    'text' => $t_3->text,
                ],
                'en' => [
                    'button' => $t_2->text,
                    'text' => $t_4->text,
                ]
            ]))
            ->assertJsonCount(2, 'data.en')
            ->assertJsonCount(2, 'data.ua')
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_wrong_key()
    {
        $t_1 = $this->translationBuilder->setAlias('button')->setLang('ua')->create();
        $t_2 = $this->translationBuilder->setAlias('button')->setLang('en')->create();
        $t_3 = $this->translationBuilder->setAlias('text')->setLang('ua')->create();
        $t_4 = $this->translationBuilder->setAlias('text')->setLang('en')->create();

        $this->getJson(route('api.translate.get', ['key' => 'wrong']))
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_wrong_lang()
    {
        $t_1 = $this->translationBuilder->setAlias('button')->setLang('ua')->create();
        $t_2 = $this->translationBuilder->setAlias('button')->setLang('en')->create();
        $t_3 = $this->translationBuilder->setAlias('text')->setLang('ua')->create();
        $t_4 = $this->translationBuilder->setAlias('text')->setLang('en')->create();

        $this->getJson(route('api.translate.get', ['lang' => 'de']))
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_most_lang_in_query()
    {
        $user = $this->userBuilder->setLang('ua')->create();
        $this->loginAsUser($user);

        $t_1 = $this->translationBuilder->setAlias('button')->setLang('ua')->create();
        $t_2 = $this->translationBuilder->setAlias('button')->setLang('en')->create();
        $t_3 = $this->translationBuilder->setAlias('text')->setLang('ua')->create();
        $t_4 = $this->translationBuilder->setAlias('text')->setLang('en')->create();

        $this->getJson(route('api.translate.get', ['lang' => 'de']),[
            'Content-Language' => 'en'
        ])
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(TranslationRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAllAsArray")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.translate.get', ['lang' => 'de']))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }
}
