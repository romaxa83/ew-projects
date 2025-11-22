<?php

namespace Tests\Unit\Models\AuthHistory;

use App\Models\Users\AuthHistory;
use App\Models\Users\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthHistoryMigrationToMorphTest extends TestCase
{
    use DatabaseTransactions;

    public function test_column_data_convert_to_correct_data_after_migration(): void
    {
        self::markTestSkipped();
        $user = User::factory()->create();

        AuthHistory::query()->upsert(
            [
                [
                    'ip' => '192.168.0.1',
                    'user_id' => $user->id
                ],
                [
                    'ip' => '192.168.0.2',
                    'user_id' => $user->id
                ],
                [
                    'ip' => '192.168.0.3',
                    'user_id' => $user->id
                ],
            ],
            'id'
        );

        $this->artisan('migrate')
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas(
            AuthHistory::TABLE,
            [
                'ip' => '192.168.0.1',
                'auth_id' => $user->id,
                'auth_type' => 'user',
            ],
        );

        $this->assertDatabaseHas(
            AuthHistory::TABLE,
            [
                'ip' => '192.168.0.2',
                'auth_id' => $user->id,
                'auth_type' => 'user',
            ],
        );

        $this->assertDatabaseHas(
            AuthHistory::TABLE,
            [
                'ip' => '192.168.0.3',
                'auth_id' => $user->id,
                'auth_type' => 'user',
            ],
        );

        $this->artisan('migrate:rollback --step=1')
            ->assertExitCode(Command::SUCCESS);
    }
}
