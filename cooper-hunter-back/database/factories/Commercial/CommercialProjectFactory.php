<?php

namespace Database\Factories\Commercial;

use App\Enums\Commercial\CommercialProjectStatusEnum;
use App\Models\Commercial\CommercialProject;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Technicians\Technician;
use App\Services\Commercial\CommercialProjectService;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Database\Factories\BaseFactory;
use Database\Factories\ForMemberTrait;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|CommercialProject[]|CommercialProject create(array $attributes = [])
 */
class CommercialProjectFactory extends BaseFactory
{
    use ForMemberTrait;

    protected $model = CommercialProject::class;

    public function definition(): array
    {
        return [
            'guid' => null,
            'parent_id' => null,
            'member_type' => Technician::MORPH_NAME,
            'member_id' => Technician::factory(),
            'status' => CommercialProjectStatusEnum::PENDING(),
            'name' => $this->faker->jobTitle,
            'address_line_1' => $this->faker->streetAddress,
            'address_line_2' => $this->faker->numerify('## floor'),
            'city' => $this->faker->city,
            'country_id' => Country::first()->id,
            'state_id' => State::first()->id,
            'zip' => $this->faker->postcode,
            'address_hash' => fn(array $attributes): string => $this->resolveAddressHash($attributes),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => new Phone($this->faker->e164PhoneNumber),
            'email' => new Email($this->faker->safeEmail),
            'company_name' => $this->faker->company,
            'company_address' => $this->faker->address,
            'description' => $this->faker->text,
            'estimate_start_date' => now(),
            'estimate_end_date' => now()->addMonth(),
            'request_until' => now()->add(config('commercial.rdp.credentials.make_request_until')),
            'start_pre_commissioning_date' => null,
            'end_pre_commissioning_date' => null,
            'start_commissioning_date' => null,
            'end_commissioning_date' => null,
        ];
    }

    private function resolveAddressHash(array $attributes): string
    {
        $project = new CommercialProject(
            [
                'address_line_1' => $attributes['address_line_1'],
                'address_line_2' => $attributes['address_line_2'],
                'city' => $attributes['city'],
                'state_id' => $attributes['state_id'],
                'country_id' => $attributes['country_id'],
                'zip' => $attributes['zip'],
            ]
        );

        return resolve(CommercialProjectService::class)
            ->getAddressHash($project);
    }

    public function withCode(?string $code = null): self
    {
        if (!$code) {
            $code = $this->faker->bothify;
        }

        return $this->state(compact('code'));
    }

    public function requestIsExpired(): self
    {
        return $this->state(
            [
                'request_until' => now()->subHour(),
            ]
        );
    }

    public function statusCreated(): self
    {
        return $this->state(
            [
                'status' => CommercialProjectStatusEnum::CREATED()
            ]
        );
    }
}
