<?php

namespace Tests\Feature\Http\Api\V1\Customers\CustomerCrud;

use App\Enums\Customers\CustomerType;
use App\Enums\Tags\TagType;
use App\Events\Events\Customers\CustomerGiveEcommTag;
use App\Events\Listeners\Customers\SendDataToHaullDepot;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;
    protected UserBuilder $userBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->tagBuilder = resolve(TagBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);

        $tag = $this->tagBuilder->type(TagType::CUSTOMER())->create();

        $this->data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
            'phone' => '12999999999',
            'phone_extension' => '999',
            'phones' => [
                [
                    'number' => '13999999999',
                    'extension' => '139',
                ]
            ],
            'tags' => [$tag->id],
            'notes' => 'text'
        ];
    }

    /** @test */
    public function success_create()
    {
        Event::fake([CustomerGiveEcommTag::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $salesManager User */
        $salesManager = $this->userBuilder->asSalesManager()->create();

        $data = $this->data;
        $data['sales_manager_id'] = $salesManager->id;

        $this->postJson(route('api.v1.customers.store'), $data)
            ->assertJson([
                'data' => [
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'email' => data_get($data, 'email'),
                    'phone' => data_get($data, 'phone'),
                    'phone_extension' => data_get($data, 'phone_extension'),
                    'phones' => data_get($data, 'phones'),
                    'notes' => data_get($data, 'notes'),
                    'type' => CustomerType::BS(),
                    'tags' => [
                        [
                            'id' => data_get($data, 'tags.0')
                        ]
                    ],
                    'addresses' => [],
                    'taxExemption' => null,
                    'sales_manager' => [
                        'id' => $salesManager->id,
                        'first_name' => $salesManager->first_name,
                        'last_name' => $salesManager->last_name,
                        'phone' => $salesManager->phone,
                        'phone_extension' => $salesManager->phone_extension,
                        'email' => $salesManager->email,
                    ],
                    'has_ecommerce_account' => false
                ],
            ])
            ->assertJsonCount(0, 'data.addresses')
        ;

        Event::assertNotDispatched(CustomerGiveEcommTag::class);
    }

    /** @test */
    public function fail_create_and_has_customer_fromHaulk()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $salesManager User */
        $salesManager = $this->userBuilder->asSalesManager()->create();

        $data = $this->data;
        $data['sales_manager_id'] = $salesManager->id;

        $this->customerBuilder
            ->email($data['email'])
            ->fromHaulk()
            ->create();

        $res = $this->postJson(route('api.v1.customers.store'), $data)
        ;

        $this->assertValidationMsg($res,
            __('validation.custom.customer.exist'),
            'email');

    }

    /** @test */
    public function success_create_as_sales_manager()
    {
        $user = $this->loginUserAsSalesManager();

        $data = $this->data;

        $this->postJson(route('api.v1.customers.store'), $data)
            ->assertJson([
                'data' => [
                    'sales_manager' => [
                        'id' => $user->id,
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function fail_create_as_sales_manager_customer_exist()
    {
        $this->loginUserAsSalesManager();

        $email = 'john@doe.com';

        $this->customerBuilder->email($email)->create();

        $data = $this->data;
        $data['email'] = $email;

        $res = $this->postJson(route('api.v1.customers.store'), $data)
        ;

        $this->assertValidationMsg($res,
            __('validation.custom.customer.exist'),
            'email');
    }

    /** @test */
    public function fail_create_as_sales_manager_customer_exist_and_has_sales_manager()
    {
        $this->loginUserAsSalesManager();

        $email = 'john@doe.com';

        $sales = $this->userBuilder->asSalesManager()->create();

        $this->customerBuilder->email($email)->salesManager($sales)->create();

        $data = $this->data;
        $data['email'] = $email;

        $res = $this->postJson(route('api.v1.customers.store'), $data)
        ;

        $this->assertValidationMsg($res,
            __('validation.custom.customer.exist_and_has_manager', [
                'sales_manager_name' => $sales->full_name,
                'sales_manager_email' => $sales->email->getValue(),
            ]),
            'email');
    }

    /** @test */
    public function success_create_with_ecomm_tag()
    {
        Event::fake([CustomerGiveEcommTag::class]);

        $this->loginUserAsSuperAdmin();

        $tag = $this->tagBuilder
            ->name(Tag::ECOM_NAME_TAG)
            ->type(TagType::CUSTOMER())
            ->create();

        $data = $this->data;
        $data['tags'][] = $tag->id;

        $id = $this->postJson(route('api.v1.customers.store'), $data)
            ->assertJson([
                'data' => [
                    'first_name' => data_get($data, 'first_name'),
                ],
            ])
            ->json('data.id')
        ;

        Event::assertDispatched(fn (CustomerGiveEcommTag $event) =>
            $event->getModel()->id === (int)$id
        );
        Event::assertListening(
            CustomerGiveEcommTag::class,
            SendDataToHaullDepot::class
        );
    }

    /** @test */
    public function success_create_with_files()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[Customer::ATTACHMENT_FIELD_NAME] = [
            $file_1 = UploadedFile::fake()->createWithContent('info_1.txt', 'Some text for file'),
            $file_2 = UploadedFile::fake()->createWithContent('info_2.txt', 'Some text for file'),
        ];

        $this->postJson(route('api.v1.customers.store'), $data)
            ->assertJsonStructure([
                'data' => [
                    Customer::ATTACHMENT_COLLECTION_NAME => [
                        [
                            'id',
                            'name',
                            'file_name',
                            'mime_type',
                            'url',
                            'size',
                            'created_at',
                        ],
                    ],
                ]
            ])
            ->assertJson([
                'data' => [
                    Customer::ATTACHMENT_COLLECTION_NAME => [
                        ['name' => 'info_1'],
                        ['name' => 'info_2'],
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data.'.Customer::ATTACHMENT_COLLECTION_NAME)
        ;
    }

    /** @test */
    public function success_create_only_required_field()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        unset(
            $data['phone_extension'],
            $data['phones'],
            $data['phone'],
            $data['notes'],
            $data['tags'],
        );

        $this->postJson(route('api.v1.customers.store'), $data)
            ->assertJson([
                'data' => [
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'email' => data_get($data, 'email'),
                    'phone' => null,
                    'phone_extension' => null,
                    'phones' => [],
                    'notes' => null,
                    'tags' => [],
                    'sales_manager' => null
                ],
            ])
            ->assertJsonCount(0, 'data.phones')
            ->assertJsonCount(0, 'data.tags')
        ;
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['first_name'] = null;

        $res = $this->postJson(route('api.v1.customers.store'), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly($res,
            __('validation.required', ['attribute' => __('validation.attributes.first_name')]),
            'first_name');
    }

    /** @test */
    public function field_wrong_tag()
    {
        $this->loginUserAsSuperAdmin();

        $tag = $this->tagBuilder->type(TagType::TRUCKS_AND_TRAILER())->create();

        $data = $this->data;
        $data['tags'] = [$tag->id];

        $res = $this->postJson(route('api.v1.customers.store'), $data)
        ;

        $this->assertValidationMsg($res,
            __('validation.exists', ['attribute' => __('validation.attributes.nested.tags.*')]),
            'tags.0');
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->postJson(route('api.v1.customers.store'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $this->assertDatabaseEmpty(Customer::TABLE);
    }

    /** @test */
    public function fail_not_sales_manager()
    {
        $this->loginUserAsSuperAdmin();

        $user = $this->userBuilder->asMechanic()->create();

        $data = $this->data;
        $data['sales_manager_id'] = $user->id;

        $res = $this->postJson(route('api.v1.customers.store'), $data)
        ;

        $this->assertValidationMsg($res,
            __('validation.custom.user.role.not_belong_to_role', [
                'role_name' => __('permissions.roles.sales_manager')
            ]),
            'sales_manager_id');
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

        $res = $this->postJson(route('api.v1.customers.store'), $data)
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
            ['phone', 'wrong', 'validation.custom.phone.phone_rule', ['attribute' => 'validation.attributes.phone']],
            ['phone', '11111111111', 'validation.unique', ['attribute' => 'validation.attributes.phone']],
            ['phone', '11111111111', 'validation.unique', ['attribute' => 'validation.attributes.phone']],
            ['sales_manager_id', '11111111111', 'validation.exists', ['attribute' => 'validation.attributes.sales_manager_id']],
            ['sales_manager_id', 'aaa', 'validation.integer', ['attribute' => 'validation.attributes.sales_manager_id']],
        ];
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.customers.store'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.customers.store'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}

