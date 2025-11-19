<?php

declare(strict_types=1);

namespace Wezom\Quotes\Services;

use Wezom\Quotes\Enums\QuoteStatusEnum;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Models\QuoteHistoryStatus;

final class QuoteStatusService
{
    private function __construct()
    {
    }

    public static function setStatus(
        Quote $model,
        QuoteStatusEnum|string $status,
        bool $save = true,
    ): ?Quote {
        $status = $status instanceof QuoteStatusEnum
            ? $status
            : QuoteStatusEnum::from($status);

        return make_transaction(function () use ($model, $status, $save) {

            $self = new self();

            if ($model->id) {
                QuoteHistoryStatus::create([
                    'quote_id' => $model->id,
                    'prev_status' => $model->status,
                    'new_status' => $status,
                ]);
            }


            $model = $self->toggleStatus($model, $status, $save);

            return $model;
        });
    }

    private function toggleStatus(
        Quote $model,
        QuoteStatusEnum $status,
        bool $save = true,
    ): Quote {
        return match ($status) {
            QuoteStatusEnum::DRAFT => $this->toDraft($model, $save),
            QuoteStatusEnum::NEW => $this->toNew($model, $save),
            QuoteStatusEnum::EXPIRED => $this->toExpired($model, $save),
            QuoteStatusEnum::SCHEDULED => $this->toSchedule($model, $save),
            QuoteStatusEnum::ARRIVED_AT_THE_PICKUP => $this->toArrivedAtThePickup($model, $save),
            QuoteStatusEnum::IN_TRANSIT => $this->toInTransit($model, $save),
            QuoteStatusEnum::DEPARTED_FROM_THE_PICKUP => $this->toDepartedFromThePickup($model, $save),
            QuoteStatusEnum::ARRIVED_AT_THE_DELIVERY => $this->toArrivedAtTheDelivery($model, $save),
            QuoteStatusEnum::DELIVERED => $this->toDelivered($model, $save),
        };
    }

    private function toDraft(Quote $model, bool $save = true): Quote
    {
        $model->status = QuoteStatusEnum::DRAFT;

        return $this->saveAndReturn($model, $save);
    }

    private function toNew(Quote $model, bool $save = true): Quote
    {
        $model->status = QuoteStatusEnum::NEW;

        return $this->saveAndReturn($model, $save);
    }

    private function toExpired(Quote $model, bool $save = true): Quote
    {
        $model->status = QuoteStatusEnum::EXPIRED;

        return $this->saveAndReturn($model, $save);
    }

    private function toSchedule(Quote $model, bool $save = true): Quote
    {
        $model->status = QuoteStatusEnum::SCHEDULED;

        return $this->saveAndReturn($model, $save);
    }

    private function toArrivedAtThePickup(Quote $model, bool $save = true): Quote
    {
        $model->status = QuoteStatusEnum::ARRIVED_AT_THE_PICKUP;

        return $this->saveAndReturn($model, $save);
    }

    private function toInTransit(Quote $model, bool $save = true): Quote
    {
        $model->status = QuoteStatusEnum::IN_TRANSIT;

        return $this->saveAndReturn($model, $save);
    }

    private function toDepartedFromThePickup(Quote $model, bool $save = true): Quote
    {
        $model->status = QuoteStatusEnum::DEPARTED_FROM_THE_PICKUP;

        return $this->saveAndReturn($model, $save);
    }

    private function toArrivedAtTheDelivery(Quote $model, bool $save = true): Quote
    {
        $model->status = QuoteStatusEnum::ARRIVED_AT_THE_DELIVERY;

        return $this->saveAndReturn($model, $save);
    }

    private function toDelivered(Quote $model, bool $save = true): Quote
    {
        $model->status = QuoteStatusEnum::DELIVERED;

        return $this->saveAndReturn($model, $save);
    }

    private function saveAndReturn(Quote $model, bool $save = true): Quote
    {
        if ($save) {
            $model->save();
        }

        return $model;
    }
}
