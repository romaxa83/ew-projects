<?php

namespace Tests\Feature\Mutations\User\User;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\Brand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class SetMainCarTest extends TestCase
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
    public function success()
    {
        $user = $this->userBuilder()->phoneVerify()->create();
        $this->loginAsUser($user);
        $carBuilder = $this->carBuilder();

        $car1 = $carBuilder->setUserId($user->id)->selected()->create();
        $car2 = $carBuilder->setUserId($user->id)->create();
        $car3 = $carBuilder->setUserId($user->id)->create();

        $user->refresh();

        $this->assertTrue($user->cars[0]->selected);
        $this->assertEquals($user->cars[0]->id, $car1->id);
        $this->assertFalse($user->cars[1]->selected);
        $this->assertFalse($user->cars[2]->selected);


        $response = $this->postGraphQL(['query' => $this->getQueryStr($car3->id)])
            ->assertOk();

        $responseData = $response->json('data.userSetMainCar');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($responseData['id'], $car3->id);

        $user->refresh();

        $this->assertFalse($user->cars[0]->selected);
        $this->assertFalse($user->cars[1]->selected);
        $this->assertTrue($user->cars[2]->selected);
        $this->assertEquals($user->cars[2]->id, $car3->id);
    }

    /** @test */
    public function success_one_car()
    {
        $user = $this->userBuilder()->phoneVerify()->create();
        $this->loginAsUser($user);
        $carBuilder = $this->carBuilder();

        $car1 = $carBuilder->setUserId($user->id)->create();

        $user->refresh();

        $this->assertFalse($user->cars[0]->selected);


        $response = $this->postGraphQL(['query' => $this->getQueryStr($car1->id)])
            ->assertOk();

        $responseData = $response->json('data.userSetMainCar');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($responseData['id'], $car1->id);

        $user->refresh();

        $this->assertTrue($user->cars[0]->selected);
    }

    /** @test */
    public function new_select()
    {
        $user = $this->userBuilder()->phoneVerify()->create();
        $this->loginAsUser($user);
        $carBuilder = $this->carBuilder();

        $car1 = $carBuilder->setUserId($user->id)->create();
        $car2 = $carBuilder->setUserId($user->id)->create();
        $car3 = $carBuilder->setUserId($user->id)->create();

        $user->refresh();

        $this->assertFalse($user->cars[0]->selected);
        $this->assertFalse($user->cars[1]->selected);
        $this->assertFalse($user->cars[2]->selected);


        $response = $this->postGraphQL(['query' => $this->getQueryStr($car2->id)])
            ->assertOk();

        $responseData = $response->json('data.userSetMainCar');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($responseData['id'], $car2->id);

        $user->refresh();

        $this->assertFalse($user->cars[0]->selected);
        $this->assertFalse($user->cars[2]->selected);
        $this->assertTrue($user->cars[1]->selected);
        $this->assertEquals($user->cars[1]->id, $car2->id);
    }

    /** @test */
    public function wrong_id()
    {
        $user = $this->userBuilder()->phoneVerify()->create();
        $this->loginAsUser($user);

        $response = $this->postGraphQL(['query' => $this->getQueryStr(33)]);

        $this->assertArrayHasKey('errors', $response);
        $this->assertEquals($response->json('errors.0.message'),__('error.not found user car'));
    }

    /** @test */
    public function car_not_verify()
    {
        $user = $this->userBuilder()->phoneVerify()->create();
        $this->loginAsUser($user);

        $carBuilder = $this->carBuilder();

        $car1 = $carBuilder->setUserId($user->id)->notVerify()->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($car1->id)]);

        $this->assertArrayHasKey('errors', $response);
        $this->assertEquals($response->json('errors.0.message'),__('error.car must be verify'));
    }

    /** @test */
    public function not_auth()
    {
        $user = $this->userBuilder()->phoneVerify()->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr(33)]);

        $this->assertArrayHasKey('errors', $response);
        $this->assertEquals($response->json('errors.0.message'), __('auth.not auth'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr($id): string
    {
        return sprintf('
            mutation {
                userSetMainCar(id: "%s") {
                    id}
            }',
            $id
        );
    }

}

