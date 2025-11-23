<?php

namespace App\Http\Controllers\Settings\Rate;

use App\DataTables\Settings\Rate\Intrastate\IntrastateRatesDataTableEditor;
use App\DataTables\Settings\Rate\Intrastate\IntrastateRatesDistanceDataTableEditor;
use App\DataTables\Settings\Rate\Intrastate\IntrastateRatesMatrixDataTableEditor;
use App\DataTables\Settings\Rate\Intrastate\IntrastateRatesWeightsDataTableEditor;
use App\Http\Controllers\Controller;
use App\Models\Calculation\IntrastateRates;
use App\Models\Calculation\IntrastateRatesDistance;
use App\Models\Calculation\IntrastateRatesWeight;
use App\Models\Division;
use Illuminate\Http\Request;
use Yajra\DataTables\{EloquentDataTable, Facades\DataTables};

class IntrastateController extends Controller
{

    public function view(Request $request)
    {
        return view('layouts.settings.rate.intrastate');
    }

    public function coefficientsDatatable()
    {
        /**
         * @var $EloquentDataTable EloquentDataTable
         */
        $EloquentDataTable = Datatables::of(IntrastateRates::with(['weightRange:id,from,to', 'distanceRange:id,from,to'])
            ->where('division_id', session('division')['id']));
        return $EloquentDataTable
            ->setRowId('id')
            ->make();
    }

    public function coefficientsMatrixEditor(IntrastateRatesMatrixDataTableEditor $editor)
    {
        return $editor->process(request());
    }


    public static function convert2Matrix($res)
    {
        $milesRows = [];
        foreach ($res as $row) {
            if ($row->distanceRange) {
                $milesRows[$row->distanceRange->id]['miles_range'] = $row->distanceRange->from . ' - ' . $row->distanceRange->to . ' mi';
                $milesRows[$row->distanceRange->id]['miles_range_from'] = +$row->distanceRange->from;
                $milesRows[$row->distanceRange->id]['DT_RowId'] = $row->distanceRange->id;
                $r = &$milesRows[$row->distanceRange->id];
                if ($row->weightRange) {
                    $r['weights_range_' . $row->weightRange->id] = [
                        'id' => $row->id,
                        'rate_weight_id' => +$row->rate_weight_id,
                        'rate_distance_id' => +$row->rate_distance_id,
                        'coefficient' => +$row->coefficient,
                    ];
                }
            }
        }
        return $milesRows;
    }

    public function coefficientsMatrix()
    {
        $res = IntrastateRates::with(['weightRange:id,from,to', 'distanceRange:id,from,to'])
            ->where('division_id', session('division')['id'])->get();
        $matrix = self::convert2Matrix($res);
        // нужно дозалить пустые! заливаем пустые
        $weights = IntrastateRatesWeight::where('division_id', session('division')['id'])->get();
        $milage = IntrastateRatesDistance::where('division_id', session('division')['id'])->get();
        if (!empty($milage)) {
            foreach ($milage as $DistanceRate) {
                if (!array_key_exists($DistanceRate->id, $matrix))
                    $matrix[$DistanceRate->id] = [
                        'miles_range' => $DistanceRate->from . ' - ' . $DistanceRate->to . ' mi',
                        'miles_range_from' => +$DistanceRate->from,
                        'DT_RowId' => $DistanceRate->id,
                    ];
                if (!empty($weights))
                    foreach ($weights as $weightRate) {
                        $key = 'weights_range_' . $weightRate->id;
                        if (!array_key_exists($key, $matrix[$DistanceRate->id])) {
                            $matrix[$DistanceRate->id][$key] = [
                                'id' => null,
                                'rate_weight_id' => +$weightRate->id,
                                'rate_distance_id' => +$DistanceRate->id,
                                'coefficient' => null,
                            ];
                        }
                    }
            }
        }
        usort($matrix,function( $a, $b) {
           return $a['miles_range'] <=> $b['miles_range'];
        });

        return DataTables::collection(array_values($matrix))->toJson();
    }


    public function coefficientsDatatableEditor(IntrastateRatesDataTableEditor $editor)
    {
        return $editor->process(request());
    }

    public function milesDatatable()
    {
        /**
         * @var $EloquentDataTable EloquentDataTable
         */
        $EloquentDataTable = Datatables::of(IntrastateRatesDistance::where('division_id', session('division')['id']));
        return $EloquentDataTable
//            ->addColumn('btns',
//                "<button type='button' class='mr-2 btn editor-edit btn-sm btn-outline-primary btn-icon waves-effect waves-themed'><i class='fal fa-edit'></i></button>" .
//                "<button type='button' class='mr-2 btn editor-delete btn-sm btn-outline-danger btn-icon waves-effect waves-themed'><i class='fal fa-times'></i></button>")
//            ->rawColumns(['btns'])
            ->setRowId('id')
            ->make();
    }

    public function milesDatatableEditor(IntrastateRatesDistanceDataTableEditor $editor)
    {
        return $editor->process(request());
    }

    public function weightsDatatable()
    {
        /**
         * @var $EloquentDataTable EloquentDataTable
         */
        $EloquentDataTable = Datatables::of(IntrastateRatesWeight::where('division_id', session('division')['id']));
        return $EloquentDataTable
            ->setRowId('id')
            ->make();
    }

    public function weightsDatatableEditor(IntrastateRatesWeightsDataTableEditor $editor)
    {
        return $editor->process(request());
    }

    public function volumesDatatable()
    {

    }

//    public function records(LocalRateDataTable $dataTable)
//    {
//        return $dataTable->render('layouts.settings.rate.local');
//    }
//
//    public function dtEditor(LocalRateDataTableEditor $editor)
//    {
//        return $editor->process(request());
//    }
}
