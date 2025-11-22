<?php

namespace Tests\Feature\Mutations\User\User;

use App\Exceptions\ErrorsCode;
use App\Models\User\Car;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Queries\Archive\ArchiveCarListTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class DeleteCarTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use AdminBuilder;
    use CarBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_user_have_one_car()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $carBuilder = $this->carBuilder();
        $car1 = $carBuilder->setUserId($user->id)->selected()->create();

        $user->refresh();

        $this->assertNotEmpty($user->cars);
        $this->assertCount(1, $user->cars);
        $this->assertEquals($user->cars[0]->id, $car1->id);
        $this->assertNull($user->cars[0]->deleted_at, $car1->id);

        // проверяем выбранное авто
        $this->assertTrue($car1->isSelected());

        $data = [
            'id' => $car1->id,
            'reason' => Car::REASON_SOLD,
            'comment' => 'some'
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.userDeleteCar');

        $this->assertArrayHasKey('code', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.user.delete car'));

        $user->refresh();

        $this->assertEmpty($user->cars);

        // запрос в архив
        $admin = $this->adminBuilder()->createRoleWithPerm(Permissions::ARCHIVE_CAR_LIST)->create();
        $this->loginAsAdmin($admin);

        $responseToArchive = $this->postGraphQL(['query' => ArchiveCarListTest::getQueryStr()]);

        $responseToArchiveData = $responseToArchive->json('data.carsArchive');

        $this->assertArrayHasKey('id', $responseToArchiveData['data'][0]);
        $this->assertArrayHasKey('number', $responseToArchiveData['data'][0]);
        $this->assertEquals($responseToArchiveData['data'][0]['id'], $car1->id);
        $this->assertEquals(1, $responseToArchiveData['paginatorInfo']['count']);

        // проверяем что удаленное авто уже не выбранное
        $car1->refresh();
        $this->assertFalse($car1->isSelected());
        $this->assertEquals($car1->delete_reason, $data['reason']);
        $this->assertEquals($car1->delete_comment, $data['comment']);
    }

    /** @test */
    public function success_user_have_more_car()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $carBuilder = $this->carBuilder();

        $car1 = $carBuilder->setUserId($user->id)->selected()->create();
        $car2 = $carBuilder->setUserId($user->id)->create();
        $car3 = $carBuilder->setUserId($user->id)->create();

        $user->refresh();

        $this->assertNotEmpty($user->cars);
        $this->assertCount(3, $user->cars);
        $this->assertEquals($user->cars[0]->id, $car1->id);
        $this->assertNull($user->cars[0]->deleted_at, $car1->id);

        // проверяем выбранное авто
        $this->assertTrue($car1->isSelected());
        $this->assertFalse($car2->isSelected());
        $this->assertFalse($car3->isSelected());

        $data = [
            'id' => $car1->id,
            'reason' => Car::REASON_OTHER,
            'comment' => 'some'
        ];

        // запрос
        $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $user->refresh();

        $this->assertCount(2, $user->cars);
        $this->assertEquals($user->cars[0]->id, $car2->id);
        $this->assertEquals($user->cars[1]->id, $car3->id);

        // проверяем что удаленное авто уже не выбранное
        $car1->refresh();
        $car2->refresh();
        $car3->refresh();
        $this->assertTrue($car2->isSelected());
        $this->assertFalse($car1->isSelected());
        $this->assertFalse($car3->isSelected());
    }


    /** @test */
    public function delete_without_reason()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $carBuilder = $this->carBuilder();
        $car1 = $carBuilder->setUserId($user->id)->selected()->create();

        $data = [
            'id' => $car1->id,
            'comment' => 'some'
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutReason($data)]);

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function delete_without_comment()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $carBuilder = $this->carBuilder();
        $car1 = $carBuilder->setUserId($user->id)->selected()->create();

        $data = [
            'id' => $car1->id,
            'reason' => Car::REASON_SOLD
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutComment($data)]);

        $responseData = $response->json('data.userDeleteCar');

        $this->assertArrayHasKey('status', $responseData);

        $car1->refresh();
        $this->assertEquals($car1->delete_reason, $data['reason']);
        $this->assertNull($car1->delete_comment);
    }

    /** @test */
    public function delete_required_comment()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $carBuilder = $this->carBuilder();
        $car1 = $carBuilder->setUserId($user->id)->selected()->create();

        $data = [
            'id' => $car1->id,
            'reason' => Car::REASON_OTHER
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutComment($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.required comment for delete car', ['reason' => $data['reason']]), $response->json('errors.0.message'));
    }

    /** @test */
    public function not_found_car()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $data = [
            'id' => 9999,
            'reason' => Car::REASON_SOLD
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutComment($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found user car'), $response->json('errors.0.message'));
    }

    /** @test */
    public function not_auth()
    {
        $user = $this->userBuilder()->create();

        $data = [
            'id' => 9999,
            'reason' => Car::REASON_SOLD
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutComment($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                userDeleteCar(input: {
                    id: "%s"
                    reason: %s
                    comment: "%s"
                }) {
                    code
                    status
                    message
                }
            }',
            $data['id'],
            $data['reason'],
            $data['comment']
        );
    }

    public static function getQueryStrWithoutReason(array $data): string
    {
        return sprintf('
            mutation {
                userDeleteCar(input: {
                    id: "%s"
                    comment: "%s"
                }) {
                    code
                    status
                    message
                }
            }',
            $data['id'],
            $data['comment']
        );
    }

    public static function getQueryStrWithoutComment(array $data): string
    {
        return sprintf('
            mutation {
                userDeleteCar(input: {
                    id: "%s"
                    reason: %s
                }) {
                    code
                    status
                    message
                }
            }',
            $data['id'],
            $data['reason']
        );
    }

    public static function data($carId, $reason = null, $comment = null): array
    {
        $reason = $reason ?? Car::REASON_SOLD;

        $data = [
            'id' => $carId,
            'reason' => $reason,
        ];

        if($comment){
            $data['comment'] = $comment;
        }

        return $data;
    }
}




