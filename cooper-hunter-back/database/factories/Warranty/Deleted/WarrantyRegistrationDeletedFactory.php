<?php

namespace Database\Factories\Warranty\Deleted;

use App\Entities\Warranty\WarrantyProductInfo;
use App\Entities\Warranty\WarrantyUserInfo;
use App\Enums\Projects\Systems\WarrantyStatus;
use App\Enums\Warranties\WarrantyType;
use App\Models\Projects\System;
use App\Models\Users\User;
use App\Models\Warranty\Deleted\WarrantyRegistrationDeleted;
use Database\Factories\ForMemberTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|WarrantyRegistrationDeleted[]|WarrantyRegistrationDeleted create(array $attributes = [])
 */
class WarrantyRegistrationDeletedFactory extends Factory
{
    use ForMemberTrait;

    protected $model = WarrantyRegistrationDeleted::class;

    public function definition(): array
    {
        return [
            'warranty_status' => WarrantyStatus::PENDING,
            'type' => WarrantyType::RESIDENTIAL,
            'member_id' => User::factory(),
            'member_type' => User::MORPH_NAME,
            'system_id' => System::factory(),
            'commercial_project_id' => null,
            'user_info' => WarrantyUserInfo::make(
                [
                    'first_name' => $this->faker->firstName,
                    'last_name' => $this->faker->lastName,
                    'email' => $this->faker->safeEmail,
                ],
                true
            ),
            'product_info' => WarrantyProductInfo::make(
                [
                    'purchase_date' => now()->format(WarrantyProductInfo::DATE_FORMAT),
                    'installation_date' => now()->format(WarrantyProductInfo::DATE_FORMAT),
                    'installer_license_number' => $this->faker->randomNumber(),
                    'purchase_place' => $this->faker->streetAddress,
                ]
            ),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function pending(): self
    {
        return $this->status(WarrantyStatus::PENDING());
    }

    public function deleted(): self
    {
        return $this->status(WarrantyStatus::DELETE());
    }

    public function status(WarrantyStatus $status): self
    {
        return $this->state(
            [
                'warranty_status' => $status->value,
            ]
        );
    }

    public function voided(): self
    {
        return $this->status(WarrantyStatus::VOIDED());
    }
}
