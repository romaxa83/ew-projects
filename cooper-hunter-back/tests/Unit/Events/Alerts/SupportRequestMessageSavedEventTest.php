<?php

namespace Tests\Unit\Events\Alerts;

use App\Enums\Alerts\AlertModelEnum;
use App\Enums\Alerts\AlertSupportRequestEnum;
use App\Models\Admins\Admin;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Models\Support\SupportRequest;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\SupportRequestCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SupportRequestMessageSavedEventTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use SupportRequestCreateTrait;
    use AdminManagerHelperTrait;

    /**@var Collection|Admin[] $admins */
    private Collection|array $admins;

    private SupportRequest $supportRequest;

    public function setUp(): void
    {
        parent::setUp();

        $this->admins = Admin::factory()
            ->count(3)
            ->create();

        $this->supportRequest = $this->createSupportRequest();

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::SUPPORT_REQUEST . '_' . AlertSupportRequestEnum::NEW_REQUEST,
                'model_id' => $this->supportRequest->id,
                'model_type' => $this->supportRequest::MORPH_NAME,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function test_add_message_by_technician(): void
    {
        $this->supportRequest->messages()
            ->create(
                [
                    'message' => $this->faker->text,
                    'sender_type' => $this->supportRequest->technician::MORPH_NAME,
                    'sender_id' => $this->supportRequest->technician->id,
                ]
            );

        $this->assertDatabaseMissing(
            Alert::class,
            [
                'type' => AlertModelEnum::SUPPORT_REQUEST . '_' . AlertSupportRequestEnum::NEW_MESSAGE,
                'model_id' => $this->supportRequest->id,
                'model_type' => $this->supportRequest::MORPH_NAME,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function test_answer_by_technician(): void
    {
        $this->test_answer_by_admin();

        $this->supportRequest->messages()
            ->create(
                [
                    'message' => $this->faker->text,
                    'sender_type' => $this->supportRequest->technician::MORPH_NAME,
                    'sender_id' => $this->supportRequest->technician->id,
                ]
            );

        $alert = Alert::whereType(AlertModelEnum::SUPPORT_REQUEST . '_' . AlertSupportRequestEnum::NEW_MESSAGE)
            ->get()
            ->last();

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'alert_id' => $alert->id,
                'recipient_id' => $this->admins[0]->id,
                'recipient_type' => $this->admins[0]::MORPH_NAME,
            ]
        );

        $this->assertDatabaseMissing(
            AlertRecipient::class,
            [
                'alert_id' => $alert->id,
                'recipient_id' => $this->admins[1]->id,
                'recipient_type' => $this->admins[1]::MORPH_NAME,
            ]
        );

        $this->assertDatabaseMissing(
            AlertRecipient::class,
            [
                'alert_id' => $alert->id,
                'recipient_id' => $this->admins[2]->id,
                'recipient_type' => $this->admins[2]::MORPH_NAME,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function test_answer_by_admin(): void
    {
        $this->supportRequest->messages()
            ->create(
                [
                    'message' => $this->faker->text,
                    'sender_type' => $this->admins[0]::MORPH_NAME,
                    'sender_id' => $this->admins[0]->id,
                ]
            );

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::SUPPORT_REQUEST . '_' . AlertSupportRequestEnum::NEW_MESSAGE,
                'model_id' => $this->supportRequest->id,
                'model_type' => $this->supportRequest::MORPH_NAME,
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'recipient_id' => $this->supportRequest->technician->id,
                'recipient_type' => $this->supportRequest->technician::MORPH_NAME,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function test_close_support_request(): void
    {
        $this->supportRequest->is_closed = true;
        $this->supportRequest->save();

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::SUPPORT_REQUEST . '_' . AlertSupportRequestEnum::CLOSE,
                'model_id' => $this->supportRequest->id,
                'model_type' => $this->supportRequest::MORPH_NAME,
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'recipient_id' => $this->supportRequest->technician->id,
                'recipient_type' => $this->supportRequest->technician::MORPH_NAME,
            ]
        );
    }
}
