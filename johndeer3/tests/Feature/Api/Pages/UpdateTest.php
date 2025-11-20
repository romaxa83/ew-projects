<?php

namespace Tests\Feature\Api\Pages;

use App\Models\Page\Page;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\PageBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $pageBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->pageBuilder = resolve(PageBuilder::class);
    }

    /** @test */
    public function success()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $model = $this->pageBuilder
            ->setAlias(Page::ALIAS_AGREEMENT)
            ->withTranslations('en', 'ua')
            ->create();

        $data = self::data();

        $this->assertNotEquals(
            $model->translations->where('lang', 'en')->first()->name,
            data_get($data, 'name.en')
        );
        $this->assertNotEquals(
            $model->translations->where('lang', 'en')->first()->text,
            data_get($data, 'text.en')
        );
        $this->assertNotEquals(
            $model->translations->where('lang', 'ua')->first()->name,
            data_get($data, 'name.ua')
        );
        $this->assertNotEquals(
            $model->translations->where('lang', 'ua')->first()->text,
            data_get($data, 'text.ua')
        );

        $this->postJson(route('api.page.update', ['alias' => Page::ALIAS_AGREEMENT]),$data)
            ->assertJson($this->structureSuccessResponse([
                'en' => [
                    'name' => data_get($data, 'name.en'),
                    'text' => data_get($data, 'text.en'),
                ],
                'ua' => [
                    'name' => data_get($data, 'name.ua'),
                    'text' => data_get($data, 'text.ua'),
                ],
            ]))
            ->assertJsonCount(2, 'data')
        ;

        $model->refresh();

        $this->assertEquals(
            $model->translations->where('lang', 'en')->first()->name,
            data_get($data, 'name.en')
        );
        $this->assertEquals(
            $model->translations->where('lang', 'en')->first()->text,
            data_get($data, 'text.en')
        );
        $this->assertEquals(
            $model->translations->where('lang', 'ua')->first()->name,
            data_get($data, 'name.ua')
        );
        $this->assertEquals(
            $model->translations->where('lang', 'ua')->first()->text,
            data_get($data, 'text.ua')
        );
    }

    /** @test */
    public function fail_without_name()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();
        unset($data['name']);

        $this->pageBuilder
            ->setAlias(Page::ALIAS_AGREEMENT)
            ->withTranslations('en')
            ->create();

        $this->postJson(route('api.page.update', ['alias' => Page::ALIAS_AGREEMENT]),$data)
            ->assertJson($this->structureErrorResponse(["The name field is required."]))
        ;
    }

    /** @test */
    public function fail_without_text()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();
        unset($data['text']);

        $this->pageBuilder
            ->setAlias(Page::ALIAS_AGREEMENT)
            ->withTranslations('en')
            ->create();

        $this->postJson(route('api.page.update', ['alias' => Page::ALIAS_AGREEMENT]),$data)
            ->assertJson($this->structureErrorResponse(["The text field is required."]))
        ;
    }

    /** @test */
    public function fail_wrong_alias()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();

        $this->pageBuilder
            ->setAlias(Page::ALIAS_AGREEMENT)
            ->withTranslations('en')
            ->create();

        $this->postJson(route('api.page.update', ['alias' => 'wrong']),$data)
            ->assertJson($this->structureErrorResponse(__("message.exceptions.not found", [
                'field' => 'alias',
                'value' => 'wrong',
            ])))
        ;
    }

    /** @test */
    public function success_if_not_locale_into_db()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = self::data();

        $this->pageBuilder
            ->setAlias(Page::ALIAS_AGREEMENT)
            ->withTranslations('en')
            ->create();

        $this->postJson(route('api.page.update', ['alias' => Page::ALIAS_AGREEMENT]),$data)
            ->assertJson($this->structureSuccessResponse([
                'en' => [
                    'name' => data_get($data, 'name.en'),
                    'text' => data_get($data, 'text.en'),
                ],
            ]))
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function not_admin()
    {
        $user = $this->userBuilder->setRole(
            Role::query()->where('role', Role::ROLE_PSS)->first()
        )->create();
        $this->loginAsUser($user);

        $this->pageBuilder
            ->setAlias(Page::ALIAS_AGREEMENT)
            ->withTranslations('en', 'ua')
            ->create();

        $data = self::data();

        $this->postJson(route('api.page.update', ['alias' => Page::ALIAS_AGREEMENT]),$data)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->pageBuilder
            ->setAlias(Page::ALIAS_AGREEMENT)
            ->withTranslations('en', 'ua')
            ->create();

        $data = self::data();

        $this->postJson(route('api.page.update', ['alias' => Page::ALIAS_AGREEMENT]),$data)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }

    public static function data(): array
    {
        return [
            'name' => [
                'en' => 'name en',
                'ua' => 'name ua',
            ],
            'text' => [
                'en' => 'text en',
                'ua' => 'text ua',
            ],
        ];
    }
}



