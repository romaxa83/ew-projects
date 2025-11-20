<?php

namespace Tests\Feature\Mutations\BackOffice\Sips;

use App\GraphQL\Mutations\BackOffice;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class DeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;

    public const MUTATION = BackOffice\Sips\SipDeleteMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->sipBuilder = resolve(SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
    }

    /** @test */
    public function success_delete(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
        ])
        ;

        $this->assertNull(Sip::find($model->id));
    }

    /** @test */
    public function fail_delete_with_employee(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();
        /** @var $employee Employee */
        $this->employeeBuilder->setSip($model)->create();

        $this->data['id'] = $model->id;

        $this->assertNotNull($model->employee);

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertExceptionMessage($res, __('exceptions.sip.cant_delete_exist_employee'));

        $this->assertNotNull(Sip::find($model->id));
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Sip */
        $model = $this->sipBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertUnauthorized($res);

        $this->assertNotNull(Sip::find($model->id));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertPermission($res);

        $this->assertNotNull(Sip::find($model->id));
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                )
            }',
            self::MUTATION,
            $id,
        );
    }
}
