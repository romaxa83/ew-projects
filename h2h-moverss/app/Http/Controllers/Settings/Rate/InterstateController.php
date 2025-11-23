<?php

namespace App\Http\Controllers\Settings\Rate;

use App\DataTables\Settings\Rate\Interstate\InterstateRangesDataTableEditor;
use App\DataTables\Settings\Rate\Interstate\InterstateRatesMatrixDataTableEditor;
use App\DataTables\Settings\Rate\Interstate\InterstateShuttleDataTableEditor;
use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Settings\Interstate\ShuttlePrice;
use App\Models\Settings\Interstate\StateCoefficient;
use Cdtweb\UsStatesList;
use App\Models\Settings\Interstate\StateRange;
use Illuminate\Http\Request;
use Yajra\DataTables\{EloquentDataTable, Facades\DataTables};

class InterstateController extends Controller
{

    public function view(Request $request)
    {
//        $abbreviations = UsStatesList::abbreviations();
        return view('layouts.settings.rate.interstate', ['states' => UsStatesList::all()]);
    }


    public static function getBlankMatrix($rangeID)
    {
        $matrix = [];
        foreach (UsStatesList::all() as $abbrFrom => $name) {
            $matrix[$abbrFrom] = [
                'from_abbr' => $name,
                'DT_RowId' => $abbrFrom
            ];
            foreach (UsStatesList::abbreviations() as $abbrTo) {
                $matrix[$abbrFrom]['to_' . $abbrTo] = [
                    'id' => null,
                    'range_id' => $rangeID,
                    'from_state' => $abbrFrom,
                    'to_state' => $abbrTo,
                    'price' => null,
                ];
            }
        }
        return $matrix;
    }

    public function coefficientsMatrix(Request $request)
    {
        $validated = $request->validate([
            'filter.range' => 'exists:App\Models\Settings\Interstate\StateRange,id',
        ]);
        $rangeId = $validated['filter']['range'];
        $matrix = self::getBlankMatrix($rangeId);

        $res = StateCoefficient::where('range_id', $rangeId)->where('division_id', session('division')['id'])->get();
        if ($res->isNotEmpty()) {
            foreach ($res as $record) {
                $matrix[$record->from_state]['to_' . $record->to_state]['id'] = $record->id;
                $matrix[$record->from_state]['to_' . $record->to_state]['price'] = $record->price;
            }
        }
        return DataTables::collection($matrix)->toJson();
    }

    public function coefficientsMatrixEditor(InterstateRatesMatrixDataTableEditor $editor)
    {
        return $editor->process(request());
    }

    public function rangesDatatable()
    {
        $EloquentDataTable = Datatables::of(StateRange::where('division_id', session('division')['id']));
        return $EloquentDataTable
            ->setRowId('id')
            ->make();
    }

    public function shuttleDatatable()
    {
        $EloquentDataTable = Datatables::of(ShuttlePrice::where('division_id', session('division')['id']));
        return $EloquentDataTable
            ->setRowId('id')
            ->make();
    }

    public function shuttleDatatableEditor(InterstateShuttleDataTableEditor $editor)
    {
        return $editor->process(request());
    }


    public function rangesDatatableEditor(InterstateRangesDataTableEditor $editor)
    {
        return $editor->process(request());
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

}
