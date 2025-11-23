<?php

namespace Database\Factories\Gmail;

use App\User;
use App\Models\Division;
use Database\Factories\BaseFactory;
use App\Models\Mailbox\Gmail\Account;

class AccountFactory extends BaseFactory
{
    protected $model = Account::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'division_id' => Division::factory(),
            'user_id' => User::factory(),
            'active' => 1,
            'is_archived' => 0,
            'miscs' => [
                'email' => "allymovers.com@gmail.com",
                'userLastOnline' => '2024-03-06 23:42:02',
                'lastSync' => '2024-06-14 08:30:13',
                'watchInit' => '2024-06-14 01:00:13',
                'historyId' => 509026,
            ],
            'deleted_at' => null,
        ];
    }
}
