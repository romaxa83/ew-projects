<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Features;

use App\GraphQL\Mutations\BackOffice\Catalog\Features\Features\FeatureCreateMutation;
use App\Models\Admins\Admin;
use App\Permissions\Catalog\Features\Features;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\ValueBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;
use Tests\Unit\Dto\Catalog\FeatureDtoTest;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = FeatureCreateMutation::NAME;
    protected array $data = [];

    protected ValueBuilder $builderValue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builderValue = app(ValueBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $this->loginByAdminManager([Features\CreatePermission::KEY]);

        $data = FeatureDtoTest::data();
        $data['active'] = 'true';

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $resData = $res->json(sprintf('data.%s', self::MUTATION));

        $this->assertArrayHasKey('id', $resData);
        $this->assertArrayHasKey('active', $resData);
        $this->assertArrayHasKey('sort', $resData);
        $this->assertArrayHasKey('values', $resData);
        $this->assertArrayHasKey('translation', $resData);
        $this->assertArrayHasKey('id', Arr::get($resData, 'translation'));
        $this->assertArrayHasKey('title', Arr::get($resData, 'translation'));
        $this->assertArrayHasKey('language', Arr::get($resData, 'translation'));
        $this->assertArrayHasKey('description', Arr::get($resData, 'translation'));
        $this->assertArrayHasKey('translations', $resData);
        $this->assertArrayHasKey('id', Arr::get($resData, 'translations.0'));
        $this->assertArrayHasKey('title', Arr::get($resData, 'translations.0'));
        $this->assertArrayHasKey('language', Arr::get($resData, 'translations.0'));
        $this->assertArrayHasKey('description', Arr::get($resData, 'translations.0'));
    }

    public function test_create_features_for_web(): void
    {
        $this->loginByAdminManager([Features\CreatePermission::KEY]);

        $data = FeatureDtoTest::data();
        $data['active'] = 'true';
        $data['display_in_web'] = 'true';

        $this->assertResponseHasNoValidationErrors(
            $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)])
        );
        $this->assertResponseHasNoValidationErrors(
            $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)])
        );
        $this->assertResponseHasNoValidationErrors(
            $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)])
        );

        $this->assertServerError($this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]), 'validation');
    }

    /** @test */
    public function success_without_values(): void
    {
        $this->loginByAdminManager([Features\CreatePermission::KEY]);

        $data = FeatureDtoTest::data();
        $data['active'] = 'true';

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $resData = $res->json(sprintf('data.%s', self::MUTATION));

        $this->assertArrayHasKey('values', $resData);
        $this->assertEmpty(Arr::get($resData, 'values'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginByAdminManager([Features\UpdatePermission::KEY]);

        $data = FeatureDtoTest::data();
        $data['active'] = 'true';

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('No permission', $res->json('errors.0.message'));
    }

    protected function loginByAdminManager(array $permissionKey): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', $permissionKey, Admin::GUARD)
            );
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                %s (
                    active: %s,
                    display_in_web: %s,
                    translations: [
                        {language: "%s", title: "%s"}
                        {language: "%s", title: "%s"}
                    ]
                ) {
                    id
                    sort
                    active
                    values {
                        id
                    }
                    translation {
                        id
                        title
                        language
                        description
                    }
                    translations {
                        id
                        title
                        language
                        description
                    }
                }
            }',
            self::MUTATION,
            $data['active'],
            $data['display_in_web'] ?? 'false',
            $data['translations']['es']['language'],
            $data['translations']['es']['title'],
            $data['translations']['en']['language'],
            $data['translations']['en']['title'],
        );
    }
}

