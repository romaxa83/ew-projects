<?php

namespace Database\Factories\Commercial;

use App\Enums\Commercial\CommercialCredentialsStatusEnum;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CredentialsRequest;
use App\Models\Technicians\Technician;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Database\Factories\BaseFactory;
use Database\Factories\ForMemberTrait;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|CredentialsRequest[]|CredentialsRequest create(array $attributes = [])
 */
class CredentialsRequestFactory extends BaseFactory
{
    use ForMemberTrait;

    protected $model = CredentialsRequest::class;

    public function definition(): array
    {
        return [
            'member_type' => Technician::MORPH_NAME,
            'member_id' => Technician::factory(),
            'company_name' => $this->faker->company,
            'company_phone' => new Phone($this->faker->e164PhoneNumber),
            'company_email' => new Email($this->faker->safeEmail),
            'status' => CommercialCredentialsStatusEnum::NEW,
            'commercial_project_id' => CommercialProject::factory(),
            'comment' => $this->faker->text,
        ];
    }
}
