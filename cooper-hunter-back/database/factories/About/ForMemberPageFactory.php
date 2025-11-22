<?php

namespace Database\Factories\About;

use App\Enums\About\ForMemberPageEnum;
use App\Models\About\ForMemberPage;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|ForMemberPage[]|ForMemberPage create(array $attributes = [])
 */
class ForMemberPageFactory extends BaseFactory
{
    protected $model = ForMemberPage::class;

    public function definition(): array
    {
        return [
            'for_member_type' => ForMemberPageEnum::getRandomInstance(),
        ];
    }

    public function forHomeowner(): self
    {
        return $this->type(ForMemberPageEnum::FOR_HOMEOWNER());
    }

    public function type(ForMemberPageEnum $type): self
    {
        return $this->state(
            [
                'for_member_type' => $type
            ]
        );
    }

    public function forTechnician(): self
    {
        return $this->type(ForMemberPageEnum::FOR_TECHNICIAN());
    }
}
