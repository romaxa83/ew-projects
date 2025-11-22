<?php

namespace App\Imports;


use App\Dto\Fueling\FuelingImportDto;
use App\Enums\Fueling\FuelingSourceEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelCardHistory;
use App\Models\Fueling\Fueling;
use App\Models\Fueling\FuelingHistory;
use App\Models\Users\User;
use App\Services\Events\Fueling\FuelingHistoryEventService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Log;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FuelingImport implements ToArray, WithHeadingRow, WithChunkReading
{
    private FuelingHistory $fuelingHistory;
    private array $fuelCards;
    private Collection $fuelCardHistory;

    public function __construct(
        FuelingHistory $fuelingHistory
    ) {
        $this->fuelingHistory = $fuelingHistory;
        $this->fuelCards = FuelCard::query()
            ->where('provider', $fuelingHistory->provider)
            ->pluck('id', 'card')
            ->toArray();
        $this->fuelCardHistory = FuelCardHistory::query()->with(['fuelCard', 'user'])->get();
    }

    private int $rows = 0;

    public function chunkSize(): int
    {
        return 1000;
    }

    public function array(array $rows)
    {
        try {
            $rows = $this->transform($rows);

            Fueling::query()->insertOrIgnore($rows['rows']->toArray());
            $this->fuelingHistory->counts_success += count($rows);
        } catch (Exception $e) {
            Log::error($e);
            $this->fuelingHistory->count_errors += count($rows);
        }
        $this->fuelingHistory->progress += count($rows['rows']);
        $this->fuelingHistory->counts_success += count($rows['rows']) - $rows['errors'];
        $this->fuelingHistory->count_errors += $rows['errors'];
        $this->fuelingHistory->save();

        FuelingHistoryEventService::fuelingHistory($this->fuelingHistory)->user($this->fuelingHistory->user)->broadcast();

    }

    public function transform(array $array): array
    {
        $newRows = collect();
        $errors = 0;

        foreach ($array as $item) {
            $dto = FuelingImportDto::build($item);
            $arrayDto = $dto->getFullBody();
            $driver = $this->getDriver($dto);

            $valid = $this->valid($arrayDto) && $driver;
            if (!$valid) {
                $errors++;
            }
            $newRows->push(array_merge(
                [
                    'provider' => $this->fuelingHistory->provider,
                    'valid' => $valid,
                    'source' => FuelingSourceEnum::IMPORT,
                    'created_at' => now(),
                    'broker_id' => $this->fuelingHistory->broker_id,
                    'carrier_id' => $this->fuelingHistory->carrier_id,
                    'fueling_history_id' => $this->fuelingHistory->id,
                    'user_id' => $driver->id ?? null,
                    'fuel_card_id' => $this->fuelCards[$dto->getCard()] ?? null,
                ],
                $arrayDto
            ));
        }

        return ['rows' => $newRows, 'errors' => $errors];
    }

    private function valid($arrayDto): bool
    {
        return Validator::make($arrayDto, [
            'card' => ['required', 'size:5', Rule::exists(FuelCard::TABLE_NAME, 'card')],
            'invoice' => ['required'],
            'transaction_date' => ['required', 'string', 'date:Y-m-d'],
            'user' => ['required', 'string',],
            'location' => ['required', 'string',],
            'state' => ['required', 'string', 'size:2'],
            'fees' => ['nullable'],
            'item' => ['required', 'string', 'regex:/^[a-zA-Z]+$/u'],
            'unit_price' => ['required', 'numeric'],
            'quantity' => ['required', 'numeric'],
            'amount' => ['required', 'numeric'],
        ])->passes();
    }

    private function getDriver(FuelingImportDto $dto): ?User
    {
        $fuelCard = $this->fuelCardHistory
            ->where('fuelCard.card', $dto->getCard())
            ->where('date_assigned', '>=', $dto->getTransactionDate())
            ->where('date_unassigned', '<=', $dto->getTransactionDate());

        if(!$fuelCard) {
            $fuelCard = $this->fuelCardHistory
                ->where('fuelCard.card', $dto->getCard())
                ->where('date_assigned', '>=', $dto->getTransactionDate())
                ->where('date_unassigned', null);
        }

        $driver = $fuelCard->count() === 1 ? $fuelCard->first()->user : null;

        if (
            $driver
            && strtolower($dto->getUser()) === strtolower($driver->full_name)
        ) {
            return $driver;
        } else {
            return null;
        }
    }
}
