<?php

namespace Tests\Feature\Mutations\User\User;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class UserDeleteCarsFromGarageTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function add_to_garage()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $number = 'AA1111AA';
        $carBuilder = $this->carBuilder();
        $car = $carBuilder
            ->setNumber($number)
            ->setUserId($user->id)
            ->setInGarage(true)
            ->create();

        $user->refresh();

        $this->assertTrue($user->cars[0]->inGarage());

        $response = $this->postGraphQL(['query' => $this->getQueryStr([$user->cars[0]->id])]);

        $this->assertFalse($response->json('data.userDeleteCarsFromGarage.cars.0.inGarage'));

        $user->refresh();

        $this->assertFalse($user->cars[0]->inGarage());
    }

    public static function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                userDeleteCarsFromGarage(input: {carIds: [%d]})
                {
                    id
                    cars {
                        id
                        inGarage
                        number
                        vin
                        year
                        status
                        isPersonal
                    }
                }
            }',
            $data[0],
        );
    }
}
