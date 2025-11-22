<?php

namespace Database\Factories\Alerts;

use App\Contracts\Members\Member;
use App\Enums\Alerts\AlertModelEnum;
use App\Enums\Alerts\AlertOrderEnum;
use App\Enums\Alerts\AlertSupportRequestEnum;
use App\Enums\Alerts\AlertTechnicianEnum;
use App\Models\Admins\Admin;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Models\Technicians\Technician;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\SupportRequestCreateTrait;

/**
 * @method Collection|Alert[]|Alert create(array $attributes = [])
 */
class AlertFactory extends Factory
{
    use OrderCreateTrait;
    use SupportRequestCreateTrait;

    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->title,
            'description' => $this->faker->text,
            'meta' => null
        ];
    }

    public function order(string $subtype = AlertOrderEnum::CREATE, ?array $meta = null): self
    {
        $order = $this->createCreatedOrder();

        return $this->state(
            [
                'title' => 'alerts.' . AlertModelEnum::ORDER . '.' . $subtype . '.title',
                'description' => 'alerts.' . AlertModelEnum::ORDER . '.' . $subtype . '.description',
                'model_id' => $order->id,
                'model_type' => $order::MORPH_NAME,
                'meta' => $meta,
                'type' => AlertModelEnum::ORDER . '_' . $subtype
            ]
        );
    }

    public function request(string $subtype = AlertSupportRequestEnum::NEW_MESSAGE): self
    {
        $supportRequest = $this->createSupportRequest();

        return $this->state(
            [
                'title' => 'alerts.' . AlertModelEnum::SUPPORT_REQUEST . '.' . $subtype . '.title',
                'description' => 'alerts.' . AlertModelEnum::SUPPORT_REQUEST . '.' . $subtype . '.description',
                'model_id' => $supportRequest->id,
                'model_type' => $supportRequest::MORPH_NAME,
                'type' => AlertModelEnum::SUPPORT_REQUEST . '_' . $subtype
            ]
        );
    }

    public function technician(string $subtype = AlertTechnicianEnum::MODERATION_READY): self
    {
        $member = Technician::factory()
            ->certified()
            ->create();
        return $this->state(
            [
                'title' => 'alerts.' . AlertModelEnum::TECHNICIAN . '.' . $subtype . '.title',
                'description' => 'alerts.' . AlertModelEnum::TECHNICIAN . '.' . $subtype . '.description',
                'model_id' => $member->getId(),
                'model_type' => $member->getMorphType(),
                'type' => AlertModelEnum::TECHNICIAN . '_' . $subtype
            ]
        );
    }

    public function forAdmin(Admin|null $admin = null, bool $read = false): self
    {
        if ($admin === null) {
            $admin = Admin::factory()
                ->create();
        }
        return $this->afterCreating(
            fn(Alert $alert) => AlertRecipient::factory()
                ->create(
                    [
                        'alert_id' => $alert->id,
                        'recipient_id' => $admin->id,
                        'recipient_type' => Admin::MORPH_NAME,
                        'is_read' => $read
                    ]
                )
        );
    }

    public function forMember(?Member $member = null, bool $read = false): self
    {
        if ($member === null) {
            $member = Technician::factory()
                ->certified()
                ->verified()
                ->create();
        }
        return $this->afterCreating(
            fn(Alert $alert) => AlertRecipient::factory()
                ->create(
                    [
                        'alert_id' => $alert->id,
                        'recipient_id' => $member->getId(),
                        'recipient_type' => $member->getMorphType(),
                        'is_read' => $read,
                    ]
                )
        );
    }
}
