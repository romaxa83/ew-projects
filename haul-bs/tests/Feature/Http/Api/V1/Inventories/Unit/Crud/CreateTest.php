<?php

namespace Feature\Http\Api\V1\Inventories\Unit\Crud;

use App\Models\Inventories\Unit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'name' => 'cm2',
            'accept_decimals' => false,
        ];
    }

    /** @test */
    public function success_create()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.unit.store'), $data)
            ->assertJson([
                'data' => [
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

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.inventories.unit.store'), $data, [
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
    public function field_success_role_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.unit.store'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $this->assertDatabaseEmpty(Unit::TABLE);
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.unit.store'), $data)
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
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.unit.store'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.unit.store'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
