<?php

namespace Feature\Http\Api\V1\Inventories\Unit\Crud;

use App\Models\Inventories\Unit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\UnitBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected UnitBuilder $unitBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->unitBuilder = resolve(UnitBuilder::class);

        $this->data = [
            'name' => 'cm2',
            'accept_decimals' => true,
        ];
    }

    /** @test */
    public function success_update()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $data = $this->data;

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->accept_decimals, data_get($data, 'accept_decimals'));

        $this->postJson(route('api.v1.inventories.unit.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => data_get($data, 'name'),
                    'accept_decimals' => data_get($data, 'accept_decimals'),
                ],
            ])
        ;
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.inventories.unit.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'name'
        );
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.unit.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $model->refresh();

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->accept_decimals, data_get($data, 'accept_decimals'));
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.unit.update', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['name', null, 'validation.required', ['attribute' => 'validation.attributes.name']],
            ['accept_decimals', null, 'validation.required', ['attribute' => 'validation.attributes.accept_decimals']],
            ['accept_decimals', 22, 'validation.boolean', ['attribute' => 'validation.attributes.accept_decimals']],
            ['accept_decimals', 'true', 'validation.boolean', ['attribute' => 'validation.attributes.accept_decimals']],
        ];
    }

    /** @test */
    public function not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.unit.update', ['id' => 0]), $data);

        self::assertErrorMsg($res, __("exceptions.inventories.unit.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $res = $this->postJson(route('api.v1.inventories.unit.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $res = $this->postJson(route('api.v1.inventories.unit.update', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
