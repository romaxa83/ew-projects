<?php

namespace Tests\Feature\Http\Api\V1\Customers\CustomerCrud;

use App\Enums\Tags\TagType;
use App\Events\Events\Customers\CustomerGiveEcommTag;
use App\Events\Listeners\Customers\SendDataToHaullDepot;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;
    protected UserBuilder $userBuilder;

    protected $data = [];

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
    public function success_update()
    {
        Event::fake([CustomerGiveEcommTag::class]);

        $this->loginUserAsSuperAdmin();

        $sales_manager = $this->userBuilder->asSalesManager()->create();
        $sales_manager_1 = $this->userBuilder->asSalesManager()->create();

        $tag = $this->tagBuilder->create();
        /** @var $model Customer */
        $model = $this->customerBuilder
            ->salesManager($sales_manager_1)
            ->tags($tag)
            ->create();

        $data = $this->data;
        $data['sales_manager_id'] = $sales_manager->id;

        $this->assertNotEquals($model->first_name, data_get($data, 'first_name'));
        $this->assertNotEquals($model->last_name, data_get($data, 'first_name'));
        $this->assertNotEquals($model->email, data_get($data, 'email'));
        $this->assertNotEquals($model->phone, data_get($data, 'phone'));
        $this->assertNotEquals($model->phones, data_get($data, 'phones'));
        $this->assertNotEquals($model->phone_extension, data_get($data, 'phone_extension'));
        $this->assertNotEquals($model->notes, data_get($data, 'notes'));
        $this->assertNotEquals($model->tags[0]->id, data_get($data, 'tags.0'));
        $this->assertNotEquals($model->sales_manager_id, $data['sales_manager_id']);


        $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'email' => data_get($data, 'email'),
                    'phone' => data_get($data, 'phone'),
                    'phone_extension' => data_get($data, 'phone_extension'),
                    'phones' => data_get($data, 'phones'),
                    'notes' => data_get($data, 'notes'),
                    'tags' => [
                        [
                            'id' => data_get($data, 'tags.0'),
                        ]
                    ],
                    'sales_manager' => [
                        'id' => $sales_manager->id
                    ]
                ],
            ])
            ->assertJsonCount(1, 'data.tags')
        ;

        Event::assertNotDispatched(CustomerGiveEcommTag::class);
    }

    /** @test */
    public function success_update_without_phone()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->phone(null)->create();

        $data = $this->data;
        $data['phone'] = null;

        $this->assertNull($model->phone);

        $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'phone' => null,
                ],
            ])
        ;
    }

    /** @test */
    public function success_update_with_tag_ecomm()
    {
        Event::fake([CustomerGiveEcommTag::class]);

        $this->loginUserAsSuperAdmin();

        $tag = $this->tagBuilder
            ->name(Tag::ECOM_NAME_TAG)
            ->type(TagType::CUSTOMER())
            ->create();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data = $this->data;
        $data['tags'][] = $tag->id;

        $this->assertFalse($model->hasECommTag());

        $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonCount(2, 'data.tags')
        ;

        Event::assertDispatched(fn (CustomerGiveEcommTag $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            CustomerGiveEcommTag::class,
            SendDataToHaullDepot::class
        );
    }

    /** @test */
    public function success_update_has_tag_ecomm()
    {
        Event::fake([CustomerGiveEcommTag::class]);

        $this->loginUserAsSuperAdmin();

        $tag = $this->tagBuilder
            ->name(Tag::ECOM_NAME_TAG)
            ->type(TagType::CUSTOMER())
            ->create();

        /** @var $model Customer */
        $model = $this->customerBuilder->tags($tag)->create();

        $data = $this->data;
        $data['tags'][] = $tag->id;

        $this->assertTrue($model->hasECommTag());

        $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonCount(2, 'data.tags')
        ;

        Event::assertNotDispatched(CustomerGiveEcommTag::class);
    }

    /** @test */
    public function success_update_file()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $file_1 = UploadedFile::fake()->createWithContent('info_1.txt', 'Some text for file');
        $file_2 = UploadedFile::fake()->createWithContent('info_2.txt', 'Some text for file');

        /** @var $model Customer */
        $model = $this->customerBuilder->attachments($file_2)->create();

        $data = $this->data;
        $data[Customer::ATTACHMENT_FIELD_NAME] = [$file_1];

        $this->assertCount(1, $model->getAttachments());

        $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    Customer::ATTACHMENT_COLLECTION_NAME => [
                        ['name' => 'info_2'],
                        ['name' => 'info_1'],
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data.'.Customer::ATTACHMENT_COLLECTION_NAME)
        ;
    }

    /** @test */
    public function success_update_not_uniq_email()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data = $this->data;
        $data['email'] = $model->email->getValue();

        $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;
    }

    /** @test */
    public function success_update_not_uniq_phone()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data = $this->data;
        $data['phone'] = $model->phone->getValue();

        $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_from_haulk()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        /** @var $model Customer */
        $model = $this->customerBuilder->fromHaulk()->create();

        $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'email' => data_get($data, 'email'),
                    'phone' => data_get($data, 'phone'),
                    'phone_extension' => data_get($data, 'phone_extension'),
                    'phones' => data_get($data, 'phones'),
                    'notes' => data_get($data, 'notes'),
                    'tags' => [
                        [
                            'id' => data_get($data, 'tags.0'),
                        ]
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.customers.update', ['id' => 0]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data['last_name'] = null;

        $res = $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.last_name')]),
            'last_name'
        );
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data = $this->data;

        $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
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

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data)
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
//            ['phone', null, 'validation.required', ['attribute' => 'validation.attributes.phone']],
            ['phone', 'wrong', 'validation.custom.phone.phone_rule', ['attribute' => 'validation.attributes.phone']],
            ['phone', '11111111111', 'validation.unique', ['attribute' => 'validation.attributes.phone']],
            ['sales_manager_id', '11111111111', 'validation.exists', ['attribute' => 'validation.attributes.sales_manager_id']],
            ['sales_manager_id', 'aaa', 'validation.integer', ['attribute' => 'validation.attributes.sales_manager_id']],
        ];
    }

    /** @test */
    public function fail_not_sales_manager()
    {
        $this->loginUserAsSuperAdmin();

        $user = $this->userBuilder->asMechanic()->create();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data = $this->data;
        $data['sales_manager_id'] = $user->id;

        $res = $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data)
        ;

        $this->assertValidationMsg($res,
            __('validation.custom.user.role.not_belong_to_role', [
                'role_name' => __('permissions.roles.sales_manager')
            ]),
            'sales_manager_id');
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $res = $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $res = $this->postJson(route('api.v1.customers.update', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
