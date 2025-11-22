<?php

namespace Tests\Feature\Api\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelCardHistory;
use App\Models\Fueling\Fueling;
use App\Models\Fueling\FuelingHistory;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FuelingImportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $this->postJson(route('fuel-cards.store'), [])->assertUnauthorized();
    }

    public function test_it_create()
    {
        $this->loginAsCarrierSuperAdmin();

        $formRequest = [
            'provider' => FuelCardProviderEnum::EFS(),
            'file' => UploadedFile::fake()->createWithContent(
                'fueling.csv',
                file_get_contents(\Storage::disk('tests')->path('fueling.csv'))
            ),
        ];
        $driver = User::factory()->driver()->create([
            'first_name' => 'Oleksandr',
            'last_name' => 'Levytskyi',
        ]);

        $fuelCard = FuelCard::factory()->create([
            'card' => '52708',
        ]);
        $fuelCard2 = FuelCard::factory()->create([
            'card' => '00000',
        ]);
        $fuelCard3 = FuelCard::factory()->create([
            'card' => '11111',
        ]);

        FuelCardHistory::factory()->for($driver)->for($fuelCard)->create([
            'active' => false,
            'date_assigned' => Carbon::parse('2023-10-01'),
            'date_unassigned' => Carbon::parse('2023-10-05'),
        ]);

        FuelCardHistory::factory()->for($driver)->for($fuelCard)->create([
            'active' => false,
            'date_assigned' => Carbon::parse('2023-10-05'),
            'date_unassigned' => Carbon::parse('2023-10-15'),
        ]);
        FuelCardHistory::factory()->for($driver)->for($fuelCard)->create([
            'active' => true,
            'date_assigned' => Carbon::parse('2023-10-15'),
            'date_unassigned' => null,
        ]);

        FuelCardHistory::factory()->for($driver)->for($fuelCard3)->create([
            'active' => false,
            'date_assigned' => Carbon::parse('2023-10-01'),
            'date_unassigned' => Carbon::parse('2023-10-05'),
        ]);

        $this->postJson(route('fueling.import'), $formRequest)
            ->assertCreated()
            ->assertJsonStructure(['data' => [
                'id',
                'total',
                'count_errors',
                'counts_success',
                'progress',
                'path_file',
                'original_name',
                'status',
                'provider',
                'created_at',
                'updated_at',
                'started_at',
                'ended_at',
            ]]);
        $this->assertDatabaseHas(Fueling::TABLE_NAME, [
            'provider' => FuelCardProviderEnum::EFS(),
            'user_id' => $driver->id,
            'fuel_card_id' => $fuelCard->id,
        ]);

        $this->assertDatabaseHas(FuelingHistory::TABLE_NAME, [
            'provider' => FuelCardProviderEnum::EFS(),
            'original_name' => 'fueling.csv'
        ]);
    }
}
