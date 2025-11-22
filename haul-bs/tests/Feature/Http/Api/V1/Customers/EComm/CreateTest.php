<?php

namespace Tests\Feature\Http\Api\V1\Customers\EComm;

use App\Enums\Customers\CustomerType;
use App\Enums\Tags\TagType;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->tagBuilder = resolve(TagBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);

        $this->data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
        ];
    }

    /** @test */
    public function success_create_without_tag()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->assertFalse(Tag::query()->where('name', Tag::ECOM_NAME_TAG)->exists());

        $this->postJsonEComm(route('api.v1.e_comm.customers.store'), $data)
            ->assertJson([
                'data' => [
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'email' => data_get($data, 'email'),
                    'type' => CustomerType::EComm(),
                    'phone' => null,
                    'phone_extension' => null,
                    'phones' => [],
                    'notes' => null,
                    'attachments' => [],
                    'tags' => [
                        [
                            'name' => Tag::ECOM_NAME_TAG
                        ]
                    ],
                    'hasRelatedEntities' => false,
                    'trucks' => [],
                    'trailers' => [],
                    'has_ecommerce_account' => true
                ],
            ])
            ->assertJsonCount(0, 'data.phones')
            ->assertJsonCount(0, 'data.attachments')
            ->assertJsonCount(0, 'data.trucks')
            ->assertJsonCount(0, 'data.trailers')
            ->assertJsonCount(1, 'data.tags')
        ;

        $this->assertTrue(Tag::query()->where('name', Tag::ECOM_NAME_TAG)->exists());
    }

    /** @test */
    public function success_create_with_tag()
    {
        $this->loginUserAsSuperAdmin();

        $tag = $this->tagBuilder
            ->name(Tag::ECOM_NAME_TAG)
            ->type(TagType::CUSTOMER())
            ->create();

        $data = $this->data;

        $this->postJsonEComm(route('api.v1.e_comm.customers.store'), $data)
            ->assertJson([
                'data' => [
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'email' => data_get($data, 'email'),
                    'type' => CustomerType::EComm(),
                    'tags' => [
                        [
                            'id' => $tag->id,
                            'name' => Tag::ECOM_NAME_TAG
                        ]
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data.tags')
        ;
    }

    /** @test */
    public function success_create_if_exists_customer()
    {
        $this->loginUserAsSuperAdmin();

        $email = 'customer@example.com';
        /** @var $customer Customer */
        $customer = $this->customerBuilder->email($email)->create();

        $data = $this->data;
        $data['email'] = $email;

        $this->assertNotNull($customer->first_name, $data['first_name']);
        $this->assertNotNull($customer->last_name, $data['last_name']);
        $this->assertFalse($customer->has_ecommerce_account);

        $this->postJsonEComm(route('api.v1.e_comm.customers.store'), $data)
            ->assertJson([
                'data' => [
                    'id' => $customer->id,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'has_ecommerce_account' => true,
                ],
            ])
        ;
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $this->customerBuilder
            ->email('test@test.com')
            ->phone('11111111111')
            ->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJsonEComm(route('api.v1.e_comm.customers.store'), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['first_name', null, 'validation.required', ['attribute' => 'validation.attributes.first_name']],
            ['first_name', 1111, 'validation.string', ['attribute' => 'validation.attributes.first_name']],
            ['first_name', '1111', 'validation.alpha', ['attribute' => 'validation.attributes.first_name']],
            ['last_name', null, 'validation.required', ['attribute' => 'validation.attributes.last_name']],
            ['last_name', 1111, 'validation.string', ['attribute' => 'validation.attributes.last_name']],
            ['last_name', '1111', 'validation.alpha', ['attribute' => 'validation.attributes.last_name']],
            ['email', null, 'validation.required', ['attribute' => 'validation.attributes.email']],
            ['email', 'wrong', 'validation.email', ['attribute' => 'validation.attributes.email']],
//            ['email', 'test@test.com', 'validation.unique', ['attribute' => 'validation.attributes.email']],
        ];
    }

    /** @test */
    public function wrong_token()
    {
        $data = $this->data;

        $res = $this->postJsonEComm(route('api.v1.e_comm.customers.store'), $data, [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
