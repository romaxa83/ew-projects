<?php

namespace Feature\Http\Api\V1\Customers\EComm;

use App\Enums\Customers\CustomerTaxExemptionStatus;
use App\Enums\Customers\CustomerTaxExemptionType;
use App\Enums\Customers\CustomerType;
use App\Models\Customers\Customer;
use App\Notifications\Users\UserTaxExemptionNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\TestCase;

class CreateTaxExemptionTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);

        $this->data = [
            'link' => 'https://file.com/test.jpg',
            'file_name' => 'test.jpg',
        ];
    }

    /** @test */
    public function success_create()
    {
        Notification::fake();

        $this->loginUserAsSuperAdmin();
        $email = config('mail.email_from_tax_exemption');

        /** @var $model Customer */
        $model = $this->customerBuilder
            ->type(CustomerType::EComm)
            ->create();

        $data = $this->data;

        $this->postJsonEComm(route('api.v1.e_comm.customers.upload-tax-exemption', ['email' => $model->email]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'taxExemption' => [
                        'link' => $data['link'],
                        'file_name' => $data['file_name'],
                        'status' => CustomerTaxExemptionStatus::UNDER_REVIEW->value,
                        'type' => CustomerTaxExemptionType::ECOM->value,
                        'date_active_to' => null,
                    ],
                ],
            ]);

        $this->assertNotificationSentTo(
            $email,
            UserTaxExemptionNotification::class
        );
    }

    /** @test */
    public function fail_not_ecomm_customer()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder
            ->type(CustomerType::BS)
            ->create();

        $data = $this->data;

        $res = $this->postJsonEComm(route('api.v1.e_comm.customers.upload-tax-exemption', ['email' => $model->email]), $data);

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJsonEComm(route('api.v1.e_comm.customers.upload-tax-exemption', ['email' => 'test@gmail.com']), $data);

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }


    /** @test */
    public function wrong_token()
    {
        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data = $this->data;

        $res = $this->postJsonEComm(route('api.v1.e_comm.customers.upload-tax-exemption', ['email' => $model->email]), $data, [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
