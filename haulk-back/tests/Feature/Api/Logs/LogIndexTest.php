<?php

namespace Tests\Feature\Api\Logs;

use App\Models\Logs\Log;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LogIndexTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        Log::query()->delete();
    }

    public function test_guest_has_unauthorized_error()
    {
        $this->getJson(route('logs'))
            ->assertUnauthorized();
    }

    public function test_auth_user_see_list_of_logs_filter_by_date_range()
    {
        $this->loginAsCarrierAdmin();

        factory(Log::class)
            ->times(10)
            ->create(['unix_time' => now()->subDays(20)->getTimestamp()]);

        factory(Log::class)
            ->times(10)
            ->create(['unix_time' => now()->subDays(5)->getTimestamp()]);

        $params = [
            'date_from' => now()->subDays(10)->format(config('formats.datetime')),
            'date_to' => now()->format(config('formats.datetime')),
        ];

        $response = $this->getJson(route('logs', $params))
            ->assertOk();

        $data = $response->json('data');

        $this->assertCount(10, $data);
    }

    public function test_it_filter_by_message()
    {
        $this->loginAsCarrierAdmin();

        factory(Log::class)
            ->times(9)
            ->create(
                [
                    'message' => 'Some other message',
                ]
            );

        factory(Log::class)
            ->times(20)
            ->create();

        $params = [
            'date_from' => now()->subDays(10)->format(config('formats.datetime')),
            'date_to' => now()->format(config('formats.datetime')),
            'message' => 'other',
        ];

        $response = $this->getJson(route('logs', $params))
            ->assertOk();

        $data = $response->json('data');

        $this->assertCount(9, $data);
    }

    public function test_it_filter_logs_by_level_names()
    {
        $this->loginAsCarrierAdmin();

        factory(Log::class)
            ->times(9)
            ->create(['level_name' => Log::DEBUG_NAME,]);

        factory(Log::class)
            ->times(20)
            ->create();

        $params = [
            'date_from' => now()->subDays(10)->format(config('formats.datetime')),
            'date_to' => now()->format(config('formats.datetime')),
            'level_names' => [Log::DEBUG_NAME],
        ];

        $response = $this->getJson(route('logs', $params))
            ->assertOk();

        $data = $response->json('data');

        $this->assertCount(9, $data);
    }
}
