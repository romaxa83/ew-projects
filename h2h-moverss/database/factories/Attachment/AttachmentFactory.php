<?php

namespace Database\Factories\Attachment;

use App\Models\Attachment;
use App\User;
use Database\Factories\BaseFactory;

class AttachmentFactory extends BaseFactory
{
    protected $model = Attachment::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'hash' => 'd590360f9935b3745eeb01b262ac66ab4e791bfed69b5b01534cce3aa401451c',
            'miscs' => [],
            'description' => $this->faker->sentence(),
            'deleted_at' => null,
            'entity_id' => null,
            'entity_type' => null,
        ];
    }
}
