<?php

namespace Tests\Feature\Queries\Support;

use App\Exceptions\ErrorsCode;
use App\Models\Support\Message;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\SupportBuilder;

class MessageCountNewTest extends TestCase
{
    use DatabaseTransactions;
    use SupportBuilder;
    use AdminBuilder;

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $countNew = 2;

        $this->supportBuilder()->setStatus(Message::STATUS_DRAFT)->setCount($countNew)->create();
        $this->supportBuilder()->setStatus(Message::STATUS_READ)->setCount(1)->create();
        $this->supportBuilder()->setStatus(Message::STATUS_DONE)->setCount(1)->create();

        $response = $this->graphQL($this->getQueryStr());

        $count = $response->json('data.messageCountNew.name');

        $this->assertEquals($countNew, $count);
    }

    /** @test */
    public function nothing()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $countNew = 0;

        $response = $this->graphQL($this->getQueryStr());

        $count = $response->json('data.messageCountNew.name');

        $this->assertEquals($countNew, $count);
    }

    /** @test */
    public function not_auth()
    {
        $this->adminBuilder()->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            messageCountNew {
                key
                name
               }
            }',
        );
    }
}

