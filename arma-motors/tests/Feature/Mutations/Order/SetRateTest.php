<?php

namespace Tests\Feature\Mutations\Order;

use App\Exceptions\ErrorsCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class SetRateTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use Statuses;
    use OrderBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $order = $this->orderBuilder()->setUserId($user->id)
            ->asOne()->withAdditions()->create();


        $this->assertNull($order->additions->rate);
        $this->assertNull($order->additions->rate_comment);

        $data = [
            'id' => $order->id,
            'rate' => 5,
            'comment' => 'some_comment',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.orderRate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('additions', $responseData);
        $this->assertArrayHasKey('rate', $responseData['additions']);
        $this->assertArrayHasKey('rateComment', $responseData['additions']);

        $order->refresh();

        $this->assertEquals($data['rate'], $responseData['additions']['rate']);
        $this->assertEquals($data['comment'], $responseData['additions']['rateComment']);
        $this->assertEquals($order->additions->rate, $responseData['additions']['rate']);
        $this->assertEquals($order->additions->rate_comment, $responseData['additions']['rateComment']);
    }

    /** @test */
    public function success_without_comment()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $order = $this->orderBuilder()->setUserId($user->id)
            ->asOne()->withAdditions()->create();


        $this->assertNull($order->additions->rate);
        $this->assertNull($order->additions->rate_comment);

        $data = [
            'id' => $order->id,
            'rate' => 5,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutComment($data)]);

        $responseData = $response->json('data.orderRate');

        $order->refresh();

        $this->assertEquals($data['rate'], $responseData['additions']['rate']);
        $this->assertNull($responseData['additions']['rateComment']);
        $this->assertNull($order->additions->rate_comment);
    }

    /** @test */
    public function success_without_comment_not_have_additions()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $order = $this->orderBuilder()->setUserId($user->id)
            ->asOne()->create();


        $this->assertNull($order->additions);

        $data = [
            'id' => $order->id,
            'rate' => 5,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutComment($data)]);

        $responseData = $response->json('data.orderRate');

        $order->refresh();

        $this->assertEquals($data['rate'], $responseData['additions']['rate']);
        $this->assertNull($responseData['additions']['rateComment']);
        $this->assertNull($order->additions->rate_comment);
    }

    /** @test */
    public function required_comment()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $order = $this->orderBuilder()->setUserId($user->id)
            ->asOne()->withAdditions()->create();


        $this->assertNull($order->additions->rate);
        $this->assertNull($order->additions->rate_comment);

        $data = [
            'id' => $order->id,
            'rate' => 2,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutComment($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__( 'error.required comment to rate', ['rate' => $data['rate']]), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                orderRate(input:{
                    id: "%s"
                    rate: %d
                    comment: "%s"
                }) {
                    id
                    additions {
                        rate
                        rateComment
                    }
                }
            }',
            $data['id'],
            $data['rate'],
            $data['comment'],
        );
    }

    public static function getQueryStrWithoutComment(array $data): string
    {
        return sprintf('
            mutation {
                orderRate(input:{
                    id: "%s"
                    rate: %d
                }) {
                    id
                    additions {
                        rate
                        rateComment
                    }
                }
            }',
            $data['id'],
            $data['rate'],
        );
    }
}


