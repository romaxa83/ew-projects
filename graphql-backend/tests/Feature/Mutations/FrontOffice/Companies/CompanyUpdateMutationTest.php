<?php

namespace Tests\Feature\Mutations\FrontOffice\Companies;

use App\GraphQL\Mutations\FrontOffice\Companies\CompanyUpdateMutation;
use App\Models\Companies\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\CompanyManagerHelperTrait;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class CompanyUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;
    use CompanyManagerHelperTrait;

    public const MUTATION = CompanyUpdateMutation::NAME;

    protected array $data = [];

    public function test_cant_update_company_by_simple_user(): void
    {
        $this->loginAsUser();

        $this->test_cant_update_company_by_not_auth_user();
    }

    public function test_cant_update_company_by_not_auth_user(): void
    {
        Company::factory()->create();

        $result = $this->query();

        $this->assertGraphQlUnauthorized($result);
    }

    protected function query(): TestResponse
    {
        $query = sprintf(
            'mutation {
                %s (
                    name: "%s"
                    lang: "%s"
                ) {
                    id
                    name
                }
            }',
            self::MUTATION,
            $this->data['name'],
            $this->data['lang']
        );

        return $this->postGraphQL(['query' => $query]);
    }

    public function test_user_with_permissions_can_update_company(): void
    {
        $manager = $this->loginAsCompanyManager();
        $updatingCompany = $manager->company;

        $this->assertDatabaseMissing(
            Company::TABLE,
            [
                'name' => $this->data['name']
            ]
        );

        $result = $this->query();
        $updatedCompany = $result->json('data.' . self::MUTATION);

        self::assertNotNull($updatingCompany['id']);
        self::assertEquals($this->data['name'], $updatedCompany['name']);

        $this->assertDatabaseHas(
            Company::TABLE,
            [
                'id' => $updatingCompany->id,
                'name' => $this->data['name'],
                'lang' => $this->data['lang']
            ]
        );
    }

    public function test_it_returns_nonexistent_lang_validation_message(): void
    {
        $this->loginAsCompanyManager();
        $this->data['lang'] = 'fr';

        $result = $this->query();

        $this->assertResponseHasValidationMessage($result, 'lang', [__('validation.exists', ['attribute' => 'lang'])]);
    }

    public function test_only_manager_from_the_same_company_can_update_company(): void
    {
        $manager = $this->loginAsCompanyManager();

        $this->query()
            ->assertOk();

        $this->assertDatabaseHas(
            Company::TABLE,
            [
                'id' => $manager->company->id,
                'name' => $this->data['name']
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'name' => 'Wezom',
            'lang' => 'en'
        ];
    }
}
