<?php

namespace Tests\Unit\Models\Communications;

use App\Enums\Communications\ConversationContactType;
use App\Models\Communications\ConversationMark;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Communications\ConversationMarkBuilder;
use Tests\TestCase;

class ConversationMarkTest extends TestCase
{
    use DatabaseTransactions;

    protected ConversationMarkBuilder $conversationMarkBuilder;

    public function setUp(): void
    {
        $this->conversationMarkBuilder = resolve(ConversationMarkBuilder::class);


        parent::setUp();
    }

    /** @test */
    public function contact_type_is_phone()
    {
        /** @var $model ConversationMark */
        $model = $this->conversationMarkBuilder
            ->contact_type(ConversationContactType::Phone->value)
            ->create();

        $this->assertTrue($model->contactTypeIsPhone());
        $this->assertFalse($model->contactTypeIsEmail());
    }

    /** @test */
    public function contact_type_is_email()
    {
        /** @var $model ConversationMark */
        $model = $this->conversationMarkBuilder
            ->contact_type(ConversationContactType::Email->value)
            ->create();

        $this->assertFalse($model->contactTypeIsPhone());
        $this->assertTrue($model->contactTypeIsEmail());
    }

    /** @test */
    public function contact_type_not_has()
    {
        /** @var $model ConversationMark */
        $model = $this->conversationMarkBuilder
            ->create();

        $this->assertFalse($model->contactTypeIsPhone());
        $this->assertFalse($model->contactTypeIsEmail());
    }
}

