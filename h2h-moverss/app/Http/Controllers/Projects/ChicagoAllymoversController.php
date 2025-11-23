<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order\Estimate;
use App\Models\Order;

class ChicagoAllymoversController extends Controller
{
    /**
     * @var  $Order Order
     */
    public $Order = null;

    public function setOrder($Order)
    {
        $this->Order = $Order;
        return $this;
    }

    public function EstimatePostSave(?Estimate $EstimateBefore, ?Estimate $EstimateNew)
    {
        // if were changed trucks, crews, local hours_min, local hours_max change all works to this
        if ($EstimateBefore && $this->wereEstimateChanges($EstimateBefore, $EstimateNew)) {
            $this->Order->load('works');

            if ($this->Order->works->isNotEmpty()) {
                foreach ($this->Order->works as $Work) {
                    $Work->update([
                        'trucks' => $EstimateNew->trucks,
                        'employees' => $EstimateNew->crews,
                    ]);
                    if ($EstimateNew->type == 'local') {
                        $Work->update([
                            'duration' => $EstimateNew->local->hours_max,
                        ]);

                    }
                }
                $Order = Order::withWorksFormat()->find($this->Order->id);
                return $Order->works;
            }
        }
        return null;
    }

    private function wereEstimateChanges(?Estimate $EstimateBefore, ?Estimate $EstimateNew)
    {
        if (!$EstimateNew) {
            return false;
        }
        if ($EstimateBefore->trucks != $EstimateNew->trucks) {
            return true;
        }
        if ($EstimateBefore->crews != $EstimateNew->crews) {
            return true;
        }
        if ($EstimateBefore->type != $EstimateNew->type) {
            return true;
        }
        if ($EstimateNew->type == 'local') {
            $EstimateNew->load('local');
            if ($EstimateBefore->local && $EstimateBefore->local->hours_max != $EstimateNew->local->hours_max) {
                return true;
            }
        }
        return false;
    }
    //
}
