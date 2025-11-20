<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminToggleActiveMutation;
use App\Models\Admins\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Admins\AdminBuilder;
use Tests\TestCase;

class AdminToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected AdminBuilder $adminBuilder;

    public const MUTATION = AdminToggleActiveMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();
        $this->adminBuilder = resolve(AdminBuilder::class);
    }

    /** @test */
    public function success_toggle_to_false(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Admin */
        $model = $this->adminBuilder->create();

        $this->assertTrue($model->isActive());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'active' => false
                    ],
                ]
            ]);

        $model->refresh();

        $this->assertFalse($model->isActive());
    }

    /** @test */
    public function success_toggle_to_true(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Admin */
        $model = $this->adminBuilder->setData(['active' => false])->create();

        $this->assertFalse($model->isActive());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $model->id,
                            'active' => true
                        ],
                    ]
                ]
            );

        $model->refresh();

        $this->assertTrue($model->isActive());
    }

    /** @test */
    public function fail_toggle_on_super_admin(): void
    {
        /** @var $model Admin */
        $model = $this->loginAsSuperAdmin();

        $this->assertTrue($model->isActive());

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertErrorMessage($res, __('exceptions.admin.cant_action_on_super_admin'));
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Admin */
        $model = $this->adminBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertUnauthorized($res);
    }

    /** @test */
    public function not_perm(): void
    {
        /** @var $model Admin */
        $model = $this->adminBuilder->create();

        $this->loginAsAdmin();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertPermission($res);
    }

    protected function getQueryStr(int $id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                ) {
                    id
                    active
                }
            }',
            self::MUTATION,
            $id,
        );
    }
}


