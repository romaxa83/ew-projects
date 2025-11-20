<?php

namespace Tests\Feature\Mutations\BackOffice\Calls\Queue;

use App\GraphQL\Mutations\BackOffice;
use App\Models\Calls\Queue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Calls\QueueBuilder;
use Tests\TestCase;

class QueueUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected QueueBuilder $queueBuilder;

    protected array $data;

    public const MUTATION = BackOffice\Calls\Queue\QueueUpdateMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->queueBuilder = resolve(QueueBuilder::class);

        $this->data = [
            'name' => $this->faker->word,
            'case_id' => $this->faker->postcode,
            'serial_number' => $this->faker->postcode,
            'comment' => $this->faker->sentence
        ];
    }

    /** @test */
    public function success_update_as_super_admin(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Queue */
        $model = $this->queueBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;

        $this->assertNotEquals($model->caller_name, data_get($data, 'name'));
        $this->assertNotEquals($model->case_id, data_get($data, 'case_id'));
        $this->assertNotEquals($model->serial_number, data_get($data, 'serial_number'));
        $this->assertNotEquals($model->comment, data_get($data, 'comment'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'from_name' => data_get($data, 'name'),
                        'case_id' => data_get($data, 'case_id'),
                        'serial_number' => data_get($data, 'serial_number'),
                        'comment' => data_get($data, 'comment'),
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function success_update_as_employee(): void
    {
        $this->loginAsEmployee();

        /** @var $model Queue */
        $model = $this->queueBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;

        $this->assertNotEquals($model->caller_name, data_get($data, 'name'));
        $this->assertNotEquals($model->case_id, data_get($data, 'case_id'));
        $this->assertNotEquals($model->serial_number, data_get($data, 'serial_number'));
        $this->assertNotEquals($model->comment, data_get($data, 'comment'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'from_name' => data_get($data, 'name'),
                        'case_id' => data_get($data, 'case_id'),
                        'serial_number' => data_get($data, 'serial_number'),
                        'comment' => data_get($data, 'comment'),
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Queue */
        $model = $this->queueBuilder->create();
        $data = $this->data;
        $data['id'] = $model->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
        ;

        $this->assertUnauthorized($res);
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        /** @var $model Queue */
        $model = $this->queueBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
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
                    id: %s
                    input: {
                        from_name: "%s"
                        case_id: "%s"
                        serial_number: "%s"
                        comment: "%s"
                    },
                ) {
                    id
                    case_id
                    from_name
                    serial_number
                    comment
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'name'),
            data_get($data, 'case_id'),
            data_get($data, 'serial_number'),
            data_get($data, 'comment'),
        );
    }
}
