<?php

namespace App\Services\OneC\Commands\Order\PackingSlip;

use App\Contracts\Utilities\Dispatchable;
use App\Models\Media\Media;
use App\Models\Orders\Dealer\PackingSlip;
use App\Services\OneC\Commands\BaseCommand;

class BasePackingSlipCommand extends BaseCommand
{
    public function transformData(Dispatchable $model, array $additions = []): array
    {
        /** @var $model PackingSlip */
        $media = [];
        foreach ($model->media as $item){
            /** @var $item Media */
            $media[] = $item->getFullUrl();
        }

        return [
            'guid' => $model->guid,
            'tracking_number' => $model->tracking_number,
            'tracking_company' => $model->tracking_company,
            'media' => $media,
        ];
    }

    protected function nameCommand(): string
    {
        return '';
    }

    protected function getUri(): string
    {
        return '';
    }
}
