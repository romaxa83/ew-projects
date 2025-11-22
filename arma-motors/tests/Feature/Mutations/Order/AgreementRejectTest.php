<?php

namespace Tests\Feature\Mutations\Order;

use App\Models\Agreement\Agreement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\AgreementBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class AgreementRejectTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;
    use Statuses;
    use AgreementBuilder;

    const QUERY = 'agreementReject';

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_create()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();
        /** @var $model Agreement */
        $model = $this->agreementBuilder()
            ->setCarUuid($carUuid)
            ->create();

        $this->postGraphQL(['query' => $this->getQueryStr($model->id)])
            ->assertJson(["data" => [
                self::QUERY => [
                    "message" => __('message.agreement.remove'),
                    "status" => true,
                    "code" => 0,
                ]
            ]]);
    }

    private function getQueryStr($id): string
    {
        return sprintf('
            mutation {
                %s(
                    id: %s
                ) {
                    code
                    status
                    message
                }
            }',
            self::QUERY,
            $id,
        );
    }
}
