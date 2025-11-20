<?php

namespace Tests\Feature\Mutations\BackOffice\Sips;

use App\GraphQL\Mutations\BackOffice;
use App\Models\Sips\Sip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class CreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected SipBuilder $sipBuilder;

    protected array $data;

    public const MUTATION = BackOffice\Sips\SipCreateMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->sipBuilder = resolve(SipBuilder::class);

        $this->data = [
            'number' => '333',
            'password' => 'Password1324234',
        ];
    }

    /** @test */
    public function success_create(): void
    {
        $this->loginAsSuperAdmin();

        $id = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
            ->assertJsonStructure([
                'data' => [
                    self::MUTATION => [
                        'id',
                        'created_at',
                        'updated_at',
                    ],
                ]
            ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'number' => data_get($this->data, 'number'),
                    ],
                ]
            ])
            ->json('data.'.self::MUTATION.'.id')
        ;

        /** @var $model Sip */
        $model = Sip::find($id);

        $this->assertEquals($model->password, data_get($this->data, 'password'));
    }

    /** @test */
    public function fail_create_not_uniq_number(): void
    {
        $this->loginAsSuperAdmin();

        $this->data['password'] = 'password';

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        $field = 'input.password';
        $this->assertResponseHasValidationMessage($res, $field,[
            __('validation.custom.password.password-rule', ['min' => Sip::MIN_LENGTH_PASSWORD])
        ]);
    }

    /** @test */
    public function fail_create_not_numeric_number(): void
    {
        $this->loginAsSuperAdmin();

        $this->data['number'] = '43553jjkj';

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])

        ;

        $field = 'input.number';
        $this->assertResponseHasValidationMessage($res, $field,[
            'The sip number must be a number.',
            'The sip number must be between 3 and 10 digits.',
        ]);
    }

    /** @test */
    public function fail_wrong_password(): void
    {
        $this->loginAsSuperAdmin();

        $number = '456';
        $this->sipBuilder->setData(['number' => $number])->create();

        $this->data['number'] = $number;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        $field = 'input.number';
        $this->assertResponseHasValidationMessage($res, $field,[
            __('validation.unique', ['attribute' => 'sip number'])
        ]);
    }

    /** @test */
    public function not_auth(): void
    {
        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        $this->assertUnauthorized($res);
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        $this->assertPermission($res);
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    input: {
                        number: "%s"
                        password: "%s"
                    },
                ) {
                    id
                    number
                    created_at
                    updated_at
                }
            }',
            self::MUTATION,
            data_get($data, 'number'),
            data_get($data, 'password'),
        );
    }
}
