<?php

namespace Database\Factories\Faq;

use App\Enums\Faq\Questions\QuestionStatusEnum;
use App\Models\Admins\Admin;
use App\Models\Faq\Question;
use App\ValueObjects\Email;
use Database\Factories\Admins\AdminFactory;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Question[]|Question create(array $attributes = [])
 */
class QuestionFactory extends BaseFactory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'status' => QuestionStatusEnum::NEW,
            'name' => $this->faker->name,
            'email' => new Email($this->faker->safeEmail),
            'question' => $this->faker->sentence,
        ];
    }

    public function answeredBy(Admin|AdminFactory $admin): self
    {
        return $this->answered($admin);
    }

    public function answered(Admin|AdminFactory|null $admin = null): self
    {
        return $this->state(
            [
                'status' => QuestionStatusEnum::ANSWERED,
                'admin_id' => $admin ?: Admin::factory(),
                'answer' => $this->faker->sentence,
            ]
        );
    }
}
