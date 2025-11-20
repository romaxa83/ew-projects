<?php

namespace Tests\Feature\Api\Translations;

use App\Events\UpdateSysTranslations;
use App\Models\Translate;
use App\Models\Version;
use App\Services\Translations\TranslationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\TranslationBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class SetTranslationTest extends TestCase
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
    public function success_set()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();

        $this->assertNull(
            Translate::query()->where([
                ['alias', 'button'],
                ['lang', 'en'],
            ])->first()
        );
        $this->assertNull(
            Translate::query()->where([
                ['alias', 'button'],
                ['lang', 'ua'],
            ])->first()
        );
        $this->assertNull(
            Translate::query()->where([
                ['alias', 'text'],
                ['lang', 'en'],
            ])->first()
        );
        $this->assertNull(
            Translate::query()->where([
                ['alias', 'text'],
                ['lang', 'ua'],
            ])->first()
        );

        $this->assertNull(Version::query()->where('alias', Version::TRANSLATES)->first());

        $this->postJson(route('api.translate.set'), $data)
            ->assertJson($this->structureSuccessResponse(__('message.translate_set')))
        ;

        $this->assertNotNull(Version::query()->where('alias', Version::TRANSLATES)->first());

        $t_1 = Translate::query()->where([
            ['alias', 'button'],
            ['lang', 'en'],
        ])->first();
        $this->assertEquals($t_1->text, data_get($data, 'button.en'));
        $this->assertEquals($t_1->model, Translate::TYPE_SITE);

        $t_2 = Translate::query()->where([
            ['alias', 'button'],
            ['lang', 'ua'],
        ])->first();
        $this->assertEquals($t_2->text, data_get($data, 'button.ua'));
        $this->assertEquals($t_2->model, Translate::TYPE_SITE);

        $t_3 = Translate::query()->where([
            ['alias', 'text'],
            ['lang', 'en'],
        ])->first();
        $this->assertEquals($t_3->text, data_get($data, 'text.en'));
        $this->assertEquals($t_3->model, Translate::TYPE_SITE);

        $t_4 = Translate::query()->where([
            ['alias', 'text'],
            ['lang', 'ua'],
        ])->first();
        $this->assertEquals($t_4->text, data_get($data, 'text.ua'));
        $this->assertEquals($t_4->model, Translate::TYPE_SITE);
    }

    /** @test */
    public function success_update_or_create()
    {
        \Event::fake([UpdateSysTranslations::class]);

        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();

        $t_1 = $this->translationBuilder->setAlias('button')
            ->setLang('en')->create();
        $t_2 = $this->translationBuilder->setAlias('button')
            ->setLang('ua')->create();

        $this->assertNotEquals($t_1->text, data_get($data, 'button.en'));
        $this->assertNotEquals($t_2->text, data_get($data, 'button.ua'));

        $this->assertNull(
            Translate::query()->where([
                ['alias', 'text'],
                ['lang', 'en'],
            ])->first()
        );
        $this->assertNull(
            Translate::query()->where([
                ['alias', 'text'],
                ['lang', 'ua'],
            ])->first()
        );

        $this->postJson(route('api.translate.set'), $data)
            ->assertJson($this->structureSuccessResponse(__('message.translate_set')))
        ;

        $t_1->refresh();
        $t_2->refresh();

        $this->assertEquals($t_1->text, data_get($data, 'button.en'));
        $this->assertEquals($t_2->text, data_get($data, 'button.ua'));

        $t_3 = Translate::query()->where([
            ['alias', 'text'],
            ['lang', 'en'],
        ])->first();
        $this->assertEquals($t_3->text, data_get($data, 'text.en'));
        $this->assertEquals($t_3->model, Translate::TYPE_SITE);

        $t_4 = Translate::query()->where([
            ['alias', 'text'],
            ['lang', 'ua'],
        ])->first();
        $this->assertEquals($t_4->text, data_get($data, 'text.ua'));
        $this->assertEquals($t_4->model, Translate::TYPE_SITE);

        \Event::assertNotDispatched(UpdateSysTranslations::class);
    }

    /** @test */
    public function success_update_one_locale()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();

        $t_1 = $this->translationBuilder->setAlias('button')
            ->setLang('en')->create();
        $t_2 = $this->translationBuilder->setAlias('button')
            ->setLang('ua')
            ->withVersion()
            ->create();

        $this->assertNotEquals($t_1->text, data_get($data, 'button.en'));
        $this->assertNotEquals($t_2->text, data_get($data, 'button.ua'));

        $version = Version::query()->where('alias', Version::TRANSLATES)->first()->version;
        $this->assertNotNull($version);

        unset($data['button']['ua']);

        $this->postJson(route('api.translate.set'), $data)
            ->assertJson($this->structureSuccessResponse(__('message.translate_set')))
        ;

        $t_1->refresh();
        $t_2->refresh();

        $this->assertEquals($t_1->text, data_get($data, 'button.en'));
        $this->assertNotEquals($t_2->text, data_get($data, 'button.ua'));

        $versionNew = Version::query()->where('alias', Version::TRANSLATES)->first()->version;
        $this->assertNotNull($versionNew);
        $this->assertNotEquals($version, $versionNew);
    }

    /** @test */
    public function success_update_sys_translations()
    {
        \Event::fake([UpdateSysTranslations::class]);

        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = [
            'test::button' => [
                'en' => 'button en',
                'ua' => 'button ua'
            ],
            'text' => [
                'en' => 'text en',
                'ua' => 'text ua'
            ]
        ];

        $t_1 = $this->translationBuilder->setAlias('test::button')
            ->setLang('en')->create();
        $t_2 = $this->translationBuilder->setAlias('test::button')
            ->setLang('ua')->create();
        $t_3 = $this->translationBuilder->setAlias('text')
            ->setLang('en')->create();

        $this->assertNotEquals($t_1->text, data_get($data, 'test::button.en'));
        $this->assertNotEquals($t_2->text, data_get($data, 'test::button.ua'));
        $this->assertNotEquals($t_3->text, data_get($data, 'text.en'));

        $this->postJson(route('api.translate.set'), $data)
            ->assertJson($this->structureSuccessResponse(__('message.translate_set')))
        ;

        \Event::assertDispatched(UpdateSysTranslations::class);
        \Event::assertDispatched(UpdateSysTranslations::class, function ($event) use ($t_1, $t_2, $t_3){
            return in_array($t_1->id, $event->ids)
                && in_array($t_2->id, $event->ids)
                && !in_array($t_3->id, $event->ids)
                ;
        });
    }

    /** @test */
    public function success_empty()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->translationBuilder->setAlias('button')
            ->setLang('ua')
            ->withVersion()
            ->create();

        $version = Version::query()->where('alias', Version::TRANSLATES)->first()->version;
        $this->assertNotNull($version);

        $this->postJson(route('api.translate.set'), [])
            ->assertJson($this->structureSuccessResponse(__('message.translate_set')))
        ;

        $versionNew = Version::query()->where('alias', Version::TRANSLATES)->first()->version;
        $this->assertNotNull($versionNew);
        $this->assertEquals($version, $versionNew);
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(TranslationService::class, function(MockInterface $mock){
            $mock->shouldReceive("saveOrUpdate")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(route('api.translate.set'), [])
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function  not_auth()
    {
        $this->postJson(route('api.translate.set'), [])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }

    public static function data(): array
    {
        return [
            'button' => [
                'en' => 'button en',
                'ua' => 'button ua'
            ],
            'text' => [
                'en' => 'text en',
                'ua' => 'text ua'
            ]
        ];
    }
}


