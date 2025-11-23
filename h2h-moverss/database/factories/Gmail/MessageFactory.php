<?php

namespace Database\Factories\Gmail;

use App\Models\Mailbox\Gmail\Account;
use App\Models\Mailbox\Gmail\Message;
use Database\Factories\BaseFactory;

class MessageFactory extends BaseFactory
{
    protected $model = Message::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'msg_id' => $this->faker->uuid(),
            'thread_id' => $this->faker->uuid(),
            'history_id' => $this->faker->uuid(),
            'tags' => Message::TAG_SENT,
            'tag' => Message::TAG_SENT,
            'subject' => $this->faker->sentence(),
            'miscs' => [
                'size' => 14680,
                'delivered_to' => 'allymovers.com@gmail.com',
                'has_attachments' => false,
                'from' => [
                    'name' => 'Mailgun Support',
                    'email' => 'support@mailgun.zendesk.com'
                ],
            ],
        ];
    }
}
