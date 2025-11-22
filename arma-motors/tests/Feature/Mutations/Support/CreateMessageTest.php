<?php

namespace Tests\Feature\Mutations\Support;

use App\Models\Support\Message;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\SupportBuilder;
use Tests\Traits\UserBuilder;

class CreateMessageTest extends TestCase
{
    use DatabaseTransactions;
    use SupportBuilder;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_without_user()
    {
        $builderSupport = $this->supportBuilder();
        $category = $builderSupport->onlyCategory()->create();

        $data = [
            'categoryId' => $category->id,
            'email' => 'test@test.com',
            'text' => 'some text',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.supportMessageCreate');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.support message accept'));

        $mes = Message::where('category_id', $category->id)->first();

        $this->assertNotNull($mes);
        $this->assertEquals($mes->email, $data['email']);
        $this->assertEquals($mes->text, $data['text']);
        $this->assertNull($mes->user);
        $this->assertEquals($mes->status, Message::STATUS_DRAFT);
    }

    /** @test */
    public function success_with_auth_user()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $builderSupport = $this->supportBuilder();
        $category = $builderSupport->onlyCategory()->create();

        $data = [
            'categoryId' => $category->id,
            'email' => 'test@test.com',
            'text' => 'some text',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $this->assertTrue($response->json('data.supportMessageCreate.status'));

        $mes = Message::where('category_id', $category->id)->first();

        $this->assertNotNull($mes);
        $this->assertEquals($mes->email, $data['email']);
        $this->assertEquals($mes->text, $data['text']);
        $this->assertNull($mes->user);

        $this->assertEquals($mes->status, Message::STATUS_DRAFT);
    }

    /** @test */
    public function success_with_user()
    {
        $user = $this->userBuilder()->create();

        $builderSupport = $this->supportBuilder();
        $category = $builderSupport->onlyCategory()->create();

        $data = [
            'userId' => $user->id,
            'categoryId' => $category->id,
            'email' => 'test@test.com',
            'text' => 'some text',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $this->assertTrue($response->json('data.supportMessageCreate.status'));

        $mes = Message::where('category_id', $category->id)->first();

        $this->assertNotNull($mes);
        $this->assertEquals($mes->email, $data['email']);
        $this->assertEquals($mes->text, $data['text']);
        $this->assertNull($mes->user);

        $this->assertEquals($mes->status, Message::STATUS_DRAFT);
    }

    /** @test */
    public function success_without_text()
    {
        $builderSupport = $this->supportBuilder();
        $category = $builderSupport->onlyCategory()->create();

        $data = [
            'categoryId' => $category->id,
            'email' => 'test@test.com',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithouText($data)])
            ->assertOk();

        $this->assertTrue($response->json('data.supportMessageCreate.status'));

        $mes = Message::where('category_id', $category->id)->first();

        $this->assertNull($mes->text);
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                supportMessageCreate(input:{
                    categoryId: %d,
                    email: "%s",
                    text: "%s",
                }) {
                    status
                    message
                }
            }',
            $data['categoryId'],
            $data['email'],
            $data['text'],
        );
    }

    private function getQueryStrWithouText(array $data): string
    {
        return sprintf('
            mutation {
                supportMessageCreate(input:{
                    categoryId: %d,
                    email: "%s"
                }) {
                    status
                    message
                }
            }',
            $data['categoryId'],
            $data['email'],
        );
    }
}


