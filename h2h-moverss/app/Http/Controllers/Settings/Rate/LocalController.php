<?php

namespace App\Http\Controllers\Settings\Rate;

use App\Models\Division;
use App\DataTables\Settings\Rate\Local\{LocalRateDataTable, LocalRateDataTableEditor};
use App\Http\Controllers\Controller;

class LocalController extends Controller
{

    public function records(LocalRateDataTable $dataTable)
    {
        return $dataTable->render('layouts.settings.rate.local');
    }

    public function dtEditor(LocalRateDataTableEditor $editor)
    {
        return $editor->process(request());
    }
}
