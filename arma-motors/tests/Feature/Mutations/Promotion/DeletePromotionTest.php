<?php

namespace Tests\Feature\Mutations\Promotion;

use App\Exceptions\ErrorsCode;
use App\Models\Promotion\Promotion;
use App\Models\Support\Message;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\PromotionBuilder;
use Tests\Traits\Builders\SupportBuilder;
use Tests\Traits\Statuses;

class DeletePromotionTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use PromotionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::PROMOTION_DELETE])
            ->create();
        $this->loginAsAdmin($admin);

        $promotion = $this->promotionBuilder()->create();
        $promotion2 = $this->promotionBuilder()->create();

        $total = Promotion::count();
        $this->assertEquals(2, $total);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($promotion->id)])
            ->assertOk();

        $responseData = $response->json('data.promotionDelete');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);

        $this->assertEquals($responseData['message'], __('message.promotion.promotion delete'));
        $this->assertTrue($responseData['status']);

        $total = Promotion::count();
        $this->assertEquals(1, $total);
    }

    /** @test */
    public function not_found()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::PROMOTION_DELETE])
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->postGraphQL(['query' => $this->getQueryStr(11)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::PROMOTION_DELETE])
            ->create();

        $promotion = $this->promotionBuilder()->create();
        $response = $this->postGraphQL(['query' => $this->getQueryStr($promotion->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::SUPPORT_MESSAGE_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $promotion = $this->promotionBuilder()->create();
        $response = $this->postGraphQL(['query' => $this->getQueryStr($promotion->id)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }


    private function getQueryStr($id): string
    {
        return sprintf('
            mutation {
                promotionDelete(id: %d) {
                    message
                    status
                }
            }',
            $id,
        );
    }
}
