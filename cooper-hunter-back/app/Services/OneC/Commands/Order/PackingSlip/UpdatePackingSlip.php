<?php

namespace App\Services\OneC\Commands\Order\PackingSlip;

use App\Enums\Requests\RequestCommand;
use App\Contracts\Utilities\Dispatchable;
use App\Models\Orders\Dealer\PackingSlip;
use App\Services\OneC\Commands\CommandException;
use Illuminate\Support\Facades\Cache;
use Throwable;

class UpdatePackingSlip extends BasePackingSlipCommand
{
    public function nameCommand(): string
    {
        return RequestCommand::UPDATE_PACKING_SLIP;
    }

    public function getUri(): string
    {
        return config("api.one_c.request_uri.dealer.order.packing_slip.update");
    }

    protected function afterRequest(Dispatchable $model, $response): void
    {
        if(data_get($response, 'success') === false){
            /** @var PackingSlip $model */
            $this->removeChange($model);

            throw new CommandException(__('exceptions.dealer.order.packing_slip.not update by onec'));
        }
    }

    protected function ifException(Dispatchable $model, Throwable $e): void
    {
        /** @var PackingSlip $model */
        $this->removeChange($model);

        throw new CommandException(__('exceptions.dealer.order.packing_slip.not update by onec'));
    }

    private function removeChange(PackingSlip $model)
    {
        $data = Cache::get('packing_slip_' . $model->guid, []);
        if(!empty($data)){

            $model->tracking_number = data_get($data, 'tracking_number');
            $model->tracking_company = data_get($data, 'tracking_company');
            $model->save();

            foreach ($model->media as $item){
                $item->delete();
            }
        }
    }
}
