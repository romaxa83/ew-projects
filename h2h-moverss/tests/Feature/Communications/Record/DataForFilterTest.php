<?php

namespace Tests\Feature\Communications\Record;

use App\Enums\Communications\Filter\EntityEnum;
use App\Enums\Communications\Filter\PeriodEnum;
use App\Models\Division;
use App\Models\Mailbox\Gmail\Message;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Twilio\TwilioSms;
use App\Models\Zadarma\CallsEvents;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class DataForFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function success()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $this->get(route('communications.filter-data'))
            ->assertJson([
                'channels' => [
                    Message::MORPH_NAME        => 'Emails',
                    TwilioSms::MORPH_NAME      => 'Twilio SMS',
                    CallsEvents::MORPH_NAME    => 'ZadarmaPBX',
                    EventAfterCall::MORPH_NAME => 'RingostatPBX'
                ],
                'period' => [
                    PeriodEnum::Today->value        => 'Today',
                    PeriodEnum::Yesterday->value    => 'Yesterday',
                    PeriodEnum::Last_7_days->value  => 'Last 7 days',
                    PeriodEnum::Last_30_days->value => 'Last 30 days',
                    PeriodEnum::Any->value          => 'Any'
                ],
                'entities' => [
                    EntityEnum::All->value    => 'All',
                    EntityEnum::Calls->value  => 'Text & Calls',
                    EntityEnum::Emails->value => 'Emails'
                ]
            ])
            ->assertJsonCount(3)
            ->assertJsonCount(4, 'channels')
            ->assertJsonCount(5, 'period')
            ->assertJsonCount(3, 'entities')
        ;
    }

    /** @test */
    public function success_old_data()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $this->get(route('communications.filter-data', ['old' => 1]))
            ->assertJson([
                'channels' => [
                    'gmail' => 'Emails',
                    'twiliosms' => 'Twilio SMS',
                    'zadarma'  => 'ZadarmaPBX',
                    'ringostat' => 'RingostatPBX'
                ],
                'period' => [
                    PeriodEnum::Today->value        => 'Today',
                    PeriodEnum::Yesterday->value    => 'Yesterday',
                    PeriodEnum::Last_7_days->value  => 'Last 7 days',
                    PeriodEnum::Last_30_days->value => 'Last 30 days',
                    PeriodEnum::Any->value          => 'Any'
                ]
            ])
            ->assertJsonCount(3)
            ->assertJsonCount(4, 'channels')
            ->assertJsonCount(5, 'period')
        ;
    }
}
