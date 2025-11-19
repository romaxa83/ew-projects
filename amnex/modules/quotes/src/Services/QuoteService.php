<?php

declare(strict_types=1);

namespace Wezom\Quotes\Services;

use App\Services\Requests\Google\Map\Commands\GetDistanceBetweenAddresses;
use Carbon\CarbonImmutable;
use Wezom\Quotes\Dto\QuoteBackDto;
use Wezom\Quotes\Dto\QuoteSiteAcceptDto;
use Wezom\Quotes\Dto\QuoteSiteDto;
use Wezom\Quotes\Enums\ContainerDimensionTypeEnum;
use Wezom\Quotes\Enums\QuoteStatusEnum;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Models\TerminalDistance;
use Wezom\Quotes\Services\Calculation\CalcPayload;
use Wezom\Quotes\Services\Calculation\CalculationPipeline;
use Wezom\Quotes\Services\Calculation\Handlers;

final class QuoteService
{
    public function create(QuoteSiteDto $dto): Quote
    {
        $model = new Quote();

        if ($dto->isOutOfGauge) {
            $model = QuoteStatusService::setStatus($model, QuoteStatusEnum::NEW, false);
            $model->container_type = ContainerDimensionTypeEnum::NONE;
            $model->quote_accepted_at = CarbonImmutable::now();
        } else {
            $model = QuoteStatusService::setStatus($model, QuoteStatusEnum::DRAFT, false);
            $model->container_type = $dto->containerType;
        }

        $model->is_not_standard_dimension = $dto->isOutOfGauge;
        $model->is_transload = $dto->isTransload;
        $model->is_palletized = $dto->isPalletized;
        $model->pickup_terminal_id = $dto->pickupTerminalId;
        $model->delivery_address = $dto->deliveryAddress;
        $model->number_pallets = $dto->numberPallets;
        $model->days_stored = $dto->daysStored;
        $model->piece_count = $dto->pieceCount;
        $model->email = $dto->email;
        $model->phone = $dto->phone;
        $model->user_name = $dto->userName;

        $model->save();

        $this->getAndSetDistance($model);

        return $model;
    }

    public function accept(Quote $model, QuoteSiteAcceptDto $dto): Quote
    {
        $model->status = QuoteStatusEnum::NEW;
        $model->email = $dto->email;
        $model->phone = $dto->phone;
        $model->user_name = $dto->userName;
        $model->quote_accepted_at = CarbonImmutable::now();

        $model->save();

        return $model;
    }

    public function updateBack(Quote $model, QuoteBackDto $dto): Quote
    {
        $model->container_number = $dto->containerNumber ?? $model->container_number;

        $model->mileage_cost = $dto->mileageCost ?? $model->mileage_cost;
        $model->cargo_cost = $dto->cargoCost ?? $model->cargo_cost;
        $model->storage_cost = $dto->storageCost ?? $model->storage_cost;

        $payload = $this->recalculateTotal($model);

        $model->total = $payload->total;

        $model->save();

        return $model;
    }

    public function getAndSetDistance(Quote $model): Quote
    {
        $distance = TerminalDistance::query()
            ->where('pickup_terminal_id', $model->pickup_terminal_id)
            ->where('delivery_address', $model->delivery_address)
            ->first()
        ;

        if ($distance) {
            $model->update(['terminal_distance_id' => $distance->id]);
        } else {
            $model->load(['pickupTerminal']);

            /** @var GetDistanceBetweenAddresses $command */
            $command = resolve(GetDistanceBetweenAddresses::class);

            $res = $command->handler([
                'origin' => $model->pickupTerminal->address,
                'destination' => $model->delivery_address,
            ]);

            if (!empty($res)) {
                $terminalDistance = new TerminalDistance();
                $terminalDistance->pickup_terminal_id = $model->pickup_terminal_id;
                $terminalDistance->delivery_address = $model->delivery_address;
                $terminalDistance->distance_as_mile = $res['distance_as_mile'];
                $terminalDistance->distance_as_meters = $res['distance_as_meters'];
                $terminalDistance->distance_text = $res['distance_text'];
                $terminalDistance->start_location = $res['start'];
                $terminalDistance->end_location = $res['end'];
                $terminalDistance->delivery_data = [];
                $terminalDistance->save();

                $model->update(['terminal_distance_id' => $terminalDistance->id]);
            }
        }

        return $model;
    }

    public function calculationAndSet(Quote $model): Quote
    {
        $payload = $this->calculation($model);

        $model->update([
            'mileage_cost' => is_null($payload->mileageRate) ? 0 : $payload->mileageRate,
            'cargo_cost' => is_null($payload->cargoCost) ? 0 : $payload->cargoCost,
            'storage_cost' => is_null($payload->storageCost) ? 0 : $payload->storageCost,
            'total' => is_null($payload->total) ? 0 : $payload->total,
            'payload' => $payload,
        ]);

        return $model;
    }

    public function calculation(Quote $model): CalcPayload
    {
        $model->load([
            'pickupTerminal',
            'distance'
        ]);

        $pipeline = new CalculationPipeline();
        $pipeline
            ->addHandlers([
                new Handlers\Mileage(),
                new Handlers\Cargo(),
                new Handlers\Storage(),
                new Handlers\Total(),
            ])
        ;

        $payload = new CalcPayload($model);

        return $pipeline->process($payload);
    }

    public function recalculateTotal(Quote $model): CalcPayload
    {
        $payload = new CalcPayload($model);
        $payload->mileageRate = $model->mileage_cost;
        $payload->cargoCost = $model->cargo_cost;
        $payload->storageCost = $model->storage_cost;

        return (new Handlers\Total())->handle($payload);
    }
}
